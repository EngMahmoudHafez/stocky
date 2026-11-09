@extends('saas.layout')

@section('title', 'Subscriptions')
@section('topbar_title', 'Subscriptions')

@section('content')

<style>
    .subs-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
    }

    .subs-page-header h1 {
        margin: 0;
        font-size: 28px;
        line-height: 1.2;
        letter-spacing: -.7px;
        color: #111827;
    }

    .subs-page-header p {
        margin: 8px 0 0;
        color: #6b7280;
        font-size: 14px;
    }

    .btn-refresh {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 38px;
        padding: 0 16px;
        border-radius: 9px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-refresh:hover {
        background: #f9fafb;
    }

    .subs-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }

    .subs-stat-card {
        background: #fff;
        border: 1px solid #e7e9ef;
        border-radius: 14px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 8px rgba(17,24,39,.025);
    }

    .subs-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #fff;
        flex-shrink: 0;
    }

    .subs-stat-icon.green {
        background: linear-gradient(135deg, #10b981, #34d399);
    }

    .subs-stat-icon.blue {
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
    }

    .subs-stat-icon.orange {
        background: linear-gradient(135deg, #f97316, #fb923c);
    }

    .subs-stat-icon.amber {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
    }

    .subs-stat-icon.red {
        background: linear-gradient(135deg, #ef4444, #f87171);
    }

    .subs-stat-text .subs-stat-value {
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        line-height: 1;
    }

    .subs-stat-text .subs-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #9ca3af;
        margin-top: 4px;
    }

    .subs-filter-card {
        background: #fff;
        border: 1px solid #e7e9ef;
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .subs-filter-card input[type="text"] {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
        font-size: 14px;
    }

    .subs-filter-card input[type="text"]:focus {
        border-color: #16a34a;
    }

    .subs-filter-card select {
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
        font-size: 14px;
        color: #374151;
        min-width: 160px;
    }

    .subs-filter-card .filter-count {
        margin-left: auto;
        color: #9ca3af;
        font-size: 12px;
    }

    .subs-table-card {
        background: #fff;
        border: 1px solid #e7e9ef;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(17,24,39,.025);
    }

    .subs-table {
        width: 100%;
        border-collapse: collapse;
    }

    .subs-table th {
        text-align: left;
        border-bottom: 1px solid #eef0f4;
        color: #6b7280;
        padding: 14px 20px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 700;
        background: #fff;
    }

    .subs-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f0f1f4;
        font-size: 14px;
        color: #374151;
        vertical-align: middle;
    }

    .subs-table tbody tr:hover {
        background: #fafaff;
    }

    .tenant-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tenant-avatar {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .tenant-name {
        font-weight: 700;
        color: #111827;
        font-size: 14px;
    }

    .tenant-email {
        color: #9ca3af;
        font-size: 12px;
        margin-top: 2px;
    }

    .plan-cell .plan-name {
        font-weight: 700;
        color: #111827;
        font-size: 13px;
    }

    .plan-cell .plan-price {
        color: #6b7280;
        font-size: 11px;
        margin-top: 2px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-pill.active {
        background: #ecfdf5;
        color: #16a34a;
    }

    .status-pill.trial {
        background: #eff6ff;
        color: #2563eb;
    }

    .status-pill.pending {
        background: #fff7ed;
        color: #ea580c;
    }

    .status-pill.suspended {
        background: #fef2f2;
        color: #dc2626;
    }

    .status-pill.cancelled {
        background: #fef2f2;
        color: #dc2626;
    }

    .btn-icon {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-icon:hover {
        background: #f9fafb;
    }

    .btn-icon.primary {
        background: #ecfdf5;
        color: #16a34a;
        border-color: #bbf7d0;
    }

    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .modal-backdrop.show {
        display: flex;
    }

    .modal-panel {
        background: #fff;
        border-radius: 14px;
        width: 520px;
        max-width: 92%;
        padding: 28px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 22px;
        cursor: pointer;
        color: #6b7280;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #374151;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
        font-size: 14px;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #16a34a;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 22px;
    }

    .btn-cancel {
        padding: 10px 18px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-save {
        padding: 10px 18px;
        border-radius: 8px;
        border: none;
        background: #16a34a;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    @media (max-width: 1100px) {
        .subs-stats {
            grid-template-columns: repeat(3, 1fr);
        }

        .subs-table-card {
            overflow-x: auto;
        }

        .subs-table {
            min-width: 1000px;
        }
    }

    @media (max-width: 700px) {
        .subs-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .subs-filter-card {
            flex-direction: column;
            align-items: stretch;
        }

        .subs-filter-card .filter-count {
            margin-left: 0;
        }
    }
</style>


<div class="subs-page-header">
    <div>
        <h1>Subscriptions</h1>
        <p>Monitor and manage all tenant subscriptions.</p>
    </div>

    <a href="{{ url('/super/subscriptions') }}" class="btn-refresh">
        ↻ Refresh
    </a>
</div>


@if(session('success'))
    <div class="alert alert-success">
        ✓ {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        {{ $errors->first() }}
    </div>
@endif


<!-- STATS -->
<div class="subs-stats">

    <div class="subs-stat-card">
        <div class="subs-stat-icon green">✓</div>

        <div class="subs-stat-text">
            <div class="subs-stat-value">
                {{ $stats['active'] }}
            </div>

            <div class="subs-stat-label">
                Active
            </div>
        </div>
    </div>


    <div class="subs-stat-card">
        <div class="subs-stat-icon blue">⏳</div>

        <div class="subs-stat-text">
            <div class="subs-stat-value">
                {{ $stats['trial'] }}
            </div>

            <div class="subs-stat-label">
                Trial
            </div>
        </div>
    </div>


    <div class="subs-stat-card">
        <div class="subs-stat-icon orange">⏸</div>

        <div class="subs-stat-text">
            <div class="subs-stat-value">
                {{ $stats['pending'] }}
            </div>

            <div class="subs-stat-label">
                Pending
            </div>
        </div>
    </div>


    <div class="subs-stat-card">
        <div class="subs-stat-icon amber">⚠</div>

        <div class="subs-stat-text">
            <div class="subs-stat-value">
                {{ $stats['suspended'] }}
            </div>

            <div class="subs-stat-label">
                Suspended
            </div>
        </div>
    </div>


    <div class="subs-stat-card">
        <div class="subs-stat-icon red">✕</div>

        <div class="subs-stat-text">
            <div class="subs-stat-value">
                {{ $stats['cancelled'] }}
            </div>

            <div class="subs-stat-label">
                Cancelled
            </div>
        </div>
    </div>

</div>


<!-- FILTER -->
<div class="subs-filter-card">

    <input
        type="text"
        id="subSearch"
        placeholder="Search by tenant..."
    >

    <select id="subStatus">
        <option value="">All statuses</option>
        <option value="active">Active</option>
        <option value="trial">Trial</option>
        <option value="pending">Pending</option>
        <option value="suspended">Suspended</option>
        <option value="cancelled">Cancelled</option>
    </select>

    <span class="filter-count">
        {{ $subscriptions->count() }}
        subscription{{ $subscriptions->count() === 1 ? '' : 's' }}
    </span>

</div>


<!-- TABLE -->
<div class="subs-table-card">

    <table class="subs-table" id="subsTable">

        <thead>
            <tr>
                <th>TENANT</th>
                <th>PLAN</th>
                <th>STATUS</th>
                <th>TRIAL ENDS</th>
                <th>LATEST PAYMENT</th>
                <th style="text-align: right;">ACTIONS</th>
            </tr>
        </thead>

        <tbody>

            @forelse($subscriptions as $s)

                <tr>

                    <!-- TENANT -->
                    <td>
                        <div class="tenant-cell">

                            <div class="tenant-avatar">
                                {{ strtoupper(substr($s->tenant->name ?? '?', 0, 1)) }}
                            </div>

                            <div>
                                <div class="tenant-name">
                                    {{ $s->tenant->name ?? 'Unknown' }}
                                </div>

                                <div class="tenant-email">
                                    {{ $s->tenant->email ?? '' }}
                                </div>
                            </div>

                        </div>
                    </td>


                    <!-- PLAN -->
                    <td class="plan-cell">

                        <div class="plan-name">
                            {{ $s->plan->name ?? '—' }}
                        </div>

                        <div class="plan-price">
                            ${{ number_format($s->plan->monthly_price ?? 0, 2) }}/mo
                            ·
                            ${{ number_format($s->plan->yearly_price ?? 0, 2) }}/yr
                        </div>

                    </td>


                    <!-- STATUS -->
                    <td>

                        <span class="status-pill {{ $s->status }}">
                            ● {{ ucfirst($s->status) }}
                        </span>

                    </td>


                    <!-- TRIAL ENDS -->
                    <td>
                        {{ $s->trial_ends_at
                            ? \Carbon\Carbon::parse($s->trial_ends_at)->format('M d, Y')
                            : '—'
                        }}
                    </td>


                    <!-- LATEST PAYMENT -->
                    <td>

                        @if($s->amount)

                            ${{ number_format($s->amount, 2) }}

                            ·

                            <span style="color: #6b7280;">
                                {{ ucfirst($s->gateway ?? 'Manual') }}
                            </span>

                        @else

                            <span style="color: #9ca3af;">
                                —
                            </span>

                        @endif

                    </td>


                    <!-- ACTIONS -->
                    <td style="text-align: right;">

                        <div style="display: inline-flex; gap: 8px;">

                            <a
                                href="{{ url('/super/tenants/'.$s->tenant_id) }}"
                                class="btn-icon primary"
                            >
                                👁 View
                            </a>


                            <button
                                type="button"
                                class="btn-icon"
                                onclick="openEditSubscription(
                                    {{ $s->id }},
                                    '{{ addslashes($s->status) }}',
                                    '{{ $s->billing_cycle ?? 'monthly' }}',
                                    '{{ $s->trial_ends_at }}'
                                )"
                            >
                                ✎ Edit
                            </button>

                        </div>

                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="6"
                        style="text-align: center; padding: 60px; color: #9ca3af;"
                    >
                        No subscriptions found.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>


<!-- EDIT SUBSCRIPTION MODAL -->
<div id="editSubModal" class="modal-backdrop">

    <div class="modal-panel">

        <div class="modal-header">

            <h3>
                Edit Subscription
            </h3>

            <button
                type="button"
                class="modal-close"
                onclick="closeModal('editSubModal')"
            >
                &times;
            </button>

        </div>


        <form
            method="post"
            id="editSubForm"
        >

            @csrf
            @method('PUT')


            <!-- STATUS -->
            <div class="form-group">

                <label>
                    Status
                </label>

                <select
                    name="status"
                    id="edit_status"
                    required
                >

                    <option value="active">
                        Active
                    </option>

                    <option value="trial">
                        Trial
                    </option>

                    <option value="pending">
                        Pending
                    </option>

                    <option value="suspended">
                        Suspended
                    </option>

                    <option value="cancelled">
                        Cancelled
                    </option>

                </select>

            </div>


            <!-- BILLING CYCLE -->
            <div class="form-group">

                <label>
                    Billing Cycle
                </label>

                <select
                    name="billing_cycle"
                    id="edit_cycle"
                    required
                >

                    <option value="monthly">
                        Monthly
                    </option>

                    <option value="yearly">
                        Yearly
                    </option>

                </select>

            </div>


            <!-- TRIAL ENDS AT -->
            <div class="form-group">

                <label>
                    Trial Ends At
                </label>

                <input
                    type="date"
                    name="trial_ends_at"
                    id="edit_trial"
                >

            </div>


            <!-- MODAL ACTIONS -->
            <div class="modal-actions">

                <button
                    type="button"
                    class="btn-cancel"
                    onclick="closeModal('editSubModal')"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn-save"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


<script>

    /* ================================
       SEARCH
    ================================= */

    document
        .getElementById('subSearch')
        .addEventListener('keyup', filterSubs);


    /* ================================
       STATUS FILTER
    ================================= */

    document
        .getElementById('subStatus')
        .addEventListener('change', filterSubs);


    /* ================================
       FILTER SUBSCRIPTIONS
    ================================= */

    function filterSubs() {

        const search =
            document
                .getElementById('subSearch')
                .value
                .toLowerCase();

        const status =
            document
                .getElementById('subStatus')
                .value
                .toLowerCase();

        const rows =
            document.querySelectorAll(
                '#subsTable tbody tr'
            );


        rows.forEach(row => {

            /*
             * Ignore empty-state row
             */
            if (row.cells.length < 6) {
                return;
            }


            const tenantName =
                row.cells[0]
                    .innerText
                    .toLowerCase();


            const statusText =
                row.cells[2]
                    .innerText
                    .toLowerCase()
                    .replace('●', '')
                    .trim();


            const matchSearch =
                tenantName.includes(search);


            const matchStatus =
                !status ||
                statusText.includes(status);


            row.style.display =
                (matchSearch && matchStatus)
                    ? ''
                    : 'none';

        });

    }


    /* ================================
       OPEN EDIT MODAL
    ================================= */

    function openEditSubscription(
        id,
        status,
        cycle,
        trialEndsAt
    ) {

        document
            .getElementById('editSubForm')
            .action =
            '/super/subscriptions/' + id;


        document
            .getElementById('edit_status')
            .value =
            status;


        document
            .getElementById('edit_cycle')
            .value =
            cycle;


        document
            .getElementById('edit_trial')
            .value =
            trialEndsAt
                ? trialEndsAt.split(' ')[0]
                : '';


        document
            .getElementById('editSubModal')
            .classList
            .add('show');

    }


    /* ================================
       CLOSE MODAL
    ================================= */

    function closeModal(id) {

        document
            .getElementById(id)
            .classList
            .remove('show');

    }


    /* ================================
       CLOSE WHEN CLICKING BACKDROP
    ================================= */

    document
        .getElementById('editSubModal')
        .addEventListener('click', function(event) {

            if (event.target === this) {
                closeModal('editSubModal');
            }

        });


    /* ================================
       ESCAPE KEY
    ================================= */

    document.addEventListener('keydown', function(event) {

        if (event.key === 'Escape') {

            const modal =
                document.getElementById('editSubModal');

            if (modal.classList.contains('show')) {
                closeModal('editSubModal');
            }

        }

    });

</script>

@endsection