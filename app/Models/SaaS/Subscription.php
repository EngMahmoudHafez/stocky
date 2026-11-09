<?php

namespace App\Models\SaaS;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'saas_subscriptions';
    protected $connection = 'central';
    protected $guarded = [];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * The tenant this subscription belongs to.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * The plan this subscription is for.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}