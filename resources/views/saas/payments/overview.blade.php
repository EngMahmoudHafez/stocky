@extends('saas.layout')
@section('title', 'Payments Overview')
@section('topbar_title', 'Payments Overview')

@section('content')
<style>
    .pay-header h1 { margin: 0; font-size: 28px; letter-spacing: -.7px; color: #111827; }
    .pay-header p { margin: 8px 0 24px; color: #6b7280; font-size: 14px; }
    .pay-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
    .pay-stat { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 20px; display: flex; align-items: flex-start; gap: 14px; }
    .pay-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .pay-stat-icon.green { background: linear-gradient(135deg, #10b981, #34d399); color: #fff; }
    .pay-stat-icon.teal { background: linear-gradient(135deg, #14b8a6, #5eead4); color: #fff; }
    .pay-stat-icon.blue { background: linear-gradient(135deg, #3b82f6, #60a5fa); color: #fff; }
    .pay-stat-icon.orange { background: linear-gradient(135deg, #f97316, #fb923c); color: #fff; }
    .pay-stat-text .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #9ca3af; margin-bottom: 4px; }
    .pay-stat-text .value { font-size: 24px; font-weight: 800; color: #111827; letter-spacing: -1px; }
    .pay-mini-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
    .pay-mini { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 18px 20px; display: flex; justify-content: space-between; align-items: center; }
    .pay-mini .mini-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; }
    .pay-mini .mini-value { font-size: 20px; font-weight: 800; color: #111827; margin-top: 6px; }
    .pay-mini .mini-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .pay-mini .mini-icon.green { background: #ecfdf5; color: #16a34a; }
    .pay-mini .mini-icon.amber { background: #fffbeb; color: #f59e0b; }
    .pay-mini .mini-icon.red { background: #fef2f2; color: #ef4444; }
    .pay-mini .mini-icon.blue { background: #eff6ff; color: #3b82f6; }
    .pay-row { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px; }
    .pay-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 20px; }
    .pay-card h3 { margin: 0 0 4px; font-size: 16px; font-weight: 800; color: #111827; }
    .pay-card p.card-sub { margin: 0 0 18px; font-size: 13px; color: #9ca3af; }
    .bar-chart { display: flex; align-items: flex-end; gap: 8px; height: 200px; padding: 20px 0 0; border-bottom: 1px solid #eef0f4; }
    .bar { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; }
    .bar-fill { width: 100%; background: #eef2ff; border: 2px solid #6366f1; border-radius: 4px 4px 0 0; min-height: 4px; }
    .bar-label { font-size: 10px; color: #9ca3af; margin-top: 8px; white-space: nowrap; }
    .gateway-list { list-style: none; padding: 0; margin: 0; }
    .gateway-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f1f4; font-size: 14px; }
    .gateway-item:last-child { border-bottom: none; }
    .gateway-name { font-weight: 700; color: #111827; }
    .gateway-sub { font-size: 11px; color: #9ca3af; margin-top: 2px; }
    .gateway-amount { font-weight: 800; color: #111827; }
    .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    .table th { text-align: left; font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: .5px; padding: 12px 8px; border-bottom: 1px solid #eef0f4; }
    .table td { padding: 14px 8px; border-bottom: 1px solid #f0f1f4; font-size: 13px; color: #374151; }
    .pill { padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; display: inline-block; }
    .pill.pending { background: #fffbeb; color: #f59e0b; }
    .pill.paid { background: #ecfdf5; color: #16a34a; }
    .pill.failed { background: #fef2f2; color: #dc2626; }
</style>

<div class="pay-header">
    <h1>Payments Overview</h1>
    <p>Revenue, transactions, and payment analytics.</p>
</div>

<div class="pay-stats">
    <div class="pay-stat">
        <div class="pay-stat-icon green">$</div>
        <div class="pay-stat-text">
            <div class="label">Total Revenue</div>
            <div class="value">${{ number_format($stats['total_revenue'], 2) }}</div>
        </div>
    </div>
    <div class="pay-stat">
        <div class="pay-stat-icon teal">↻</div>
        <div class="pay-stat-text">
            <div class="label">Monthly Revenue</div>
            <div class="value">${{ number_format($stats['monthly_revenue'], 2) }}</div>
        </div>
    </div>
    <div class="pay-stat">
        <div class="pay-stat-icon blue">▤</div>
        <div class="pay-stat-text">
            <div class="label">Total Transactions</div>
            <div class="value">{{ $stats['total_transactions'] }}</div>
        </div>
    </div>
    <div class="pay-stat">
        <div class="pay-stat-icon orange">%</div>
        <div class="pay-stat-text">
            <div class="label">Total Tax</div>
            <div class="value">${{ number_format($stats['total_tax'], 2) }}</div>
        </div>
    </div>
</div>

<div class="pay-mini-stats">
    <div class="pay-mini">
        <div>
            <div class="mini-label">Paid</div>
            <div class="mini-value">{{ $stats['paid'] }}</div>
        </div>
        <div class="mini-icon green">✓</div>
    </div>
    <div class="pay-mini">
        <div>
            <div class="mini-label">Pending</div>
            <div class="mini-value">{{ $stats['pending'] }}</div>
        </div>
        <div class="mini-icon amber">⏳</div>
    </div>
    <div class="pay-mini">
        <div>
            <div class="mini-label">Failed</div>
            <div class="mini-value">{{ $stats['failed'] }}</div>
        </div>
        <div class="mini-icon red">✕</div>
    </div>
    <div class="pay-mini">
        <div>
            <div class="mini-label">Refunded</div>
            <div class="mini-value">{{ $stats['refunded'] }}</div>
        </div>
        <div class="mini-icon blue">↺</div>
    </div>
</div>

<div class="pay-row">
    <div class="pay-card">
        <h3>Revenue (Last 12 Months)</h3>
        <p class="card-sub">Monthly totals from active subscriptions.</p>
        <div class="bar-chart">
            @foreach($revenueChart as $m)
                @php $h = $m['value'] > 0 ? min(180, max(4, ($m['value'] / 1000) * 100)) : 4; @endphp
                <div class="bar" title="${{ number_format($m['value'], 2) }}">
                    <div class="bar-fill" style="height: {{ $h }}px;"></div>
                    <span class="bar-label">{{ $m['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="pay-card">
        <h3>By Gateway</h3>
        <p class="card-sub">Transaction count per gateway.</p>
        <ul class="gateway-list">
            @foreach($byGateway as $gw => $count)
                <li class="gateway-item">
                    <div>
                        <div class="gateway-name">{{ $gw }}</div>
                        <div class="gateway-sub">{{ $count }} transaction{{ $count === 1 ? '' : 's' }}</div>
                    </div>
                    <div class="gateway-amount">$0.00</div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="pay-card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3>Recent Payments</h3>
            <p class="card-sub" style="margin: 4px 0 0;">Latest subscription payments.</p>
        </div>
        <a href="{{ url('/super/payments/transactions') }}" class="btn-outline" style="text-decoration:none;">View all</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>INVOICE</th><th>TENANT</th><th>PLAN</th><th>AMOUNT</th><th>GATEWAY</th><th>STATUS</th><th>DATE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentPayments as $p)
                <tr>
                    <td><a href="#" style="color: #16a34a; font-weight: 600;">INV-{{ str_pad($p->id, 10, '0', STR_PAD_LEFT) }}</a></td>
                    <td>{{ $p->tenant->name ?? '—' }}</td>
                    <td>{{ $p->plan->name ?? '—' }}</td>
                    <td><strong>${{ number_format($p->amount, 2) }}</strong></td>
                    <td>{{ $p->gateway ?? 'Bank Transfer' }}</td>
                    <td><span class="pill {{ $p->status === 'active' ? 'paid' : ($p->status === 'trial' ? 'pending' : 'failed') }}">● {{ ucfirst($p->status) }}</span></td>
                    <td>{{ $p->created_at->format('M d, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection