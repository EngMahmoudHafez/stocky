<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice INV-{{ str_pad($payment->id, 10, '0', STR_PAD_LEFT) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 40px; font-family: 'Inter', sans-serif; background: #fff; color: #111827; font-size: 14px; }
        .invoice { max-width: 800px; margin: 0 auto; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 24px; border-bottom: 2px solid #16a34a; margin-bottom: 30px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand-logo { width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; }
        .brand-name { font-size: 22px; font-weight: 800; letter-spacing: -.5px; }
        .brand-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .invoice-info { text-align: right; }
        .invoice-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9ca3af; letter-spacing: 1px; }
        .invoice-num { font-size: 22px; font-weight: 800; color: #16a34a; margin-top: 4px; }
        .invoice-date { font-size: 13px; color: #6b7280; margin-top: 4px; }

        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px; }
        .field-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9ca3af; letter-spacing: 1px; margin-bottom: 8px; }
        .field-value { font-size: 14px; font-weight: 600; }
        .field-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { text-align: left; padding: 12px 16px; background: #f9fafb; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: .5px; border-bottom: 2px solid #e5e7eb; }
        td { padding: 16px; border-bottom: 1px solid #eef0f4; font-size: 14px; }

        .total-row { display: flex; justify-content: flex-end; margin-bottom: 40px; }
        .total-box { width: 320px; }
        .total-line { display: flex; justify-content: space-between; padding: 10px 0; font-size: 14px; }
        .total-line.grand { padding: 16px 0; border-top: 2px solid #e5e7eb; font-size: 18px; font-weight: 800; color: #111827; }
        .total-line.grand .label { font-weight: 800; }

        .status-badge { display: inline-block; padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .status-badge.active { background: #ecfdf5; color: #16a34a; }
        .status-badge.trial { background: #fffbeb; color: #f59e0b; }
        .status-badge.pending { background: #fffbeb; color: #f59e0b; }
        .status-badge.cancelled { background: #fef2f2; color: #dc2626; }

        .footer { text-align: center; padding-top: 24px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; }

        .no-print { text-align: center; margin-bottom: 24px; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; border: none; background: #16a34a; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; margin: 0 4px; }
        .btn-secondary { background: #f3f4f6; color: #374151; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn" onclick="window.print()">🖨 Print / Save as PDF</button>
    <a href="{{ url('/super/payments/' . $payment->id) }}" class="btn btn-secondary">← Back</a>
</div>

<div class="invoice">

    <div class="header">
        <div class="brand">
            <div class="brand-logo">S</div>
            <div>
                <div class="brand-name">Inventory</div>
                <div class="brand-sub">Subscription Billing</div>
            </div>
        </div>
        <div class="invoice-info">
            <div class="invoice-label">Invoice</div>
            <div class="invoice-num">INV-{{ str_pad($payment->id, 10, '0', STR_PAD_LEFT) }}</div>
            <div class="invoice-date">{{ $payment->created_at->format('M d, Y') }}</div>
        </div>
    </div>

    <div class="row">
        <div>
            <div class="field-title">Billed To</div>
            <div class="field-value">{{ $payment->tenant->name ?? '—' }}</div>
            <div class="field-sub">{{ $payment->tenant->slug ?? '' }}</div>
            <div class="field-sub">{{ $payment->tenant->email ?? '' }}</div>
        </div>
        <div style="text-align:right;">
            <div class="field-title">Payment Status</div>
            <div style="margin-top: 6px;">
                <span class="status-badge {{ $payment->status }}">● {{ ucfirst($payment->status) }}</span>
            </div>
            <div class="field-sub" style="margin-top: 10px;">Gateway: <strong>{{ $payment->gateway ?? '—' }}</strong></div>
            <div class="field-sub">Cycle: <strong>{{ ucfirst($payment->billing_cycle ?? 'Yearly') }}</strong></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Plan</th>
                <th>Cycle</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $payment->plan->name ?? 'Subscription' }} Subscription</td>
                <td>{{ $payment->plan->name ?? '—' }}</td>
                <td>{{ ucfirst($payment->billing_cycle ?? 'Yearly') }}</td>
                <td style="text-align: right; font-weight: 700;">${{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-row">
        <div class="total-box">
            <div class="total-line"><span class="label">Subtotal</span><span>${{ number_format($payment->amount, 2) }}</span></div>
            <div class="total-line"><span class="label">Tax</span><span>$0.00</span></div>
            <div class="total-line grand"><span class="label">Total</span><span>${{ number_format($payment->amount, 2) }} USD</span></div>
        </div>
    </div>

    <div class="footer">
        Thank you for your business!<br>
        @include('partials.brand-credit')
    </div>

</div>

</body>
</html>