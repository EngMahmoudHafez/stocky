@extends('saas.layout')
@section('title', 'Payment ' . $payment->id)
@section('topbar_title', 'Payment Details')

@section('content')

<style>
    .ps-header { margin-bottom: 22px; }
    .ps-breadcrumb { font-size: 12px; color: #9ca3af; margin-bottom: 8px; }
    .ps-breadcrumb a { color: #6b7280; }
    .ps-title-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; }
    .ps-title h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.6px; color: #111827; }
    .ps-title .meta { margin-top: 8px; font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 10px; }
    .ps-status-pill { padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; display: inline-block; }
    .ps-status-pill.active { background: #ecfdf5; color: #16a34a; }
    .ps-status-pill.trial { background: #fff7ed; color: #ea580c; }
    .ps-status-pill.pending { background: #fffbeb; color: #f59e0b; }
    .ps-status-pill.cancelled { background: #fef2f2; color: #dc2626; }
    .ps-actions { display: flex; gap: 10px; }
    .btn-soft { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 9px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; }
    .btn-soft:hover { background: #f9fafb; }
    .btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 9px; border: none; background: #16a34a; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; }
    .btn-primary:hover { background: #15803d; }
    .btn-danger { background: #dc2626; }
    .btn-danger:hover { background: #b91c1c; }

    .ps-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    .ps-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; margin-bottom: 16px; overflow: hidden; }
    .ps-card-header { padding: 16px 20px; border-bottom: 1px solid #eef0f4; display: flex; justify-content: space-between; align-items: center; }
    .ps-card-header h3 { margin: 0; font-size: 14px; font-weight: 800; color: #111827; }
    .ps-card-body { padding: 20px; }

    .info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px; }
    .info-item .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #9ca3af; margin-bottom: 6px; }
    .info-item .value { font-size: 14px; color: #111827; font-weight: 600; }

    .amount-box {
        background: #eef2ff;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .amount-box .amount-label { font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; letter-spacing: .5px; }
    .amount-box .amount-value { font-size: 30px; font-weight: 800; color: #111827; letter-spacing: -1px; margin-top: 6px; }
    .amount-box .invoice-num { text-align: right; }
    .amount-box .invoice-num .inv { color: #16a34a; font-size: 13px; font-weight: 700; }
    .amount-box .invoice-num .cycle { color: #6b7280; font-size: 12px; margin-top: 4px; }

    .gateway-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 16px 20px; border-bottom: 1px solid #eef0f4; }
    .gateway-item { display: flex; align-items: center; gap: 12px; }
    .gateway-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .gateway-icon.blue { background: #eff6ff; }
    .gateway-icon.green { background: #ecfdf5; }
    .gateway-item .label { font-size: 11px; text-transform: uppercase; color: #9ca3af; font-weight: 700; letter-spacing: .5px; }
    .gateway-item .value { font-size: 14px; color: #111827; font-weight: 700; margin-top: 2px; }

    .payment-id-row { padding: 16px 20px; border-bottom: 1px solid #eef0f4; display: flex; align-items: center; gap: 12px; }
    .payment-id-row .label { font-size: 11px; text-transform: uppercase; color: #9ca3af; font-weight: 700; letter-spacing: .5px; }
    .payment-id-row code { background: #f3f4f6; color: #7c3aed; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }

    .notes-row { padding: 16px 20px; }
    .notes-row .label { font-size: 11px; text-transform: uppercase; color: #9ca3af; font-weight: 700; letter-spacing: .5px; margin-bottom: 6px; }
    .notes-row .value { font-size: 13px; color: #4b5563; }

    .tenant-block { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
    .tenant-avatar-lg { width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .tenant-block .name { font-size: 15px; font-weight: 800; color: #111827; }
    .tenant-block .slug { font-size: 12px; color: #9ca3af; margin-top: 2px; }
    .tenant-block .email { font-size: 12px; color: #9ca3af; }

    .btn-full { display: flex; justify-content: center; align-items: center; gap: 8px; width: 100%; padding: 11px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; }
    .btn-full:hover { background: #f9fafb; }

    .plan-name-lg { font-size: 15px; font-weight: 800; color: #111827; }
    .plan-prices { font-size: 12px; color: #6b7280; margin-top: 4px; }
    .sub-meta { font-size: 12px; color: #6b7280; margin-top: 6px; }

    .action-buttons { display: flex; flex-direction: column; gap: 10px; }
</style>

<div class="ps-header">
    <div class="ps-breadcrumb">
        <a href="{{ url('/super/payments/transactions') }}">Transactions</a> &nbsp;/&nbsp;
        <span>INV-{{ str_pad($payment->id, 10, '0', STR_PAD_LEFT) }}</span>
    </div>
    <div class="ps-title-row">
        <div class="ps-title">
            <h1>Payment INV-{{ str_pad($payment->id, 10, '0', STR_PAD_LEFT) }}</h1>
            <div class="meta">
                <span class="ps-status-pill {{ $payment->status }}">● {{ ucfirst($payment->status) }}</span>
                <span>{{ \Carbon\Carbon::parse($payment->created_at)->format('M d, Y \a\t h:i A') }}</span>
            </div>
        </div>
        <div class="ps-actions">
            <a href="{{ url('/super/payments/' . $payment->id . '/invoice/download') }}" target="_blank" class="btn-soft">⬇ Download PDF</a>
            @if($payment->status !== 'active')
                <form method="POST" action="{{ url('/super/payments/' . $payment->id . '/mark-paid') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-primary">✓ Mark Paid</button>
                </form>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif

<div class="ps-grid">
    {{-- LEFT: Payment info --}}
    <div>
        <div class="ps-card">
            <div class="ps-card-header">
                <h3>▤ Payment Information</h3>
                <span class="ps-status-pill {{ $payment->status }}">● {{ ucfirst($payment->status) }}</span>
            </div>

            <div class="ps-card-body">
                <div class="amount-box">
                    <div>
                        <div class="amount-label">Amount</div>
                        <div class="amount-value">USD ${{ number_format($payment->amount, 2) }}</div>
                    </div>
                    <div class="invoice-num">
                        <div class="inv">INV-{{ str_pad($payment->id, 10, '0', STR_PAD_LEFT) }}</div>
                        <div class="cycle">{{ ucfirst($payment->billing_cycle ?? 'Yearly') }}</div>
                    </div>
                </div>
            </div>

            <div class="gateway-grid">
                <div class="gateway-item">
                    <div class="gateway-icon blue">💳</div>
                    <div>
                        <div class="label">Gateway</div>
                        <div class="value">{{ $payment->gateway ?? 'PayPal' }}</div>
                    </div>
                </div>
                <div class="gateway-item">
                    <div class="gateway-icon green">📅</div>
                    <div>
                        <div class="label">Billing Cycle</div>
                        <div class="value">{{ ucfirst($payment->billing_cycle ?? 'Yearly') }}</div>
                    </div>
                </div>
            </div>

            <div class="payment-id-row">
                <span class="label">Gateway Payment ID</span>
                <code>demo_{{ strtolower($payment->gateway ?? 'paypal') }}_{{ str_replace('-', '', $payment->created_at->format('YmdHis')) }}</code>
            </div>

            <div class="info-row" style="padding: 16px 20px 0;">
                <div class="info-item">
                    <div class="label">Created</div>
                    <div class="value">{{ $payment->created_at->format('M d, Y') }}<br><span style="font-size:12px;color:#9ca3af;">{{ $payment->created_at->format('h:i A') }}</span></div>
                </div>
                <div class="info-item">
                    <div class="label">Updated</div>
                    <div class="value">{{ $payment->updated_at->format('M d, Y') }}<br><span style="font-size:12px;color:#9ca3af;">{{ $payment->updated_at->format('h:i A') }}</span></div>
                </div>
            </div>

            <div class="notes-row">
                <div class="label">Notes</div>
                <div class="value">{{ $payment->notes ?? 'Renewal — awaiting confirmation' }}</div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Tenant, Subscription, Plan, Actions --}}
    <div>
        {{-- Tenant --}}
        <div class="ps-card">
            <div class="ps-card-header">
                <h3>▣ Tenant</h3>
            </div>
            <div class="ps-card-body">
                <div class="tenant-block">
                    <div class="tenant-avatar-lg">{{ strtoupper(substr($payment->tenant->name ?? '?', 0, 1)) }}</div>
                    <div>
                        <div class="name">{{ $payment->tenant->name ?? 'Unknown' }}</div>
                        <div class="slug">{{ $payment->tenant->slug ?? '' }}</div>
                        <div class="email">✉ {{ $payment->tenant->email ?? '' }}</div>
                    </div>
                </div>
                <a href="{{ url('/super/tenants/' . $payment->tenant_id) }}" class="btn-full">→ View Tenant</a>
            </div>
        </div>

        {{-- Subscription --}}
        <div class="ps-card">
            <div class="ps-card-header">
                <h3>▤ Subscription</h3>
                <span class="ps-status-pill active">● {{ ucfirst($payment->status) }}</span>
            </div>
            <div class="ps-card-body">
                <div class="plan-name-lg">{{ $payment->plan->name ?? '—' }}</div>
                <div class="sub-meta">
                    {{ ucfirst($payment->billing_cycle ?? 'Yearly') }} billing<br>
                    Ends {{ $payment->ends_at ? \Carbon\Carbon::parse($payment->ends_at)->format('M d, Y') : \Carbon\Carbon::parse($payment->created_at)->addYear()->format('M d, Y') }}
                </div>
                <a href="{{ url('/super/subscriptions') }}" class="btn-full" style="margin-top: 14px;">👁 View Subscription</a>
            </div>
        </div>

        {{-- Plan --}}
        <div class="ps-card">
            <div class="ps-card-header">
                <h3>💠 Plan</h3>
            </div>
            <div class="ps-card-body">
                <div class="plan-name-lg">{{ $payment->plan->name ?? '—' }}</div>
                <div class="plan-prices">
                    ${{ number_format($payment->plan->monthly_price ?? 0, 2) }}/mo
                    · ${{ number_format($payment->plan->yearly_price ?? 0, 2) }}/yr
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="ps-card">
            <div class="ps-card-header">
                <h3>⚙ Actions</h3>
            </div>
            <div class="ps-card-body">
                <div class="action-buttons">
                    <form method="POST" action="{{ url('/super/payments/' . $payment->id . '/mark-paid') }}">
                        @csrf
                        <button type="submit" class="btn-full" style="color: #16a34a;">✓ Mark as Paid</button>
                    </form>
                    <form method="POST" action="{{ url('/super/payments/' . $payment->id . '/mark-failed') }}">
                        @csrf
                        <button type="submit" class="btn-full" style="color: #dc2626;">✕ Mark as Failed</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection