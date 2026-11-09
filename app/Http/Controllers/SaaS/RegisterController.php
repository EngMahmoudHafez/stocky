<?php

namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\Controller;
use App\Models\SaaS\{Tenant, Plan, Subscription};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function show(Request $r)
    {
        $settingsFile = storage_path('app/super-settings.json');
        $settings = is_file($settingsFile)
            ? (json_decode(file_get_contents($settingsFile), true) ?: [])
            : [];

        $settings = array_merge([
            'app_name'     => 'Poraone',
            'company_name' => 'Poraone',
        ], $settings);

        $plans = Plan::where('is_active', 1)->orderBy('monthly_price')->get();
        $selectedPlanId = $r->query('plan', $plans->first()->id ?? null);

        return view('saas.register', compact('settings', 'plans', 'selectedPlanId'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'subdomain'      => 'required|alpha_dash|unique:saas_tenants,slug|min:3|max:30',
            'company_name'   => 'required|string|max:191',
            'admin_email'    => 'required|email',
            'owner_phone'    => 'nullable|string|max:191',
            'admin_password' => 'required|min:8|confirmed',
            'plan_id'        => 'required|exists:saas_plans,id',
            'billing_cycle'  => 'required|in:monthly,yearly',
        ]);

        $plan = Plan::findOrFail($data['plan_id']);
        $slug = strtolower($data['subdomain']);

        $tenant = Tenant::create([
            'name'              => $data['company_name'],
            'slug'              => $slug,
            'email'             => $data['admin_email'],
            'phone'             => $data['owner_phone'] ?? null,
            'plan_id'           => $plan->id,
            'status'            => 'pending',
            'billing_cycle'     => $data['billing_cycle'],
            'trial_ends_at'     => now()->addDays((int) ($plan->trial_days ?? 14)),
            'domain'            => $slug . '.' . env('SAAS_BASE_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost'),
            'database_name'     => env('TENANT_DB_PREFIX', 'stocky_tenant_') . Str::slug($slug, '_'),
            'database_host'     => env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'database_port'     => (int) env('TENANT_DB_PORT', env('DB_PORT', 3306)),
            'database_username' => env('TENANT_DB_USERNAME', env('DB_USERNAME', '')),
            'database_password' => env('TENANT_DB_PASSWORD', env('DB_PASSWORD', '')),
        ]);

        // Store password securely in a pending file for provisioning
        $pendingFile = storage_path('app/pending-registrations.json');
        $pending = is_file($pendingFile) ? (json_decode(file_get_contents($pendingFile), true) ?: []) : [];
        $pending[$tenant->id] = [
            'password'   => $data['admin_password'],
            'created_at' => now()->toDateTimeString(),
        ];
        file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));

        Subscription::create([
            'tenant_id'     => $tenant->id,
            'plan_id'       => $plan->id,
            'status'        => 'pending',
            'billing_cycle' => $data['billing_cycle'],
            'amount'        => $data['billing_cycle'] === 'yearly'
                ? $plan->yearly_price
                : $plan->monthly_price,
            'trial_ends_at' => $tenant->trial_ends_at,
        ]);

        return redirect('/register/thank-you')->with('tenant_id', $tenant->id);
    }

    public function thankYou(Request $r)
    {
        $settingsFile = storage_path('app/super-settings.json');
        $settings = is_file($settingsFile)
            ? (json_decode(file_get_contents($settingsFile), true) ?: [])
            : [];
        $settings = array_merge(['app_name' => 'Poraone', 'company_name' => 'Poraone'], $settings);

        return view('saas.register-thank-you', compact('settings'));
    }
}