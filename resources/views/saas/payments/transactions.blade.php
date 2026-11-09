@extends('saas.layout')
@section('title', 'Transactions')
@section('topbar_title', 'Transactions')

@section('content')

<style>
    .tx-header h1 { margin: 0; font-size: 28px; letter-spacing: -.7px; color: #111827; }
    .tx-header p { margin: 8px 0 24px; color: #6b7280; font-size: 14px; }

    /* Filter Card */
    .filter-card {
        background: #fff;
        border: 1px solid #e7e9ef;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .filter-row-top {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }
    .filter-row-bottom {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 16px;
    }
    .filter-card input,
    .filter-card select {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
        font-size: 14px;
        color: #374151;
        background: #fff;
        transition: border-color .15s ease;
        font-family: inherit;
    }
    .filter-card input:focus,
    .filter-card select:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }
    .filter-card input::placeholder {
        color: #9ca3af;
    }
    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .btn-filter {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #16a34a;
        color: #fff;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s ease;
    }
    .btn-filter:hover { background: #15803d; }
    .btn-clear {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f3f4f6;
        color: #374151;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s ease;
    }
    .btn-clear:hover { background: #e5e7eb; }

    /* Filter state hint */
    .filter-hint {
        font-size: 12px;
        color: #9ca3af;
        margin-left: auto;
    }
    .filter-hint strong { color: #16a34a; }

    /* Table Card */
    .table-card {
        background: #fff;
        border: 1px solid #e7e9ef;
        border-radius: 14px;
        overflow: hidden;
    }
    .table-card-header {
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #eef0f4;
    }
    .table-card-header strong { color: #111827; font-size: 14px; }
    .table-card-header span { color: #9ca3af; font-size: 12px; }

    .tx-table {
        width: 100%;
        border-collapse: collapse;
    }
    .tx-table th {
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        color: #6b7280;
        letter-spacing: .5px;
        padding: 14px 18px;
        border-bottom: 1px solid #eef0f4;
        background: #fff;
        font-weight: 700;
    }
    .tx-table td {
        padding: 16px 18px;
        border-bottom: 1px solid #f0f1f4;
        font-size: 13px;
        color: #374151;
        vertical-align: middle;
    }
    .tx-table tbody tr:hover {
        background: #fafaff;
    }
    .tx-table tbody tr:last-child td {
        border-bottom: none;
    }

    .pill {
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }
    .pill.pending { background: #fffbeb; color: #f59e0b; }
    .pill.paid { background: #ecfdf5; color: #16a34a; }
    .pill.trial { background: #fffbeb; color: #f59e0b; }
    .pill.failed { background: #fef2f2; color: #dc2626; }
    .pill.cancelled { background: #fef2f2; color: #dc2626; }

    .invoice-link {
        color: #16a34a;
        font-weight: 700;
        text-decoration: none;
    }
    .invoice-link:hover { text-decoration: underline; }

    .btn-icon {
        display: inline-flex;
        width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #fff;
        cursor: pointer;
        font-size: 14px;
        color: #374151;
        text-decoration: none;
        margin-left: 4px;
        transition: background .15s ease;
    }
    .btn-icon:hover { background: #f9fafb; }

    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    .no-results .empty-icon {
        font-size: 40px;
        margin-bottom: 12px;
        opacity: 0.5;
    }
    .no-results .empty-title {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }
    .no-results .empty-sub {
        font-size: 13px;
        color: #9ca3af;
    }
</style>

<div class="tx-header">
    <h1>Transactions</h1>
    <p>All subscription payment transactions.</p>
</div>

{{-- ==========================================================
     FILTER BAR
     ========================================================== --}}
<div class="filter-card">

    {{-- Row 1: search, status, gateway --}}
    <div class="filter-row-top">
        <input type="text" id="filterSearch" placeholder="Search invoice, tenant, transaction...">

        <select id="filterStatus">
            <option value="">All statuses</option>
            <option value="active">Paid</option>
            <option value="trial">Pending</option>
            <option value="cancelled">Failed</option>
            <option value="suspended">Suspended</option>
        </select>

        <select id="filterGateway">
            <option value="">All gateways</option>
            <option value="stripe">Stripe</option>
            <option value="paypal">PayPal</option>
            <option value="paystack">Paystack</option>
            <option value="flutterwave">Flutterwave</option>
            <option value="mollie">Mollie</option>
            <option value="bank transfer">Bank Transfer</option>
            <option value="cash">Cash</option>
            <option value="online">Online</option>
        </select>
    </div>

    {{-- Row 2: plan, date from, date to --}}
    <div class="filter-row-bottom">
        <select id="filterPlan">
            <option value="">All plans</option>
            @foreach($transactions->unique('plan_id')->pluck('plan')->filter() as $plan)
                <option value="{{ strtolower($plan->name) }}">{{ $plan->name }}</option>
            @endforeach
        </select>

        <input type="date" id="filterDateFrom" placeholder="From date">
        <input type="date" id="filterDateTo" placeholder="To date">
    </div>

    {{-- Actions --}}
    <div class="filter-actions">
        <button type="button" class="btn-filter" onclick="applyFilters()">▼ Filter</button>
        <button type="button" class="btn-clear" onclick="clearFilters()">Clear</button>
        <span class="filter-hint" id="filterHint">
            Showing all <strong>{{ $transactions->count() }}</strong> transactions
        </span>
    </div>
</div>

{{-- ==========================================================
     TABLE
     ========================================================== --}}
<div class="table-card">
    <div class="table-card-header">
        <strong>▤ Payments</strong>
        <span id="recordCount">{{ $transactions->count() }} records</span>
    </div>

    <table class="tx-table" id="txTable">
        <thead>
            <tr>
                <th>INVOICE</th>
                <th>TENANT</th>
                <th>PLAN</th>
                <th>GATEWAY</th>
                <th>AMOUNT</th>
                <th>CYCLE</th>
                <th>STATUS</th>
                <th>DATE</th>
                <th style="text-align:right;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                @php
                    $statusLabel = match($t->status) {
                        'active' => 'Paid',
                        'trial' => 'Pending',
                        'cancelled' => 'Failed',
                        default => ucfirst($t->status),
                    };
                @endphp
                <tr
                    data-tenant="{{ strtolower($t->tenant->name ?? '') }}"
                    data-plan="{{ strtolower($t->plan->name ?? '') }}"
                    data-gateway="{{ strtolower($t->gateway ?? 'Bank Transfer') }}"
                    data-status="{{ strtolower($t->status) }}"
                    data-invoice="inv-{{ str_pad($t->id, 10, '0', STR_PAD_LEFT) }}"
                    data-date="{{ $t->created_at->format('Y-m-d') }}">
                    <td>
                        <a href="{{ url('/super/payments/'.$t->id) }}" class="invoice-link">
                            INV-{{ str_pad($t->id, 10, '0', STR_PAD_LEFT) }}
                        </a>
                    </td>
                    <td>{{ $t->tenant->name ?? '—' }}</td>
                    <td>{{ $t->plan->name ?? '—' }}</td>
                    <td>{{ $t->gateway ?? 'Bank Transfer' }}</td>
                    <td><strong>USD ${{ number_format($t->amount, 2) }}</strong></td>
                    <td>{{ ucfirst($t->billing_cycle ?? 'yearly') }}</td>
                    <td>
                        <span class="pill {{ $t->status }}">
                            ● {{ $statusLabel }}
                        </span>
                    </td>
                    <td>{{ $t->created_at->format('M d, Y') }}</td>
                    <td style="text-align:right;">
                        <a href="{{ url('/super/payments/'.$t->id) }}" class="btn-icon" title="View" style="text-decoration: none;">👁</a>
                        <a href="{{ url('/super/payments/'.$t->id.'/invoice/download') }}" target="_blank" class="btn-icon" title="Download Invoice" style="text-decoration: none;">⬇</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="no-results">
                            <div class="empty-icon">▤</div>
                            <div class="empty-title">No transactions found</div>
                            <div class="empty-sub">Transactions will appear here once payments are processed.</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    // ==========================================================
    // LIVE FILTERING
    // ==========================================================
    const filterSearch     = document.getElementById('filterSearch');
    const filterStatus     = document.getElementById('filterStatus');
    const filterGateway    = document.getElementById('filterGateway');
    const filterPlan       = document.getElementById('filterPlan');
    const filterDateFrom   = document.getElementById('filterDateFrom');
    const filterDateTo     = document.getElementById('filterDateTo');
    const table            = document.getElementById('txTable');
    const recordCount      = document.getElementById('recordCount');
    const filterHint       = document.getElementById('filterHint');

    // Apply filters on typing / changing (live)
    filterSearch.addEventListener('input', applyFilters);
    filterStatus.addEventListener('change', applyFilters);
    filterGateway.addEventListener('change', applyFilters);
    filterPlan.addEventListener('change', applyFilters);
    filterDateFrom.addEventListener('change', applyFilters);
    filterDateTo.addEventListener('change', applyFilters);

    function applyFilters() {
        const search   = filterSearch.value.toLowerCase().trim();
        const status   = filterStatus.value.toLowerCase();
        const gateway  = filterGateway.value.toLowerCase();
        const plan     = filterPlan.value.toLowerCase();
        const dateFrom = filterDateFrom.value;
        const dateTo   = filterDateTo.value;

        const rows = table.querySelectorAll('tbody tr');
        let visibleCount = 0;

        rows.forEach(row => {
            // Skip the "no results" empty row
            if (row.cells.length === 1) return;

            const rowTenant  = row.dataset.tenant;
            const rowPlan    = row.dataset.plan;
            const rowGateway = row.dataset.gateway;
            const rowStatus  = row.dataset.status;
            const rowInvoice = row.dataset.invoice;
            const rowDate    = row.dataset.date;

            // Search match: tenant, plan, invoice, or gateway
            const matchesSearch = !search
                || rowTenant.includes(search)
                || rowPlan.includes(search)
                || rowInvoice.includes(search)
                || rowGateway.includes(search);

            const matchesStatus  = !status || rowStatus === status;
            const matchesGateway = !gateway || rowGateway.includes(gateway);
            const matchesPlan    = !plan || rowPlan === plan;

            // Date range
            let matchesDate = true;
            if (dateFrom && rowDate < dateFrom) matchesDate = false;
            if (dateTo && rowDate > dateTo) matchesDate = false;

            if (matchesSearch && matchesStatus && matchesGateway && matchesPlan && matchesDate) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update counters
        recordCount.textContent = visibleCount + (visibleCount === 1 ? ' record' : ' records');

        const hasFilters = search || status || gateway || plan || dateFrom || dateTo;
        if (hasFilters) {
            filterHint.innerHTML = 'Showing <strong>' + visibleCount + '</strong> of ' + (rows.length - 1) + ' transactions';
        } else {
            filterHint.innerHTML = 'Showing all <strong>' + (rows.length - 1) + '</strong> transactions';
        }
    }

    function clearFilters() {
        filterSearch.value = '';
        filterStatus.value = '';
        filterGateway.value = '';
        filterPlan.value = '';
        filterDateFrom.value = '';
        filterDateTo.value = '';

        applyFilters();
    }

    // Apply once on page load to initialize counters
    document.addEventListener('DOMContentLoaded', applyFilters);
</script>

@endsection