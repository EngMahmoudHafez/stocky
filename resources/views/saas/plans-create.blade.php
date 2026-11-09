@extends('saas.layout')

@section('title', isset($plan) ? 'Edit Plan' : 'Create Plan')
@section('topbar_title', isset($plan) ? 'Edit Plan' : 'Create Plan')

@section('content')

<style>
    .create-plan-layout { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; }
    .create-panel { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(17,24,39,.025); margin-bottom: 20px; }
    .create-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #eef0f4; }
    .create-panel-header h2 { margin: 0; color: #111827; font-size: 16px; font-weight: 750; }
    .features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
    .feature-card { position: relative; cursor: pointer; border-radius: 10px; }
    .feature-card input { position: absolute; opacity: 0; pointer-events: none; }
    .feature-card-inner { border: 1px solid #e5e7eb; border-radius: 10px; padding: 15px; display: flex; flex-direction: column; align-items: center; text-align: center; background: #fff; transition: all .15s ease; height: 100%; }
    .feature-card:hover .feature-card-inner { border-color: #c7d2fe; background: #fafaff; }
    .feature-card-icon { font-size: 24px; margin-bottom: 10px; color: #6b7280; }
    .feature-card-body { flex: 1; }
    .feature-card-title { display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 4px; }
    .feature-card-desc { display: block; font-size: 11px; color: #9ca3af; line-height: 1.4; }
    .feature-card-toggle { position: absolute; top: 10px; right: 10px; width: 20px; height: 20px; border-radius: 50%; border: 2px solid #e5e7eb; display: flex; align-items: center; justify-content: center; color: transparent; transition: all .15s ease; }
    .feature-card-toggle i { font-size: 12px; }
    .feature-card input:checked + .feature-card-inner { border-color: #bbf7d0; background: #ecfdf5; }
    .feature-card input:checked + .feature-card-inner .feature-card-icon { color: #16a34a; }
    .feature-card input:checked + .feature-card-inner .feature-card-title { color: #16a34a; }
    .feature-card input:checked + .feature-card-inner .feature-card-desc { color: #6ee7b7; }
    .feature-card input:checked + .feature-card-inner .feature-card-toggle { background: #16a34a; border-color: #16a34a; color: #fff; }
    .how-it-works { position: sticky; top: 90px; }
    .how-title { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .how-item { display: flex; gap: 10px; margin-bottom: 15px; font-size: 13px; color: #6b7280; line-height: 1.5; }
    .how-icon { width: 20px; height: 20px; border-radius: 50%; background: #ecfdf5; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; margin-top: 2px; }
    .toggle-row { display: flex; align-items: flex-start; justify-content: space-between; padding: 15px; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 12px; }
    .toggle-row .toggle-info { flex: 1; padding-right: 15px; }
    .toggle-row .toggle-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 4px; }
    .toggle-row .toggle-desc { font-size: 12px; color: #6b7280; }
    .switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #e5e7eb; transition: .4s; border-radius: 24px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #16a34a; }
    input:checked + .slider:before { transform: translateX(20px); }
    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: #374151; }
    .form-group input { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; }
    .form-group input:focus { border-color: #6366f1; }
    .form-group small { display: block; color: #9ca3af; font-size: 11px; margin-top: 6px; }
    .limits-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
    .limit-card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 15px; text-align: center; }
    .limit-card input { text-align: center; font-weight: 700; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px; width: 100%; outline: none; }
    .btn-action { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 20px; border-radius: 9px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; }
    .btn-primary { color: #fff; border-color: #16a34a; background: #16a34a; }
    .btn-primary:hover { background: #15803d; }
    @media (max-width: 1100px) { .create-plan-layout { grid-template-columns: 1fr; } .features-grid { grid-template-columns: repeat(2, 1fr); } .limits-grid { grid-template-columns: 1fr; } }
</style>

@php
    $isEdit = isset($plan);
    $features = $isEdit ? (is_array($plan->features) ? $plan->features : (json_decode($plan->features ?? '[]', true) ?: [])) : [];
    $featureAliases = ['meeting' => 'meetings', 'service' => 'service_maintenance', 'Store' => 'store', 'People' => 'people', 'User_Management' => 'user_management', 'EWallet' => 'e_wallet', 'woocommerce_settings' => 'woocommerce', 'sale_return' => 'sales_return', 'purchase_return' => 'purchases_return', 'adjustments' => 'adjustment', 'transfers' => 'transfer'];
    $features = array_values(array_unique(array_map(fn($f) => $featureAliases[$f] ?? $f, $features)));

    // Canonical plan features. These keys match the Next SPA entitlement
    // layer; selecting a module unlocks its child menu items automatically.
    $allFeatures = [
        'dashboard' => ['Dashboard', 'bi-grid-1x2', 'Main dashboard'],
        'store' => ['Store / E-commerce', 'bi-shop', 'Online store and storefront management'],
        'people' => ['People', 'bi-people', 'Customers and suppliers'],
        'user_management' => ['User Management', 'bi-shield-check', 'Users and roles'],
        'products' => ['Products', 'bi-box-seam', 'Product management and inventory'],
        'sales' => ['Sales', 'bi-cart', 'Sales and POS'],
        'sales_return' => ['Sales Return', 'bi-arrow-return-left', 'Sales returns'],
        'purchases' => ['Purchases', 'bi-receipt', 'Purchase management'],
        'purchases_return' => ['Purchases Return', 'bi-arrow-return-right', 'Purchase returns'],
        'quotations' => ['Quotations', 'bi-file-earmark-text', 'Quotations'],
        'adjustment' => ['Adjustment', 'bi-map-pin', 'Stock adjustments'],
        'transfer' => ['Transfer', 'bi-arrow-left-right', 'Stock transfers'],
        'damages' => ['Damages', 'bi-exclamation-triangle', 'Damages'],
        'hrm' => ['HRM', 'bi-person-badge', 'Human resources'],
        'recruit' => ['Recruits & Jobs', 'bi-person-plus', 'Recruitment'],
        'meetings' => ['Meetings', 'bi-calendar-check', 'Meeting planning'],
        'marketing' => ['Marketing', 'bi-megaphone', 'Marketing campaigns and tools'],
        'accounting' => ['Accounting', 'bi-wallet2', 'Accounting and financial reports'],
        'e_wallet' => ['E-Wallet', 'bi-wallet', 'Customer wallet management'],
        'commissions' => ['Commissions', 'bi-percent', 'Commission programs and receipts'],
        'promotions' => ['Promotions', 'bi-tags', 'POS promotions'],
        'woocommerce' => ['WooCommerce', 'bi-cart3', 'WooCommerce integration'],
        'shopify' => ['Shopify', 'bi-bag', 'Shopify synchronization'],
        'documents' => ['Document Archive', 'bi-folder2-open', 'Document storage and archiving'],
        'subscription_product' => ['Subscription Products', 'bi-arrow-repeat', 'Recurring product subscriptions'],
        'manufacturing' => ['Manufacturing (MRP)', 'bi-gear', 'Production, BOMs and planning'],
        'assets' => ['Asset Management', 'bi-pc-display', 'Assets, assignments and maintenance'],
        'projects' => ['Projects & Tasks', 'bi-kanban', 'Projects, tasks and timesheets'],
        'bookings' => ['Booking Management', 'bi-calendar2-check', 'Bookings, calendar and trays'],
        'service_maintenance' => ['Service & Maintenance', 'bi-wrench', 'Service jobs and maintenance'],
        'fleet' => ['Fleet Management', 'bi-truck', 'Vehicles, fuel and fleet maintenance'],
        'hospital' => ['Hospital Management', 'bi-heart-pulse', 'Patients, doctors, lab and billing'],
        'school' => ['School Management', 'bi-mortarboard', 'Students, teachers and academics'],
        'settings' => ['Settings', 'bi-gear', 'System settings'],
        'reports' => ['Reports', 'bi-graph-up', 'Business and operational reports'],
    ];
@endphp

<div class="page-heading">
    <div>
        <h1>{{ $isEdit ? 'Edit plan' : 'Create new plan' }}</h1>
        <p>{{ $isEdit ? 'Update pricing, limits, and features.' : 'Set up a new billing plan with pricing, usage limits, and feature toggles.' }}</p>
    </div>
</div>

<div class="create-plan-layout">
    <div>
        <form method="post" action="{{ $isEdit ? url('/super/plans/'.$plan->id) : url('/super/plans') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif
            
            <div class="create-panel">
                <div class="create-panel-header"><h2>Plan details</h2></div>
                <div class="form-row">
                    <div class="form-group"><label>Plan name*</label><input name="name" value="{{ $plan->name ?? '' }}" placeholder="e.g. Starter" required></div>
                    <div class="form-group"><label>Slug*</label><input name="slug" value="{{ $plan->slug ?? '' }}" placeholder="auto-generated" required><small>URL-friendly identifier.</small></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Monthly price*</label><input name="price" type="number" step="0.01" value="{{ $plan->monthly_price ?? '' }}" placeholder="0.00" required></div>
                    <div class="form-group"><label>Yearly price*</label><input name="yearly_price" type="number" step="0.01" value="{{ $plan->yearly_price ?? '' }}" placeholder="0.00" required></div>
                </div>
                <div class="toggle-row"><div class="toggle-info"><div class="toggle-title">Active</div><div class="toggle-desc">Only active plans are available for new subscriptions.</div></div><label class="switch"><input type="checkbox" name="is_active" value="1" {{ ($plan->is_active ?? 1) ? 'checked' : '' }}><span class="slider"></span></label></div>
                <div class="toggle-row"><div class="toggle-info"><div class="toggle-title">Private Plan</div><div class="toggle-desc">Hidden from landing and registration forms.</div></div><label class="switch"><input type="checkbox" name="is_private" value="1" {{ ($plan->is_private ?? 0) ? 'checked' : '' }}><span class="slider"></span></label></div>
                <div class="toggle-row" style="margin-bottom: 0;"><div class="toggle-info"><div class="toggle-title">Free trial</div><div class="toggle-desc">Allow users to try this plan for free before paying.</div></div><label class="switch"><input type="checkbox" name="is_trial" value="1" {{ ($plan->is_trial ?? 0) ? 'checked' : '' }}><span class="slider"></span></label></div>
            </div>

            <div class="create-panel">
                <div class="create-panel-header"><h2>Usage limits</h2></div>
                <div class="limits-grid">
                    @foreach(['max_products' => 'Products', 'max_users' => 'Users', 'max_warehouses' => 'Warehouses', 'max_customers' => 'Customers', 'max_suppliers' => 'Suppliers', 'max_whatsapp_messages' => 'WhatsApp / Month'] as $field => $label)
                        <div class="limit-card">
                            <div class="limit-label">{{ $label }}</div>
                            @php $limits = json_decode($plan->limits ?? '{}', true); @endphp
                            <input type="number" name="limits[{{ $field }}]" value="{{ $limits[$field] ?? '' }}" placeholder="Unlimited" min="-1">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="create-panel">
                <div class="create-panel-header">
                    <h2><i class="bi bi-puzzle me-2"></i>Features</h2>
                    <span class="text-muted" style="font-size: 11px;">Toggle modules available on this plan</span>
                </div>
                <div class="features-grid">
                    @foreach($allFeatures as $key => $feature)
                        @php $isActive = in_array($key, $features); @endphp
                        <label class="feature-card" for="feature_{{ $key }}">
                            <input type="checkbox" name="features[]" value="{{ $key }}" id="feature_{{ $key }}" class="feature-checkbox" {{ $isActive ? 'checked' : '' }}>
                            <div class="feature-card-inner">
                                <div class="feature-card-icon"><i class="bi {{ $feature[1] }}"></i></div>
                                <div class="feature-card-body">
                                    <span class="feature-card-title">{{ $feature[0] }}</span>
                                    <span class="feature-card-desc">{{ $feature[2] }}</span>
                                </div>
                                <div class="feature-card-toggle"><i class="bi bi-check-lg"></i></div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn-action btn-primary">{{ $isEdit ? 'Update plan' : 'Create plan' }}</button>
                <a href="{{ url('/super/plans') }}" class="btn-action">Cancel</a>
            </div>
        </form>
    </div>

    <div class="create-panel how-it-works">
        <div class="how-title"><span style="color: #6366f1;">💡</span> How limits work</div>
        <div class="how-item"><div class="how-icon">✓</div><span>Empty or -1 means the resource is unlimited.</span></div>
        <div class="how-item"><div class="how-icon">✓</div><span>A positive number sets the maximum allowed count.</span></div>
        <div class="how-item"><div class="how-icon">✓</div><span>Tenants see a warning when approaching their limit.</span></div>
        <div class="how-item"><div class="how-icon">✓</div><span>Feature toggles control access to entire modules.</span></div>
        <div class="how-item"><div class="how-icon">✓</div><span>Changes apply immediately to all tenants.</span></div>
    </div>
</div>

<script>
    document.querySelectorAll('.feature-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            this.closest('.feature-card').classList.toggle('active', this.checked);
        });
    });
</script>

@endsection