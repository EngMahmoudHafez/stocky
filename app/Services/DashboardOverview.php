<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWarehouse;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardOverview
{
    private const RANKING_LIMIT = 5;

    private const SOLD_UNITS = "details.quantity * COALESCE(NULLIF(details.pack_multiplier, 0), 1) *
        CASE WHEN units.operator = '/' THEN 1 / COALESCE(NULLIF(units.operator_value, 0), 1)
             ELSE COALESCE(NULLIF(units.operator_value, 0), 1) END";

    private readonly Collection $warehouses;

    private readonly Collection $selectedWarehouses;

    private readonly ?int $recordOwner;

    private readonly CarbonImmutable $today;

    private readonly CarbonImmutable $start;

    public function __construct(User $user, int $days, ?int $warehouseId)
    {
        $this->warehouses = DB::table('warehouses')->whereNull('deleted_at')
            ->when(! $user->is_all_warehouses, fn (Builder $query) => $query->whereIn(
                'id', UserWarehouse::where('user_id', $user->id)->select('warehouse_id')
            ))
            ->orderBy('id')->get(['id', 'name']);
        abort_if($warehouseId !== null && ! $this->warehouses->contains('id', $warehouseId), 403);
        $this->selectedWarehouses = $warehouseId === null
            ? $this->warehouses
            : $this->warehouses->where('id', $warehouseId)->values();
        $this->recordOwner = $user->hasRecordView() ? null : $user->id;
        $this->today = CarbonImmutable::today();
        $this->start = $this->today->subDays($days - 1);
    }

    public function snapshot(): array
    {
        $sales = $this->documents('sales', 'completed');
        $purchases = $this->documents('purchases', 'received');
        $salesByDay = $this->dailyTotals($sales);
        $purchasesByDay = $this->dailyTotals($purchases);

        return [
            'warehouses' => $this->warehouses,
            'range' => ['from' => $this->start->toDateString(), 'to' => $this->today->toDateString()],
            'updated_at' => CarbonImmutable::now()->toIso8601String(),
            'today' => $this->todayTotals($salesByDay, $purchasesByDay),
            'branches' => $this->branchComparison($salesByDay, $purchasesByDay),
            'trend' => $this->trend($salesByDay, $purchasesByDay),
            'payments' => $this->payments(),
            'rankings' => $this->rankings($sales),
            'recent_sales' => $this->recentSales($sales),
        ];
    }

    private function documents(string $table, string $status): Builder
    {
        return $this->inWarehouses(DB::table($table), "$table.warehouse_id")
            ->whereNull("$table.deleted_at")
            ->where("$table.statut", $status)
            ->when($this->recordOwner !== null, fn (Builder $query) => $query->where("$table.user_id", $this->recordOwner))
            ->whereBetween("$table.date", [$this->start->toDateString(), $this->today->toDateString()]);
    }

    private function inWarehouses(Builder $query, string $column): Builder
    {
        return $query->whereIn($column, $this->selectedWarehouses->pluck('id'));
    }

    private function dailyTotals(Builder $documents): Collection
    {
        return (clone $documents)->select('date', 'warehouse_id')->selectRaw('SUM(GrandTotal) AS total')
            ->groupBy('date', 'warehouse_id')->get();
    }

    private function todayTotals(Collection $sales, Collection $purchases): array
    {
        $date = $this->today->toDateString();

        return [
            'sales' => (float) $sales->where('date', $date)->sum('total'),
            'purchases' => (float) $purchases->where('date', $date)->sum('total'),
            'sale_returns' => (float) $this->documents('sale_returns', 'received')->where('date', $date)->sum('GrandTotal'),
            'purchase_returns' => (float) $this->documents('purchase_returns', 'completed')->where('date', $date)->sum('GrandTotal'),
        ];
    }

    private function branchComparison(Collection $sales, Collection $purchases): Collection
    {
        $stock = $this->inWarehouses(DB::table('product_warehouse as stock'), 'stock.warehouse_id')
            ->join('products', 'products.id', '=', 'stock.product_id')
            ->whereNull('stock.deleted_at')->whereNull('products.deleted_at')->where('stock.manage_stock', 1)
            ->selectRaw('stock.warehouse_id, SUM(stock.qte) AS units')
            ->groupBy('stock.warehouse_id')->pluck('units', 'stock.warehouse_id');

        $salesByWarehouse = $sales->groupBy('warehouse_id');
        $purchasesByWarehouse = $purchases->groupBy('warehouse_id');

        return $this->selectedWarehouses->map(fn ($warehouse) => [
            'id' => $warehouse->id,
            'name' => $warehouse->name,
            'sales' => (float) ($salesByWarehouse->get($warehouse->id)?->sum('total') ?? 0),
            'purchases' => (float) ($purchasesByWarehouse->get($warehouse->id)?->sum('total') ?? 0),
            'stock_units' => (float) $stock->get($warehouse->id, 0),
        ])->sortByDesc('sales')->values();
    }

    private function trend(Collection $sales, Collection $purchases): array
    {
        $salesByDate = $sales->groupBy('date');
        $purchasesByDate = $purchases->groupBy('date');
        $trend = [];
        for ($day = $this->start; $day->lte($this->today); $day = $day->addDay()) {
            $date = $day->toDateString();
            $trend[] = [
                'date' => $date,
                'sales' => (float) ($salesByDate->get($date)?->sum('total') ?? 0),
                'purchases' => (float) ($purchasesByDate->get($date)?->sum('total') ?? 0),
            ];
        }

        return $trend;
    }

    private function payments(): array
    {
        return [
            'received' => $this->paymentTotal('payment_sales', 'sales', 'sale_id')
                + $this->paymentTotal('payment_purchase_returns', 'purchase_returns', 'purchase_return_id'),
            'sent' => $this->paymentTotal('payment_purchases', 'purchases', 'purchase_id')
                + $this->paymentTotal('payment_sale_returns', 'sale_returns', 'sale_return_id'),
        ];
    }

    private function paymentTotal(string $payments, string $document, string $foreignKey): float
    {
        return (float) $this->inWarehouses(DB::table("$payments as payments"), 'document.warehouse_id')
            ->join("$document as document", 'document.id', '=', "payments.$foreignKey")
            ->whereNull('payments.deleted_at')->whereNull('document.deleted_at')
            ->when($this->recordOwner !== null, fn (Builder $query) => $query->where('payments.user_id', $this->recordOwner))
            ->whereBetween('payments.date', [$this->start->toDateString(), $this->today->toDateString()])
            ->sum('payments.montant');
    }

    private function rankings(Builder $sales): array
    {
        $lines = (clone $sales)->join('sale_details as details', 'details.sale_id', '=', 'sales.id')
            ->join('products', 'products.id', '=', 'details.product_id')
            ->leftJoin('units', 'units.id', '=', 'details.sale_unit_id');
        $variants = $this->rankVariantAttributes($lines);

        return [
            'products' => $this->rankUnits($lines, 'products.id', 'products.name'),
            'categories' => $this->rankUnits((clone $lines)->join('categories', 'categories.id', '=', 'products.category_id'), 'categories.id', 'categories.name'),
            'sizes' => $variants['sizes'],
            'colors' => $variants['colors'],
            'customers' => (clone $sales)->join('clients', 'clients.id', '=', 'sales.client_id')
                ->select('clients.name')->selectRaw('SUM(sales.GrandTotal) AS value')
                ->groupBy('clients.id', 'clients.name')->orderByDesc('value')->orderBy('clients.name')->limit(self::RANKING_LIMIT)->get(),
        ];
    }

    private function rankUnits(Builder $lines, string $key, string $label): Collection
    {
        return (clone $lines)->select("$label as name")->selectRaw('SUM('.self::SOLD_UNITS.') AS value')
            ->groupBy($key, $label)->orderByDesc('value')->orderBy($label)->limit(self::RANKING_LIMIT)->get();
    }

    private function rankVariantAttributes(Builder $lines): array
    {
        $variants = (clone $lines)->join('product_variants as variants', 'variants.id', '=', 'details.product_variant_id')
            ->whereNotNull('variants.name')->select('variants.name')
            ->selectRaw('SUM('.self::SOLD_UNITS.') AS units')->groupBy('variants.name')->get();
        $sizes = [];
        $colors = [];

        foreach ($variants as $variant) {
            // Variant names with an explicit separator can be ranked without guessing from legacy labels.
            if (preg_match('/^\s*(.+?)\s+\/\s+(.+?)\s*$/u', $variant->name, $parts)) {
                [, $color, $size] = $parts;
                $colors[$color] = ($colors[$color] ?? 0) + (float) $variant->units;
                $sizes[$size] = ($sizes[$size] ?? 0) + (float) $variant->units;
            }
        }

        return ['sizes' => $this->topAttributes($sizes), 'colors' => $this->topAttributes($colors)];
    }

    private function topAttributes(array $counts): Collection
    {
        ksort($counts);

        return collect($counts)->sortDesc()->take(self::RANKING_LIMIT)
            ->map(fn ($units, $name) => ['name' => (string) $name, 'value' => $units])->values();
    }

    private function recentSales(Builder $sales): Collection
    {
        return (clone $sales)->leftJoin('clients', 'clients.id', '=', 'sales.client_id')
            ->join('warehouses', 'warehouses.id', '=', 'sales.warehouse_id')
            ->orderByDesc('sales.date')->orderByDesc('sales.time')->orderByDesc('sales.id')->limit(5)
            ->get(['sales.id', 'sales.Ref as reference', 'sales.date', 'sales.GrandTotal as total',
                'clients.name as customer', 'warehouses.name as warehouse']);
    }
}
