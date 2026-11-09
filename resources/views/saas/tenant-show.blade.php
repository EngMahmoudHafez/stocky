@extends('saas.layout')

@section('title', $tenant->name)
@section('topbar_title', 'Tenant Details')

@section('content')

<style>
    .detail-page-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-bottom: 20px; }
    .detail-panel { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(17,24,39,.025); margin-bottom: 20px; }
    .detail-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #eef0f4; }
    .detail-panel-header h2 { margin: 0; color: #111827; font-size: 16px; font-weight: 750; }
    .status-pill-soft { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .status-active { background: #ecfdf5; color: #16a34a; }
    .status-trial { background: #eff6ff; color: #2563eb; }
    .status-suspended { background: #fef2f2; color: #dc2626; }
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
    .info-item label { display: block; color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; font-weight: 600; margin-bottom: 5px; }
    .info-item span { color: #111827; font-size: 14px; font-weight: 600; }
    .info-item .copy-text { font-family: monospace; font-size: 13px; background: #f3f4f6; padding: 4px 8px; border-radius: 6px; display: inline-block; }
    .btn-group-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .btn-action-sm { display: inline-flex; align-items: center; gap: 5px; padding: 8px 12px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 12px; font-weight: 600; text-decoration: none; cursor: pointer; transition: all .15s ease; }
    .btn-action-sm:hover { background: #f9fafb; }
    .btn-danger-sm { color: #fff; background: #dc2626; border-color: #dc2626; }
    .btn-danger-sm:hover { background: #b91c1c; }
    .btn-purple-sm { color: #fff; background: #7c3aed; border-color: #7c3aed; }
    .btn-purple-sm:hover { background: #6d28d9; }
    .limit-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding: 10px; background: #f9fafb; border-radius: 8px; }
    .limit-row .limit-info { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; color: #374151; }
    .limit-row .limit-bar { flex: 1; margin: 0 15px; height: 8px; background: #e5e7eb; border-radius: 99px; overflow: hidden; }
    .limit-row .limit-bar-fill { height: 100%; border-radius: 99px; background: #16a34a; }
    .limit-row .limit-bar-fill.red { background: #ef4444; }
    .limit-row .limit-bar-fill.purple { background: #7c3aed; }
    .limit-row .limit-bar-fill.blue { background: #3b82f6; }
    .limit-row .limit-num { font-size: 13px; font-weight: 700; color: #111827; }
    .feature-group-title { font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; margin-top: 15px; }
    .feature-pills { display: flex; flex-wrap: wrap; gap: 8px; }
    .feature-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 99px; border: 1px solid #e5e7eb; font-size: 12px; font-weight: 600; color: #9ca3af; background: #f9fafb; }
    .feature-pill.active { background: #ecfdf5; border-color: #bbf7d0; color: #16a34a; }
    .feature-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
    .domain-row { display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid #eef0f4; border-radius: 8px; margin-bottom: 10px; }
    .domain-row .domain-name { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: #111827; }
    .input-group { display: flex; gap: 10px; margin-top: 10px; }
    .input-group input { flex: 1; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; }
    .input-group input:focus { border-color: #7c3aed; }
    .table { width: 100%; border-collapse: collapse; }
    .table th { text-align: left; border-bottom: 1px solid #eee; color: #6b7280; padding: 10px; font-size: 12px; text-transform: uppercase; }
    .table td { padding: 12px 10px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #374151; vertical-align: middle; }
    .btn-soft { background: #f3f4f6; border: 1px solid #e5e7eb; }
    .btn-soft:hover { background: #e5e7eb; }
    .btn-purple-full { width: 100%; color: #fff; border-color: #6366f1; background: linear-gradient(135deg, #6366f1, #7c3aed); }
    .admin-action-form { display: inline-block; margin: 0; }
    .btn-link { background: none; border: 0; padding: 0; cursor: pointer; font-weight: 600; font-size: 12px; font-family: inherit; }
    @media (max-width: 1100px) { .detail-page-grid { grid-template-columns: 1fr; } .info-grid { grid-template-columns: 1fr; } }
</style>

@php
    $plan = $tenant->plan;
    $features = [];
    if ($plan && !empty($plan->features)) {
        $features = is_array($plan->features) ? $plan->features : (json_decode($plan->features, true) ?: []);
    }
    $usagePercent = function ($used, $limit) {
        $used  = (int) ($used ?? 0);
        $limit = (int) ($limit ?? 0);
        if ($limit <= 0) return 0;
        return (int) min(100, round(($used / $limit) * 100));
    };
    $displayLimit = function ($limit) {
        $limit = (int) ($limit ?? 0);
        return $limit > 0 ? $limit : '∞';
    };
    $usersUsed     = $tenant->users_count    ?? 2;
    $productsUsed  = $tenant->products_count ?? 3;
    $invoicesUsed  = $tenant->invoices_count ?? 4;
    $storageUsed   = $tenant->storage_used   ?? 1;
    $usersLimit    = $plan->users_limit    ?? 0;
    $productsLimit = $plan->products_limit ?? 0;
    $invoicesLimit = $plan->invoices_limit ?? 5;
    $storageLimit  = $plan->storage_limit  ?? 10;

    // Owner status: 1 = active, 0 = blocked. Null if tenant DB not readable.
    $ownerStatus = $ownerStatus ?? null;
    $isBlocked   = ($ownerStatus === 0 || $ownerStatus === '0' || $ownerStatus === 'blocked');
@endphp

{{-- Top Header & Action Buttons --}}
<div class="page-heading">
    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800;">
            {{ strtoupper(substr($tenant->name, 0, 1)) }}
        </div>
        <div>
            <h1 style="margin-bottom: 5px;">{{ $tenant->name }}</h1>
            <p style="margin: 0; color: #6b7280;">{{ $tenant->email ?? 'No email provided' }} • Registered: {{ $tenant->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    <div class="btn-group-actions">
        <a href="{{ url('/super/tenants') }}" class="btn-action-sm">← Back</a>
        <a href="{{ url('/super/tenants/'.$tenant->id.'/edit') }}" class="btn-action-sm">✎ Edit</a>
        <a href="{{ url('/super/tenants/'.$tenant->id.'/login') }}" class="btn-action-sm btn-purple-sm">⇥ Login as Tenant</a>

        @if($tenant->status === 'suspended')
            <form method="POST" action="{{ route('super.tenants.activate', $tenant) }}" class="admin-action-form">
                @csrf
                <button type="submit" class="btn-action-sm"
                        onclick="return confirm('Activate {{ $tenant->name }}?');">
                    ▶ Activate
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('super.tenants.suspend', $tenant) }}" class="admin-action-form">
                @csrf
                <button type="submit" class="btn-action-sm"
                        onclick="return confirm('Suspend {{ $tenant->name }}? The tenant will not be able to log in until reactivated.');">
                    ⏸ Suspend
                </button>
            </form>
        @endif

        <form method="POST" action="{{ route('super.tenants.delete', $tenant) }}" class="admin-action-form">
            @csrf
            <input type="hidden" name="confirm" value="1">
            <button type="submit" class="btn-action-sm btn-danger-sm"
                    onclick="return confirm('⚠ DESTRUCTIVE: this deletes the tenant record. Continue?');">
                Delete
            </button>
        </form>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success" style="background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;padding:13px 15px;border-radius:10px;margin-bottom:20px;font-size:13px;">
        ✓ {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div class="alert alert-error" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:13px 15px;border-radius:10px;margin-bottom:20px;font-size:13px;">
        <strong>Please check the following:</strong>
        <ul style="margin:7px 0 0 18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="detail-page-grid">

    <!-- ==================== LEFT COLUMN ==================== -->
    <div>
        <!-- Tenant Details -->
        <div class="detail-panel">
            <div class="detail-panel-header">
                <h2>Tenant details</h2>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Tenant ID</label>
                    <span class="copy-text">{{ $tenant->id }}</span>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <span class="status-pill-soft status-{{ $tenant->status }}">● {{ ucfirst($tenant->status) }}</span>
                </div>
                <div class="info-item">
                    <label>Plan</label>
                    <span>{{ $plan->name ?? 'No plan' }} — ${{ $plan->monthly_price ?? '0.00' }}/mo</span>
                </div>
                <div class="info-item">
                    <label>Subscription</label>
                    <span class="status-pill-soft status-active">● {{ ucfirst($tenant->subscriptions->first()->status ?? 'Active') }}</span>
                </div>
                <div class="info-item">
                    <label>Admin Email</label>
                    <span>{{ $tenant->email ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <label>Owner Phone</label>
                    <span>{{ $tenant->phone ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <label>Created</label>
                    <span>{{ $tenant->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="info-item">
                    <label>Last Updated</label>
                    <span>{{ $tenant->updated_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Plan Limits & Features -->
        <div class="detail-panel">
            <div class="detail-panel-header">
                <h2>Plan limits &amp; features</h2>
                @if($plan)
                    <a href="{{ url('/super/plans/'.$plan->id.'/edit') }}" class="link" style="color: #6366f1; font-size: 12px; font-weight: 600;">✎ Edit plan</a>
                @endif
            </div>

            @if(!$plan)
                <div style="padding: 20px; background: #f9fafb; border-radius: 8px; color: #6b7280; font-size: 13px; text-align: center;">
                    No plan assigned to this tenant.
                </div>
            @else
                <div class="feature-group-title">Usage limits</div>

                <div class="limit-row">
                    <div class="limit-info">👥 Users</div>
                    <div class="limit-bar"><div class="limit-bar-fill" style="width: {{ $usagePercent($usersUsed, $usersLimit) }}%;"></div></div>
                    <div class="limit-num">{{ $usersUsed }}/{{ $displayLimit($usersLimit) }}</div>
                </div>

                <div class="limit-row">
                    <div class="limit-info">📦 Products</div>
                    <div class="limit-bar"><div class="limit-bar-fill" style="width: {{ $usagePercent($productsUsed, $productsLimit) }}%;"></div></div>
                    <div class="limit-num">{{ $productsUsed }}/{{ $displayLimit($productsLimit) }}</div>
                </div>

                <div class="limit-row">
                    <div class="limit-info">🧾 Invoices</div>
                    <div class="limit-bar"><div class="limit-bar-fill red" style="width: {{ $usagePercent($invoicesUsed, $invoicesLimit) }}%;"></div></div>
                    <div class="limit-num">{{ $invoicesUsed }}/{{ $displayLimit($invoicesLimit) }}</div>
                </div>

                <div class="limit-row">
                    <div class="limit-info">💾 Storage</div>
                    <div class="limit-bar"><div class="limit-bar-fill purple" style="width: {{ $usagePercent($storageUsed, $storageLimit) }}%;"></div></div>
                    <div class="limit-num">{{ $storageUsed }}/{{ $displayLimit($storageLimit) }} GB</div>
                </div>

                <div class="feature-group-title">Feature access</div>
                <div class="feature-pills">
                    @php
                        $allFeaturesList = [
                            'dashboard' => 'Dashboard','sales' => 'Sales','sales_return' => 'Sales Return',
                            'purchases' => 'Purchases','purchases_return' => 'Purchases Return','quotations' => 'Quotations',
                            'stock_adjustment' => 'Stock Adjustment','stock_transfers' => 'Stock Transfers','damages' => 'Damages',
                            'people' => 'People','hr_management' => 'HR Management','recruitment' => 'Recruitment',
                            'meetings' => 'Meetings','marketing' => 'Marketing','online_orders' => 'Online Orders',
                            'woocommerce' => 'WooCommerce','shopify' => 'Shopify','point_of_sale' => 'Point of Sale',
                            'products' => 'Products','user_management' => 'User Management','accounting' => 'Accounting',
                            'e_wallet' => 'E-Wallet','service_maintenance' => 'Service & Maintenance','bookings' => 'Bookings',
                            'assets' => 'Assets','projects_tasks' => 'Projects & Tasks','contacts' => 'Contacts',
                            'all_reports' => 'All Reports','promotions' => 'Promotions','commissions' => 'Commissions',
                            'knowledge_base' => 'Knowledge Base','tenants' => 'Tenants'
                        ];
                    @endphp
                    @foreach($allFeaturesList as $key => $label)
                        @if(in_array($key, $features))
                            <span class="feature-pill active"><span class="dot"></span> {{ $label }}</span>
                        @else
                            <span class="feature-pill"><span class="dot"></span> {{ $label }}</span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- ==================== RIGHT COLUMN ==================== -->
    <div>
        <!-- Domains -->
        <div class="detail-panel">
            <div class="detail-panel-header">
                <h2>Domains</h2>
            </div>
            <div class="domain-row">
                <div class="domain-name">
                    <span style="font-size: 16px;">🌐</span> {{ $tenant->domain ?: ($tenant->slug . '.' . env('SAAS_BASE_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost')) }}
                </div>
                <span class="status-pill-soft status-active">● Active</span>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="detail-panel">
            <div class="detail-panel-header">
                <h2>Admin actions</h2>
            </div>
            <div class="feature-pills" style="gap: 8px;">

                {{-- Run migration --}}
                <form method="POST" action="{{ route('super.tenants.run-migration', $tenant) }}" class="admin-action-form">
                    @csrf
                    <button type="submit" class="btn-action-sm"
                            style="background: #fffbeb; border-color: #fcd34d; color: #d97706;"
                            onclick="return confirm('Run pending migrations on {{ $tenant->database_name }}?');">
                        ⏳ Run migration
                    </button>
                </form>

                {{-- Re-seed --}}
                <form method="POST" action="{{ route('super.tenants.reseed', $tenant) }}" class="admin-action-form">
                    @csrf
                    <button type="submit" class="btn-action-sm"
                            onclick="return confirm('Re-run seeders on {{ $tenant->database_name }}? Existing rows may collide.');">
                        ⏳ Re-seed
                    </button>
                </form>

                {{-- Do provision --}}
                <form method="POST" action="{{ route('super.tenants.provision', $tenant) }}" class="admin-action-form">
                    @csrf
                    <button type="submit" class="btn-action-sm"
                            onclick="return confirm('Run full provisioning on {{ $tenant->slug }}?');">
                        ⚙ Do provision
                    </button>
                </form>

                {{-- Reset Database --}}
                <form method="POST" action="{{ route('super.tenants.reset-database', $tenant) }}" class="admin-action-form">
                    @csrf
                    <input type="hidden" name="confirm" value="1">
                    <button type="submit" class="btn-action-sm"
                            style="background: #fef2f2; border-color: #fecaca; color: #dc2626;"
                            onclick="return confirm('⚠ DESTRUCTIVE: this will DROP ALL TABLES in {{ $tenant->database_name }} and rebuild from scratch. Continue?');">
                        ⟳ Reset Database
                    </button>
                </form>

            </div>
        </div>

        <!-- Database Configuration -->
        <div class="detail-panel">
            <div class="detail-panel-header">
                <h2>Database Configuration</h2>
                <div style="display: flex; gap: 10px;">
                    <form method="POST" action="{{ route('super.tenants.test-connection', $tenant) }}" class="admin-action-form">
                        @csrf
                        <button type="submit" class="btn-action-sm btn-soft">→ Test Connection</button>
                    </form>
                </div>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <label>DB Host</label>
                    <span class="copy-text">{{ $tenant->database_host ?: '127.0.0.1' }}</span>
                </div>
                <div class="info-item">
                    <label>DB Name</label>
                    <span class="copy-text">{{ $tenant->database_name ?: '—' }}</span>
                </div>
                <div class="info-item">
                    <label>DB Username</label>
                    <span class="copy-text">{{ $tenant->database_username ?: '(From .env)' }}</span>
                </div>
                <div class="info-item">
                    <label>DB Password</label>
                    <span class="copy-text">{{ $tenant->database_password ? '••••••••' : '(From .env)' }}</span>
                </div>
                <div class="info-item">
                    <label>DB Port</label>
                    <span class="copy-text">{{ $tenant->database_port ?: '3306' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bottom Full-Width Section: Users --}}
<div class="detail-panel" style="margin-top: 20px;">
    <div class="detail-panel-header">
        <h2>Users</h2>
        <div style="display: flex; gap: 10px;">
            <a href="#" class="link" style="color: #6366f1; font-size: 12px; font-weight: 600;">View all (1)</a>
            <button class="btn-action-sm">+ New User</button>
        </div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700;">
                            {{ strtoupper(substr($tenant->name, 0, 1)) }}
                        </div>
                        <span style="font-weight: 600;">{{ $tenant->name }} (Owner)</span>
                    </div>
                </td>
                <td>{{ $tenant->email ?? '—' }}</td>
                <td><span class="status-pill-soft status-trial">● Owner</span></td>
                <td>
                    @if($isBlocked)
                        <span class="status-pill-soft status-suspended">● Blocked</span>
                    @else
                        <span class="status-pill-soft status-active">● Active</span>
                    @endif
                </td>
                <td style="text-align: right;">
                    <button type="button" class="btn-link" onclick="openPasswordModal()" style="color: #6366f1; margin-right: 10px;">⇥ Change Password</button>

                    @if($isBlocked)
                        <form method="POST" action="{{ route('super.tenants.unblock-user', $tenant) }}" class="admin-action-form">
                            @csrf
                            <button type="submit" class="btn-link" style="color: #16a34a;"
                                    onclick="return confirm('Unblock {{ $tenant->email }}?');">
                                ✓ Unblock
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('super.tenants.block-user', $tenant) }}" class="admin-action-form">
                            @csrf
                            <button type="submit" class="btn-link" style="color: #6366f1;"
                                    onclick="return confirm('Block {{ $tenant->email }}? They will no longer be able to log in.');">
                                ⛔ Block
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ============ CHANGE PASSWORD MODAL ============ --}}
<div id="passwordModal" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
    <div onclick="closePasswordModal()" style="position:absolute; inset:0; background:rgba(15,23,42,.55); backdrop-filter: blur(2px);"></div>

    <div style="position:relative; background:#fff; border-radius:16px; width:min(440px, 92vw); padding:26px; box-shadow:0 25px 60px -15px rgba(0,0,0,.35); z-index:1;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
            <h3 style="margin:0; font-size:17px; color:#111827; font-weight:750;">Change Tenant Password</h3>
            <button type="button" onclick="closePasswordModal()" style="background:none; border:0; font-size:22px; color:#9ca3af; cursor:pointer; line-height:1;">×</button>
        </div>

        <p style="margin:0 0 18px; font-size:13px; color:#6b7280;">
            Set a new password for <strong>{{ $tenant->email ?? 'the tenant owner' }}</strong>.
        </p>

        <form method="POST" action="{{ url('/super/tenants/'.$tenant->id.'/change-password') }}" id="passwordForm">
            @csrf

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">New Password</label>
                <input type="password" name="password" id="newPassword" minlength="8" required
                       style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; outline:none;">
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">Confirm Password</label>
                <input type="password" name="password_confirmation" id="confirmPassword" minlength="8" required
                       style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; outline:none;">
            </div>

            <div id="passwordError" style="display:none; margin-bottom:14px; padding:10px 12px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#991b1b; font-size:12px;"></div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="closePasswordModal()" class="btn-action-sm">Cancel</button>
                <button type="submit" class="btn-action-sm btn-purple-sm">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPasswordModal() {
        document.getElementById('passwordModal').style.display = 'flex';
        document.getElementById('newPassword').focus();
    }
    function closePasswordModal() {
        document.getElementById('passwordModal').style.display = 'none';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
        document.getElementById('passwordError').style.display = 'none';
    }

    document.getElementById('passwordForm').addEventListener('submit', function (e) {
        var p1 = document.getElementById('newPassword').value;
        var p2 = document.getElementById('confirmPassword').value;
        var err = document.getElementById('passwordError');

        if (p1.length < 8) {
            e.preventDefault();
            err.textContent = 'Password must be at least 8 characters.';
            err.style.display = 'block';
            return;
        }
        if (p1 !== p2) {
            e.preventDefault();
            err.textContent = 'Passwords do not match.';
            err.style.display = 'block';
            return;
        }
        err.style.display = 'none';
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePasswordModal();
    });
</script>
@endsection