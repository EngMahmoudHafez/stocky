<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    public const AWAITING_DISPATCH = 'awaiting_dispatch';

    public const IN_TRANSIT = 'in_transit';

    public const PARTIALLY_RECEIVED = 'partially_received';

    public const RECEIVED = 'received';

    public const RECEIPT_REJECTED = 'receipt_rejected';

    protected $table = 'transfers';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id', 'date', 'user_id', 'from_warehouse_id', 'to_warehouse_id', 'time',
        'items', 'statut', 'approval_status', 'notes', 'GrandTotal', 'discount', 'shipping', 'TaxNet', 'tax_rate',
        'created_at', 'updated_at', 'deleted_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'from_warehouse_id' => 'integer',
        'to_warehouse_id' => 'integer',
        'items' => 'double',
        'GrandTotal' => 'double',
        'discount' => 'double',
        'shipping' => 'double',
        'TaxNet' => 'double',
        'tax_rate' => 'double',
        'receipt_version' => 'integer',
        'dispatched_at' => 'datetime',

    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function details()
    {
        return $this->hasMany('App\Models\TransferDetail');
    }

    public function receipts()
    {
        return $this->hasMany(TransferReceipt::class);
    }

    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function awaitsReceipt(): bool
    {
        return in_array($this->receipt_status, [self::IN_TRANSIT, self::PARTIALLY_RECEIVED], true);
    }

    public function from_warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'from_warehouse_id');
    }

    public function to_warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'to_warehouse_id');
    }

    /**
     * Accessor to ensure OLD TRANSFERS SAFETY:
     * any existing row with a NULL approval_status is treated as "approved".
     */
    public function getApprovalStatusAttribute($value)
    {
        if ($value === null) {
            return 'approved';
        }

        return $value;
    }

    /**
     * Convenience helper for business logic.
     */
    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }
}
