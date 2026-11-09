@extends('saas.layout')

@section('title', 'Tenants')
@section('topbar_title', 'Tenants')

@section('content')

<div class="page-heading">
    <div>
        <h1>Tenants</h1>
        <p>Manage all workspace tenants and their status.</p>
    </div>

    <div style="display: flex; gap: 10px;">
        @php $pendingCount = $tenants->where('status', 'pending')->count(); @endphp
        @if($pendingCount > 0)
            <form method="POST" action="{{ url('/super/tenants/provision-all') }}" style="display: inline-block;">
                @csrf
                <button type="submit" class="button" style="background: #dc2626; color: #fff; border-color: #dc2626;" onclick="return confirm('Provision ALL {{ $pendingCount }} pending tenants? This will create databases and admin users.')">
                    ⚡ Provision All ({{ $pendingCount }})
                </button>
            </form>
        @endif
        <button class="button" onclick="window.location.reload()">⟳ Refresh</button>
        <a href="{{ url('/super/tenants/create') }}" class="button button-primary">+ Create Tenant</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<!-- =========================================================
     SEARCH & FILTER BAR
========================================================== -->
<div class="card" style="padding: 15px; margin-bottom: 20px;">
    <div style="display: flex; gap: 15px; align-items: center;">
        <div style="flex: 1; position: relative;">
            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;">🔍</span>
            <input type="text" id="searchTenant" placeholder="Search tenants..." style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px;">
        </div>
        <select id="statusFilter" style="padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; color: #374151; min-width: 150px;">
            <option value="">All statuses</option>
            <option value="pending">⏳ Pending</option>
            <option value="trial">Trial</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>
</div>

<!-- =========================================================
     TENANTS TABLE
========================================================== -->
<div class="card" style="padding: 0; overflow: hidden;">
    <table class="table" id="tenantsTable">
        <thead>
            <tr>
                <th style="padding: 16px 20px;">TENANT</th>
                <th style="padding: 16px 20px;">DOMAINS</th>
                <th style="padding: 16px 20px;">STATUS</th>
                <th style="padding: 16px 20px;">PLAN</th>
                <th style="padding: 16px 20px;">CREATED</th>
                <th style="padding: 16px 20px; text-align: right;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenants as $t)
            <tr @if($t->status === 'pending') style="background: #fffbeb;" @endif>
                <td style="padding: 16px 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: {{ $t->status === 'pending' ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #6366f1, #8b5cf6)' }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px;">
                            {{ strtoupper(substr($t->name, 0, 1)) }}
                        </div>
                        <div>
                            <strong style="color: #111827; font-size: 14px; display: block;">{{ $t->name }}</strong>
                            <span style="color: #9ca3af; font-size: 12px;">{{ $t->email ?? 'No email' }}</span>
                            @if($t->status === 'pending')
                                <div style="font-size: 11px; color: #d97706; font-weight: 700; margin-top: 2px;">⏳ Awaiting provisioning</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="padding: 16px 20px;">
                    <span style="background: #f3f4f6; color: #374151; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                        {{ $t->domain ?: ($t->slug . '.' . env('SAAS_BASE_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost')) }}
                    </span>
                </td>
                <td style="padding: 16px 20px;">
                    @php
                        $colors = [
                            'pending'   => ['#fffbeb', '#d97706'],
                            'trial'     => ['#eff6ff', '#2563eb'],
                            'active'    => ['#ecfdf5', '#16a34a'],
                            'suspended' => ['#fef2f2', '#dc2626'],
                            'cancelled' => ['#f3f4f6', '#6b7280'],
                        ];
                        $bg = $colors[$t->status][0] ?? '#f3f4f6';
                        $fg = $colors[$t->status][1] ?? '#6b7280';
                    @endphp
                    <span class="pill" style="background: {{ $bg }}; color: {{ $fg }};">
                        ● {{ ucfirst($t->status) }}
                    </span>
                </td>
                <td style="padding: 16px 20px; color: #374151;">{{ $t->plan->name ?? '—' }}</td>
                <td style="padding: 16px 20px; color: #6b7280;">{{ $t->created_at->format('M d, Y') }}</td>
                <td style="padding: 16px 20px; text-align: right;">
                    <div style="display: inline-flex; gap: 8px; align-items: center;">
                        @if($t->status === 'pending')
                            {{-- PROVISION BUTTON (only for pending tenants) --}}
                            <form method="POST" action="{{ url('/super/tenants/'.$t->id.'/provision') }}" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="button button-primary" style="min-height: 32px; padding: 0 12px; font-size: 12px; background: #dc2626; border-color: #dc2626;" onclick="return confirm('Provision tenant {{ $t->slug }}? This will create the database and admin user.')">
                                    ⚡ Provision
                                </button>
                            </form>
                        @endif

                        <a href="{{ url('/super/tenants/'.$t->id) }}" class="button" style="min-height: 32px; padding: 0 10px; font-size: 12px;">👁 View</a>
                        <a href="{{ url('/super/tenants/'.$t->id.'/edit') }}" class="button" style="min-height: 32px; padding: 0 10px; font-size: 12px;">Edit✎</a>
                        <a href="{{ url('/super/tenants/'.$t->id.'/login') }}" class="button" style="min-height: 32px; padding: 0 10px; font-size: 12px;">Login⇥</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 60px; color: #9ca3af;">
                    <div style="font-size: 40px; margin-bottom: 10px;">📭</div>
                    <div>No tenants yet.</div>
                    <div style="font-size: 12px; margin-top: 6px;">New customer registrations will appear here.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="padding: 15px 20px; border-top: 1px solid #eef0f4; color: #9ca3af; font-size: 12px;">
        {{ count($tenants) }} tenants @if($pendingCount > 0) · <strong style="color: #dc2626;">{{ $pendingCount }} pending</strong> @endif
    </div>
</div>

<script>
    // Search & Filter Logic (Frontend only)
    document.getElementById('searchTenant').addEventListener('keyup', filterTable);
    document.getElementById('statusFilter').addEventListener('change', filterTable);

    function filterTable() {
        let searchValue = document.getElementById('searchTenant').value.toLowerCase();
        let statusValue = document.getElementById('statusFilter').value.toLowerCase();
        let rows = document.querySelectorAll('#tenantsTable tbody tr');

        rows.forEach(row => {
            let name = row.cells[0].innerText.toLowerCase();
            let status = row.cells[2].innerText.toLowerCase();
            
            let matchName = name.includes(searchValue);
            let matchStatus = statusValue === '' || status.includes(statusValue);

            if (matchName && matchStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

<style>
    .table th {
        background: #f9fafb;
        color: #6b7280;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        border-bottom: 1px solid #eef0f4;
    }
    .table td {
        border-bottom: 1px solid #f0f1f4;
        font-size: 14px;
        vertical-align: middle;
    }
    .table tbody tr:hover {
        background: #fafaff;
    }
</style>

@endsection