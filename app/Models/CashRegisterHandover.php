<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegisterHandover extends Model
{
    protected $fillable = ['request_uuid', 'cash_register_id', 'transfer_money_id', 'created_by',
        'receiver_id', 'receiver_name', 'amount', 'expected_cash_after', 'notes'];

    protected $casts = ['amount' => 'double', 'expected_cash_after' => 'double'];
}
