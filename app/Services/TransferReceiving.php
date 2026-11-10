<?php

namespace App\Services;

use App\Models\product_warehouse;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;

class TransferReceiving
{
    public function dispatch(Transfer $transfer, int $userId): void
    {
        $this->lockWarehouse($transfer->from_warehouse_id);
        Warehouse::whereNull('deleted_at')->findOrFail($transfer->to_warehouse_id);
        $details = $transfer->details()->with('product')->orderBy('product_id')->orderBy('product_variant_id')->get();
        if ($details->isEmpty()) {
            $this->invalid('details', 'empty');
        }

        foreach ($details as $detail) {
            $this->dispatchLine($transfer, $detail);
        }

        $transfer->receipt_status = Transfer::IN_TRANSIT;
        $transfer->statut = 'sent';
        $transfer->dispatched_by = $userId;
        $transfer->dispatched_at = now();
        $transfer->approval_status = 'approved';
        $transfer->save();
    }

    private function dispatchLine(Transfer $transfer, TransferDetail $detail): void
    {
        if ($detail->product?->is_batch_tracked) {
            $this->invalid('details', 'batch_workflow');
        }

        $unit = Unit::find($detail->purchase_unit_id ?? $detail->product?->unit_purchase_id);
        $stock = $this->stockRow($detail, $transfer->from_warehouse_id);

        if (! $unit || $unit->operator_value <= 0 || $detail->quantity <= 0 || ! $stock?->manage_stock) {
            $this->invalid('details', 'invalid_stock');
        }

        $stockQuantity = round($unit->operator === '/'
            ? $detail->quantity / $unit->operator_value
            : $detail->quantity * $unit->operator_value, 3);
        if ($stockQuantity <= 0 || $stock->qte < $stockQuantity) {
            $this->invalid('details', 'insufficient_stock');
        }

        $stock->decrement('qte', $stockQuantity);
        $detail->dispatched_stock_quantity = $stockQuantity;
        $detail->save();
    }

    public function record(Transfer $transfer, int $userId, array $receipt): void
    {
        if ($transfer->receipts()->where('request_id', $receipt['request_id'])->exists()) {
            return;
        }
        abort_unless($transfer->awaitsReceipt() && $transfer->receipt_version === (int) $receipt['version'], 409, __('transfers.stale_receipt'));

        $this->lockWarehouse($transfer->to_warehouse_id);
        $pending = $transfer->details()->whereColumn('received_quantity', '<', 'quantity')->orderBy('id')->get();
        $counts = collect($receipt['lines'] ?? [])->pluck('counted_quantity', 'detail_id');
        $reject = $receipt['action'] === 'reject';
        $pendingIds = $pending->pluck('id');
        if ($counts->keys()->diff($pendingIds)->isNotEmpty() || (! $reject && $counts->count() !== $pendingIds->count())) {
            $this->invalid('lines', 'missing_counts');
        }

        $notes = trim($receipt['notes'] ?? '');
        if ($notes === '' && ($reject || $counts->sum() <= 0)) {
            $this->invalid('notes', 'notes_required');
        }

        $lines = $pending->map(fn (TransferDetail $detail) => $this->recordLine(
            $transfer,
            $detail,
            isset($counts[$detail->id]) ? (float) $counts[$detail->id] : null,
            $reject
        ));

        $transfer->receipts()->create([
            'user_id' => $userId,
            'request_id' => $receipt['request_id'],
            'action' => $receipt['action'],
            'notes' => $notes ?: null,
            'lines' => $lines->all(),
        ]);

        $this->updateReceiptStatus($transfer, $lines->sum('remaining_quantity'), $reject);
    }

    private function recordLine(Transfer $transfer, TransferDetail $detail, ?float $counted, bool $reject): array
    {
        $remaining = round($detail->quantity - $detail->received_quantity, 3);
        $accepted = $reject ? 0 : min($remaining, $counted);

        if ($accepted > 0) {
            $previous = $detail->received_quantity;
            $detail->received_quantity = round($previous + $accepted, 3);
            $stockQuantity = $this->stockAt($detail, $detail->received_quantity) - $this->stockAt($detail, $previous);
            $this->creditDestination($detail, $transfer->to_warehouse_id, round($stockQuantity, 3));
            $detail->save();
        }

        return [
            'detail_id' => $detail->id,
            'expected_quantity' => $remaining,
            'counted_quantity' => $counted,
            'accepted_quantity' => $accepted,
            'variance' => $counted === null ? null : round($counted - $remaining, 3),
            'remaining_quantity' => round($remaining - $accepted, 3),
        ];
    }

    private function updateReceiptStatus(Transfer $transfer, float $remaining, bool $reject): void
    {
        $transfer->receipt_status = match (true) {
            $reject => Transfer::RECEIPT_REJECTED,
            $remaining <= 0 => Transfer::RECEIVED,
            $transfer->details()->where('received_quantity', '>', 0)->exists() => Transfer::PARTIALLY_RECEIVED,
            default => Transfer::IN_TRANSIT,
        };
        $transfer->statut = $transfer->receipt_status === Transfer::RECEIVED ? 'completed' : 'sent';
        $transfer->receipt_version++;
        $transfer->save();
    }

    private function creditDestination(TransferDetail $detail, int $warehouseId, float $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $stock = $this->stockRow($detail, $warehouseId);
        if ($stock) {
            $stock->increment('qte', $quantity);

            return;
        }

        $stock = new product_warehouse;
        $stock->product_id = $detail->product_id;
        $stock->product_variant_id = $detail->product_variant_id;
        $stock->warehouse_id = $warehouseId;
        $stock->manage_stock = 1;
        $stock->qte = $quantity;
        $stock->save();
    }

    private function stockAt(TransferDetail $detail, float $quantity): float
    {
        // Round the cumulative credit so split receipts add up to the original dispatch.
        return round($detail->dispatched_stock_quantity * ($quantity / $detail->quantity), 3);
    }

    private function stockRow(TransferDetail $detail, int $warehouseId): ?product_warehouse
    {
        return product_warehouse::whereNull('deleted_at')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $detail->product_id)
            ->where('product_variant_id', $detail->product_variant_id)
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

    private function lockWarehouse(int $warehouseId): void
    {
        Warehouse::whereNull('deleted_at')->lockForUpdate()->findOrFail($warehouseId);
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => __('transfers.'.$message)]);
    }
}
