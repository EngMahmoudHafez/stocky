<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferReceipt extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'request_id', 'action', 'notes', 'lines'];

    protected $casts = ['lines' => 'array'];

    public function receiver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
