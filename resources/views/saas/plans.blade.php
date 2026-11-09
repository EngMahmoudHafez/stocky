@extends('saas.layout')

@section('title', 'Plans')
@section('topbar_title', 'Plans')

@section('content')

<style>
    .plans-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
    .plans-page-header h1 { margin: 0; font-size: 28px; line-height: 1.2; letter-spacing: -.7px; color: #111827; }
    .plans-page-header p { margin: 8px 0 0; color: #6b7280; font-size: 14px; }
    .plans-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; }
    .plan-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 24px; box-shadow: 0 2px 8px rgba(17,24,39,.025); display: flex; flex-direction: column; position: relative; }
    .plan-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    .plan-icon-box { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; color: #fff; box-shadow: 0 8px 20px rgba(99,102,241,.25); }
    .plan-icon-purple { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .plan-icon-blue { background: linear-gradient(135deg, #3b82f6, #06b6d4); }
    .plan-icon-green { background: linear-gradient(135deg, #10b981, #34d399); }
    .plan-status { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; background: #ecfdf5; color: #16a34a; font-size: 12px; font-weight: 700; }
    .plan-name { color: #111827; font-size: 20px; font-weight: 800; margin-bottom: 2px; }
    .plan-slug { color: #9ca3af; font-size: 13px; margin-bottom: 15px; }
    .plan-price-box { margin-bottom: 20px; }
    .plan-price { font-size: 32px; font-weight: 800; color: #111827; line-height: 1; }
    .plan-price small { font-size: 14px; font-weight: 600; color: #6b7280; }
    .plan-yearly { display: flex; align-items: center; gap: 10px; margin-top: 8px; font-size: 13px; color: #6b7280; }
    .save-badge { background: #ecfdf5; color: #16a34a; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
    .plan-divider { height: 1px; background: #eef0f4; margin: 20px 0; }
    .plan-section-title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 15px; }
    .limit-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 13px; }
    .limit-row .limit-label { color: #6b7280; display: flex; align-items: center; gap: 8px; }
    .limit-row .limit-value { color: #111827; font-weight: 700; }
    .feature-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
    .feature-pill { display: inline-flex; align-items: center; gap: 5px; padding: 6px 10px; border-radius: 8px; border: 1px solid #e5e7eb; font-size: 11px; font-weight: 600; color: #6b7280; background: #fff; }
    .feature-pill.active { background: #ecfdf5; border-color: #bbf7d0; color: #16a34a; }
    .feature-pill .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .plan-card-footer { margin-top: auto; padding-top: 15px; border-top: 1px solid #eef0f4; display: flex; justify-content: space-between; align-items: center; }
    .plan-subscribers { font-size: 13px; color: #6b7280; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
    .btn-plan-edit { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all .15s ease; }
    .btn-plan-edit:hover { background: #e5e7eb; }
    .button { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 15px; border-radius: 9px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; }
    .button:hover { background: #f9fafb; }
    .button-primary { color: #fff; border-color: #16a34a; background: #16a34a; }
    .button-primary:hover { background: #15803d; border-color: #15803d; }
    @media (max-width: 1100px) { .plans-grid { grid-template-columns: 1fr; } }
</style>

@php
    // EXACT FEATURE KEY MAP — matches plans-create.blade.php
    $featureNames = [
        'dashboard' => 'Dashboard',
        'billing' => 'Billing',
        'support' => 'Support',

        'store' => 'Store',
        'store_settings' => 'Store Settings',
        'store_payment_gateway' => 'Payment Gateway',
        'store_orders' => 'Online Orders',
        'store_collections' => 'Collections',
        'store_banners' => 'Banners',
        'store_subscribers' => 'Subscribers',
        'store_messages' => 'Messages',
        'store_invite_codes' => 'Invite Codes',
        'store_pending_customers' => 'Pending Customers',
        'store_shipping_methods' => 'Shipping Methods',
        'store_tax_rates' => 'Tax Rates',
        'store_coupons' => 'Coupons',
        'store_flash_sales' => 'Flash Sales',
        'store_reviews' => 'Product Reviews',
        'store_popups' => 'Popup Messages',
        'store_quote_requests' => 'Quote Requests',
        'store_returns' => 'Returns Requests',
        'store_pages' => 'Pages',
        'store_menus' => 'Menus',
        'store_realestate' => 'Real Estate',
        'store_realestate_properties' => 'Properties',
        'store_realestate_categories' => 'Property Categories',
        'store_realestate_inquiries' => 'Property Inquiries',

        'people' => 'People',
        'people_customers' => 'Customers',
        'people_suppliers' => 'Suppliers',

        'user_management' => 'User Management',
        'user_management_users' => 'Users',
        'user_management_permissions' => 'Group Permissions',

        'products' => 'Products',
        'products_add' => 'Add Product',
        'products_list' => 'Products List',
        'products_import' => 'Import Products',
        'products_categories' => 'Categories',
        'products_subcategories' => 'Sub Category',
        'products_brands' => 'Brand',
        'products_units' => 'Units',
        'products_barcodes' => 'Print Barcode',
        'products_batches' => 'Batches',
        'products_serial_numbers' => 'Serial Numbers',

        'sales' => 'Sales',
        'sales_list' => 'All Sales',
        'sales_add' => 'Create Sale',
        'sales_import' => 'Import Sales',
        'sales_pos' => 'POS',
        'sales_customer_screen' => 'Customer Screen',
        'sales_shipments' => 'Shipments',
        'sales_realtime_counter' => 'Real-time Sales Counter',
        'sales_kitchen' => 'Kitchen Display',
        'sales_return' => 'Sales Return',

        'purchases' => 'Purchases',
        'purchases_add' => 'Add Purchase',
        'purchases_list' => 'List Purchases',
        'purchases_return' => 'Purchases Return',

        'quotations' => 'Quotations',
        'adjustment' => 'Adjustment',
        'transfer' => 'Transfer',
        'stock_adjustment' => 'Stock Adjustment',
        'stock_transfers' => 'Stock Transfers',
        'damages' => 'Damages',

        'hrm' => 'HRM',
        'recruit' => 'Recruits & Jobs',
        'meetings' => 'Meetings',
        'meeting' => 'Meetings',
        'marketing' => 'Marketing',

        'accounting' => 'Accounting',
        'e_wallet' => 'E-Wallet',
        'commissions' => 'Commissions',
        'promotions' => 'Promotions',

        'woocommerce' => 'WooCommerce',
        'shopify' => 'Shopify',
        'documents' => 'Document Archive',
        'subscription_product' => 'Subscription Product',

        'manufacturing' => 'Manufacturing (MRP)',
        'assets' => 'Asset Management',
        'projects' => 'Projects Management',
        'bookings' => 'Booking Management',
        'service_maintenance' => 'Service & Maintenance',
        'service' => 'Service & Maintenance',
        'fleet' => 'Fleet Management',
        'hospital' => 'Hospital Management',
        'school' => 'School Management',

        'settings' => 'Settings',

        'reports' => 'Reports',
        'reports_ai' => 'AI Reports',
        'reports_sales' => 'Sales Report',
        'reports_purchases' => 'Purchases Report',
        'reports_customers' => 'Customers Report',
        'reports_suppliers' => 'Suppliers Report',
        'reports_stock' => 'Stock Report',
    ];
@endphp

<div class="plans-page-header">
    <div>
        <h1>Plans</h1>
        <p>Manage billing plans, usage limits, and feature access for your tenants.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ url('/super/plans/create') }}" class="button button-primary">+ New plan</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif

<div class="plans-grid">
    @foreach($plans as $index => $p)
        @php
            $iconClass = 'plan-icon-purple';
            if(str_contains(strtolower($p->name), 'professional')) $iconClass = 'plan-icon-blue';
            if(str_contains(strtolower($p->name), 'enterprise')) $iconClass = 'plan-icon-green';
            
            $yearlyPrice = (float) $p->yearly_price;
            $monthlyPrice = (float) $p->monthly_price;
            $yearlyTotal = $monthlyPrice * 12;
            $discount = $yearlyTotal > 0 && $yearlyPrice > 0 ? round((($yearlyTotal - $yearlyPrice) / $yearlyTotal) * 100) : 0;

            // Decode limits from JSON column
            $limitsData = json_decode($p->limits ?? '{}', true) ?: [];

            $limits = [
                'Products' => ($limitsData['max_products'] ?? '—'),
                'Users' => ($limitsData['max_users'] ?? '—'),
                'Warehouses' => ($limitsData['max_warehouses'] ?? '—'),
                'Customers' => ($limitsData['max_customers'] ?? '—'),
                'Suppliers' => ($limitsData['max_suppliers'] ?? '—'),
                'WhatsApp Messages/month' => ($limitsData['max_whatsapp_messages'] ?? '—'),
            ];

            // Decode and translate features to friendly names
            $storedFeatures = is_array($p->features) ? $p->features : (json_decode($p->features ?? '[]', true) ?: []);
            $features = [];
            foreach($storedFeatures as $key) {
                $features[] = $featureNames[$key] ?? ucfirst(str_replace('_', ' ', $key));
            }
            if (empty($features)) $features = ['No features assigned'];

            // Subscriber count (via withCount('tenants'))
            $subscriberCount = $p->tenants_count ?? 0;
        @endphp

        <div class="plan-card">
            <div class="plan-card-header">
                <div class="plan-icon-box {{ $iconClass }}">{{ strtoupper(substr($p->name, 0, 1)) }}</div>
                <span class="plan-status">● Active</span>
            </div>

            <div class="plan-name">{{ $p->name }}</div>
            <div class="plan-slug">{{ $p->slug }}</div>

            <div class="plan-price-box">
                <div class="plan-price">${{ number_format($monthlyPrice, 2) }} <small>/mo</small></div>
                <div class="plan-yearly">
                    <span>${{ number_format($yearlyPrice, 2) }} /yr</span>
                    @if($discount > 0)<span class="save-badge">Save {{ $discount }}%</span>@endif
                </div>
            </div>

            <div class="plan-divider"></div>

            <div class="plan-section-title">⚡ LIMITS</div>
            @foreach($limits as $label => $value)
                <div class="limit-row">
                    <span class="limit-label">▣ {{ $label }}</span>
                    <span class="limit-value">{{ $value }}</span>
                </div>
            @endforeach

            <div class="plan-divider"></div>

            <div class="plan-section-title">✨ FEATURES ({{ count($features) }})</div>
            <div class="feature-pills">
                @foreach($features as $feature)
                    <span class="feature-pill active"><span class="dot"></span> {{ $feature }}</span>
                @endforeach
            </div>

            <div class="plan-card-footer">
                <div class="plan-subscribers">
                    👥 {{ $subscriberCount }} {{ $subscriberCount === 1 ? 'subscriber' : 'subscribers' }}
                </div>
                <a href="{{ url('/super/plans/'.$p->id.'/edit') }}" class="btn-plan-edit">✎ Edit</a>
            </div>
        </div>
    @endforeach
</div>

@endsection