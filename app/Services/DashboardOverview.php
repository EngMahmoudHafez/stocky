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

    private readonly bool $canViewStockAlerts;

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
        $this->canViewStockAlerts = $user->roles()->whereHas('permissions', fn ($query) => $query->where('name', 'Reports_quantity_alerts')
        )->exists();
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
            'period_summary' => $this->periodSummary($sales),
            'low_stock' => $this->canViewStockAlerts ? $this->lowStock() : null,
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

    private function periodSummary(Builder $sales): array
    {
        $totals = (clone $sales)->selectRaw('COUNT(*) AS invoices, COALESCE(SUM(GrandTotal), 0) AS sales,
            COALESCE(SUM(CASE WHEN GrandTotal > COALESCE(paid_amount, 0)
                THEN GrandTotal - COALESCE(paid_amount, 0) ELSE 0 END), 0) AS unpaid')->first();
        $invoices = (int) $totals->invoices;

        return [
            'sales' => (float) $totals->sales,
            'invoices' => $invoices,
            'average_sale' => $invoices > 0 ? (float) $totals->sales / $invoices : 0,
            'unpaid' => (float) $totals->unpaid,
        ];
    }

    private function lowStock(): Collection
    {
        return $this->inWarehouses(DB::table('product_warehouse as stock'), 'stock.warehouse_id')
            ->join('products', 'products.id', '=', 'stock.product_id')
            ->join('warehouses', 'warehouses.id', '=', 'stock.warehouse_id')
            ->leftJoin('product_variants as variants', 'variants.id', '=', 'stock.product_variant_id')
            ->whereNull('stock.deleted_at')->whereNull('products.deleted_at')->whereNull('variants.deleted_at')
            ->where('stock.manage_stock', 1)->whereColumn('stock.qte', '<=', 'products.stock_alert')
            ->orderBy('stock.qte')->orderBy('stock.id')->limit(5)
            ->get(['stock.id', 'products.name', 'variants.name as variant', 'warehouses.name as warehouse',
                'stock.qte as quantity', 'products.stock_alert as threshold']);
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
        $variants = (clone $lines)->join('product_variants as variants', 'variants.id', '=', 'details.product_variant_id');

        return [
            'products' => $this->rankUnits($lines, 'products.id', 'products.name'),
            'categories' => $this->rankUnits((clone $lines)->join('categories', 'categories.id', '=', 'products.category_id'), 'categories.id', 'categories.name'),
            'sizes' => $this->rankAttribute($variants, 'variants.size'),
            'colors' => $this->rankAttribute($variants, 'variants.color'),
            'materials' => $this->rankAttribute($lines, 'products.material'),
            'seasons' => $this->rankAttribute($lines, 'products.season'),
            'customers' => (clone $sales)->join('clients', 'clients.id', '=', 'sales.client_id')
                ->select('clients.name')->selectRaw('SUM(sales.GrandTotal) AS value')
                ->groupBy('clients.id', 'clients.name')->orderByDesc('value')->orderBy('clients.name')->limit(self::RANKING_LIMIT)->get(),
        ];
    }

    private function rankUnits(Builder $lines, string $key, string $label): Collection
    {
        return (clone $lines)->select("$label as name")->selectRaw('SUM('.self::SOLD_UNITS.') AS value')
            ->groupBy(...array_unique([$key, $label]))->orderByDesc('value')->orderBy($label)->limit(self::RANKING_LIMIT)->get();
    }

    private function rankAttribute(Builder $lines, string $column): Collection
    {
        return $this->rankUnits((clone $lines)->whereNotNull($column)->where($column, '<>', ''), $column, $column);
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
