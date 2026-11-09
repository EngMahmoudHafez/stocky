<?php

namespace App\Http\Middleware;

use App\Helpers\TenantDetect;
use App\Models\SaaS\Subscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class ResolveSaaSTenant
{
    public function handle(Request $request, Closure $next)
    {   
        // =========================================================
        // CENTRAL ROUTES VS TENANT ROUTES
        // =========================================================

        $host = strtolower($request->getHost());
        $baseDomain = strtolower(
            trim((string) env('SAAS_BASE_DOMAIN', 'localhost'), '.')
        );

        // Detect tenant subdomains, e.g. nfc.localhost.
        $isTenantHost =
            $baseDomain !== '' &&
            $host !== $baseDomain &&
            str_ends_with($host, '.' . $baseDomain);

        // Never allow tenants to access the Super Admin panel.
        if ($isTenantHost && (
            $request->is('super') ||
            $request->is('super/*')
        )) {
            return redirect('/login');
        }

        // These routes remain available on the central domain.
        if (
            $request->is('super') ||
            $request->is('super/*') ||
            $request->is('accountant') ||
            $request->is('accountant/*') ||
            $request->is('setup') ||
            $request->is('setup/*')
        ) {
            return $next($request);
        }


        // =========================================================
        // DETECT TENANT
        // =========================================================

        $tenant = TenantDetect::current();

        if (!$tenant) {
            return $next($request);
        }

        // =========================================================
        // TENANT STATUS
        // =========================================================

        if (!in_array($tenant->status, ['active', 'trial'], true)) {
            abort(403, 'This tenant is not active.');
        }

        // =========================================================
        // CHECK SUBSCRIPTION
        // =========================================================

        $subscription = Subscription::on('central')
            ->with('plan')
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();

        if ($subscription) {

            if (
                in_array(
                    $subscription->status,
                    ['pending', 'suspended', 'cancelled'],
                    true
                )
            ) {
                abort(403, 'Your subscription is not active.');
            }

            if (
                $subscription->status === 'trial' &&
                $subscription->trial_ends_at &&
                $subscription->trial_ends_at->isPast()
            ) {
                abort(403, 'Your trial has expired.');
            }

            if (
                $subscription->status === 'active' &&
                $subscription->ends_at &&
                $subscription->ends_at->isPast()
            ) {
                abort(403, 'Your subscription has expired.');
            }
        }

        // =========================================================
        // SWITCH TO TENANT DATABASE
        // =========================================================

        $mysql = config('database.connections.mysql');

        config([
            'database.connections.tenant' => array_merge($mysql, [
                'database' => $tenant->database_name,
                'username' => $tenant->database_username ?: env('DB_USERNAME'),
                'password' => $tenant->database_password !== null
                    ? $tenant->database_password
                    : env('DB_PASSWORD'),
            ]),
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        DB::setDefaultConnection('tenant');
        config(['database.default' => 'tenant']);

        // =========================================================
        // TENANT CONTEXT
        // =========================================================

        $request->attributes->set('saasTenant', $tenant);
        $request->attributes->set('saasSubscription', $subscription);

        Session::put('saas_tenant_id', $tenant->id);
        Session::put('saas_tenant', $tenant);

        app()->instance(
            \App\Models\SaaS\Tenant::class,
            $tenant
        );

        // =========================================================
        // PLAN / MODULES
        // =========================================================

        $plan = $subscription?->plan ?: $tenant->plan;

        $planFeatures = is_array($plan?->features)
            ? $plan->features
            : (
                json_decode(
                    $plan?->features ?? '[]',
                    true
                ) ?: []
            );

        $request->attributes->set(
            'saas_modules',
            (
                empty($planFeatures) ||
                in_array('*', $planFeatures, true)
            )
                ? ['*']
                : $planFeatures
        );

        // =========================================================
        // IMPORTANT TENANT WAREHOUSE FIX
        //
        // Tenant users should behave like the administrator with
        // respect to warehouse access.
        //
        // This is deliberately done HERE instead of modifying:
        //
        // SalesController
        // PurchaseController
        // POS Controller
        // Transfer Controller
        // Return Controllers
        // etc.
        //
        // Therefore all existing warehouse restrictions based on
        // is_all_warehouses / UserWarehouse automatically work.
        // =========================================================

        try {

            // -----------------------------------------------------
            // Make every tenant user an "all warehouses" user.
            // -----------------------------------------------------

            if (Schema::connection('tenant')->hasTable('users')) {

                $userColumns = Schema::connection('tenant')
                    ->getColumnListing('users');

                if (in_array('is_all_warehouses', $userColumns, true)) {

                    DB::connection('tenant')
                        ->table('users')
                        ->whereNull('deleted_at')
                        ->where(function ($query) {
                            $query
                                ->whereNull('is_all_warehouses')
                                ->orWhere('is_all_warehouses', '!=', 1);
                        })
                        ->update([
                            'is_all_warehouses' => 1,
                        ]);
                }
            }

            // -----------------------------------------------------
            // Also create UserWarehouse records.
            //
            // This is important because many old controllers do:
            //
            // UserWarehouse::where('user_id', ...)
            //
            // instead of checking is_all_warehouses.
            //
            // So we make BOTH systems agree.
            // -----------------------------------------------------

            if (
                Schema::connection('tenant')->hasTable('users') &&
                Schema::connection('tenant')->hasTable('warehouses') &&
                Schema::connection('tenant')->hasTable('user_warehouse')
            ) {

                $users = DB::connection('tenant')
                    ->table('users')
                    ->whereNull('deleted_at')
                    ->pluck('id');

                $warehouses = DB::connection('tenant')
                    ->table('warehouses')
                    ->whereNull('deleted_at')
                    ->pluck('id');

                if ($users->isNotEmpty() && $warehouses->isNotEmpty()) {

                    foreach ($users as $userId) {

                        foreach ($warehouses as $warehouseId) {

                            $exists = DB::connection('tenant')
                                ->table('user_warehouse')
                                ->where('user_id', $userId)
                                ->where('warehouse_id', $warehouseId)
                                ->exists();

                            if (!$exists) {

                                DB::connection('tenant')
                                    ->table('user_warehouse')
                                    ->insert([
                                        'user_id' => $userId,
                                        'warehouse_id' => $warehouseId,
                                    ]);
                            }
                        }
                    }
                }
            }

        } catch (\Throwable $e) {

            // Warehouse synchronization must never prevent the
            // tenant from logging in or using the application.

            \Log::warning(
                'Tenant warehouse access synchronization failed for ' .
                $tenant->slug .
                ': ' .
                $e->getMessage()
            );
        }

        // =========================================================
        // TENANT USERS MUST NOT MANAGE GLOBAL MODULE CATALOGUE
        // =========================================================

        if ($request->is('next/settings/business-modules*')) {
            return redirect('/next/dashboard');
        }

        // =========================================================
        // CONTINUE REQUEST
        // =========================================================

        return $next($request);
    }
}