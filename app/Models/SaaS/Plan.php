<?php

namespace App\Models\SaaS;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'saas_plans';
    protected $connection = 'central';
    protected $guarded = [];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * All tenants currently assigned to this plan.
     */
    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }

    /**
     * All subscriptions linked to this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}