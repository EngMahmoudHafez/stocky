@extends('saas.layout')
@section('title', 'Invoices')
@section('topbar_title', 'Invoices')

@section('content')
<style>
    .inv-header h1 { margin: 0; font-size: 28px; letter-spacing: -.7px; color: #111827; }
    .inv-header p { margin: 8px 0 24px; color: #6b7280; font-size: 14px; }
    .filter-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 18px 20px; margin-bottom: 16px; display: flex; gap: 14px; align-items: center; }
    .filter-card input { flex: 1; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; }
    .filter-card select { padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; }
    .btn-filter { background: #16a34a; color: #fff; padding: 10px 20px; border-radius: 8px; border: none; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-clear { background: #f3f4f6; color: #374151; padding: 10px 20px; border-radius: 8px; border: none; font-size: 13px; font-weight: 700; cursor: pointer; }
    .table-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; overflow: hidden; }
    .inv-table { width: 100%; border-collapse: collapse; }
    .inv-table th { text-align: left; font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: .5px; padding: 14px 18px; border-bottom: 1px solid #eef0f4; }
    .inv-table td { padding: 16px 18px; border-bottom: 1px solid #f0f1f4; font-size: 13px; color: #374151; }
    .pill { padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .pill.paid { background: #ecfdf5; color: #16a34a; }
    .pill.pending { background: #fffbeb; color: #f59e0b; }
    .pill.failed { background: #fef2f2; color: #dc2626; }
    .invoice-link { color: #16a34a; font-weight: 700; text-decoration: none; }
    .btn-icon { display: inline-flex; width: 32px; height: 32px; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; font-size: 14px; }
</style>

<div class="inv-header">
    <h1>Invoices</h1>
    <p>All generated invoices for subscription payments.</p>
</div>

<div class="filter-card">
    <input type="text" placeholder="Search invoice number or tenant...">
    <select><option>All statuses</option><option>Paid</option><option>Pending</option><option>Failed</option></select>
    <button class="btn-filter">▼ Filter</button>
    <button class="btn-clear">Clear</button>
</div>

<div class="table-card">
    <div style="padding: 18px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eef0f4;">
        <strong style="color: #111827; font-size: 14px;">▤ Invoices</strong>
        <span style="color: #9ca3af; font-size: 12px;">{{ $invoices->count() }} invoices</span>
    </div>
    <table class="inv-table">
        <thead>
            <tr>
                <th>INVOICE NUMBER</th><th>TENANT</th><th>PLAN</th><th>AMOUNT</th><th>TAX</th><th>TOTAL</th><th>GATEWAY</th><th>STATUS</th><th>DATE</th><th style="text-align:right;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td><a href="#" class="invoice-link">INV-{{ str_pad($inv->id, 10, '0', STR_PAD_LEFT) }}</a></td>
                    <td>{{ $inv->tenant->name ?? '—' }}</td>
                    <td>{{ $inv->plan->name ?? '—' }}</td>
                    <td>${{ number_format($inv->amount, 2) }}</td>
                    <td>—</td>
                    <td><strong>${{ number_format($inv->amount, 2) }}</strong></td>
                    <td>{{ $inv->gateway ?? 'Bank Transfer' }}</td>
                    <td><span class="pill {{ $inv->status === 'active' ? 'paid' : ($inv->status === 'trial' ? 'pending' : 'failed') }}">● {{ ucfirst($inv->status) }}</span></td>
                    <td>{{ $inv->created_at->format('M d, Y') }}</td>
                    <td style="text-align:right;">
    <a href="{{ url('/super/payments/'.$inv->id.'/invoice/download') }}" target="_blank" class="btn-icon" title="Download" style="text-decoration: none;">⬇</a>
    <a href="{{ url('/super/payments/'.$inv->id) }}" class="btn-icon" title="View" style="text-decoration: none;">👁</a>
</td>
                </tr>
            @empty
                <tr><td colspan="10" style="text-align:center; padding:60px; color:#9ca3af;">No invoices found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection