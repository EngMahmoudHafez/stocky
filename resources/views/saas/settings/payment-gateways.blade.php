@extends('saas.layout')
@section('title', 'Payment Gateway Settings')
@section('topbar_title', 'Payment Gateway Settings')

@section('content')

<style>
    .pg-header h1 { margin: 0; font-size: 28px; letter-spacing: -.7px; color: #111827; }
    .pg-header p { margin: 8px 0 24px; color: #6b7280; font-size: 14px; }

    .gateway-tabs { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .gateway-tab { padding: 10px 18px; border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; font-size: 13px; font-weight: 700; color: #374151; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .gateway-tab:hover { background: #f9fafb; }
    .gateway-tab.active { border-color: #16a34a; background: #ecfdf5; color: #16a34a; }
    .gateway-tab .status-dot { width: 8px; height: 8px; border-radius: 50%; background: #16a34a; }
    .gateway-tab.disabled .status-dot { background: #d1d5db; }

    .row { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    .card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 22px; margin-bottom: 16px; }
    .card-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 16px; margin-bottom: 20px; border-bottom: 1px solid #eef0f4; }
    .card-header .title-group { display: flex; gap: 14px; align-items: flex-start; }
    .gateway-logo { width: 44px; height: 44px; border-radius: 10px; background: #eff6ff; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .card-header h3 { margin: 0; font-size: 16px; font-weight: 800; }
    .card-header p { margin: 4px 0 0; font-size: 12px; color: #6b7280; }

    /* Enable toggle */
    .enable-toggle { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #374151; }
    .toggle-switch { position: relative; width: 44px; height: 24px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; inset: 0; background: #e5e7eb; border-radius: 999px; cursor: pointer; transition: .2s; }
    .toggle-slider::before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .2s; }
    .toggle-switch input:checked + .toggle-slider { background: #16a34a; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }

    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .form-group input, .form-group textarea { width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; font-family: inherit; }
    .form-group input:focus, .form-group textarea:focus { border-color: #16a34a; }
    .form-group small { display: block; color: #9ca3af; font-size: 11px; margin-top: 4px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .env-options { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .env-option { padding: 16px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; text-align: left; background: #fff; }
    .env-option.selected { border-color: #16a34a; background: #ecfdf5; }
    .env-option strong { display: block; font-size: 14px; margin-bottom: 4px; }
    .env-option span { font-size: 12px; color: #6b7280; }

    .setup-list { list-style: none; padding: 0; margin: 0; counter-reset: step; }
    .setup-list li { counter-increment: step; padding: 12px 0 12px 36px; position: relative; font-size: 13px; color: #4b5563; line-height: 1.5; border-bottom: 1px solid #f0f1f4; }
    .setup-list li:last-child { border-bottom: none; }
    .setup-list li::before { content: counter(step); position: absolute; left: 0; top: 12px; width: 24px; height: 24px; border-radius: 50%; background: #ecfdf5; color: #16a34a; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
    .code-pill { background: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 6px; font-family: monospace; font-size: 11px; }

    .btn-save { padding: 12px 24px; border-radius: 9px; border: none; background: #16a34a; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-save:hover { background: #15803d; }
    .info-note { font-size: 12px; color: #16a34a; display: flex; align-items: center; gap: 6px; margin-top: 16px; }
    .warn-note { font-size: 12px; color: #f59e0b; display: flex; align-items: center; gap: 6px; margin-top: 6px; }

    .how-it-works-list { list-style: none; padding: 0; margin: 0; }
    .how-it-works-list li { padding: 10px 0 10px 24px; position: relative; font-size: 13px; color: #4b5563; line-height: 1.5; }
    .how-it-works-list li::before { content: '●'; position: absolute; left: 0; top: 10px; color: #16a34a; font-size: 12px; }
</style>

<div class="pg-header">
    <h1>Payment Gateway Settings</h1>
    <p>Configure API credentials for each payment gateway used in subscription checkout.</p>
</div>

@if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif

{{-- Tabs --}}
<div class="gateway-tabs">
    @foreach($gateways as $key => $gw)
        <a href="{{ url('/super/settings/payment-gateways?gateway=' . $key) }}"
           class="gateway-tab {{ $active === $key ? 'active' : '' }} {{ empty($enabled[$key]) ? 'disabled' : '' }}">
            <span class="status-dot"></span>
            {{ $gw['name'] }}
        </a>
    @endforeach
</div>

<form method="POST" action="{{ url('/super/settings/payment-gateways/' . $active) }}">
    @csrf

    <div class="row">
        <div>
            {{-- Header card with enable toggle --}}
            <div class="card">
                <div class="card-header">
                    <div class="title-group">
                        <div class="gateway-logo">{{ $activeGateway['icon'] }}</div>
                        <div>
                            <h3>{{ $activeGateway['name'] }}</h3>
                            <p>{{ $activeGateway['desc'] }}</p>
                        </div>
                    </div>
                    <div class="enable-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_enabled" {{ $activeEnabled ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <span>{{ $activeEnabled ? 'Active' : 'Disabled' }}</span>
                    </div>
                </div>

                {{-- Cash & Online gateways get a simple how-it-works list --}}
                @if($active === 'cash')
                    <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 800;">ℹ How It Works</h4>
                    <ul class="how-it-works-list">
                        <li>Customer selects <strong>Cash Payment</strong> at checkout.</li>
                        <li>Tenant receives the payment in person and marks it as received.</li>
                        <li>No API keys or third-party accounts needed.</li>
                        <li>Best for in-person or local businesses.</li>
                    </ul>
                @elseif($active === 'online')
                    <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 800;">ℹ How It Works</h4>
                    <ul class="how-it-works-list">
                        <li>Customer selects <strong>Online Payment</strong> at checkout.</li>
                        <li>They are shown your custom payment link (PayPal.me, Wise, bank, etc.).</li>
                        <li>Once they pay, they mark the payment as completed.</li>
                        <li>Simple, manual, no API setup required.</li>
                    </ul>
                @elseif($active === 'bank_transfer')
                    <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 800;">ℹ How It Works</h4>
                    <ul class="how-it-works-list">
                        <li>Customer selects <strong>Bank Transfer</strong> at checkout and sees your bank details.</li>
                        <li>Customer makes the transfer and uploads a <strong>proof of payment</strong> (image or PDF).</li>
                        <li>The order is marked as <strong>Pending Verification</strong> until you review and approve it.</li>
                        <li>Once approved, the tenant workspace is provisioned automatically.</li>
                    </ul>
                    <p style="margin-top: 16px; font-size: 12px; color: #6b7280;">Configure your bank account details in <strong>Settings → General Settings</strong>.</p>
                @else
                    {{-- Standard gateways (Stripe, PayPal, Paystack, etc.) --}}
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 12px; font-size: 15px; font-weight: 800;">⚙ Environment</h4>
                        <div class="env-options">
                            <button type="button" class="env-option selected">
                                <strong>🧪 Sandbox / Test</strong>
                                <span>Use test API keys for development</span>
                            </button>
                            <button type="button" class="env-option">
                                <strong>🛡 Live / Production</strong>
                                <span>Use production API keys</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Currency Configuration (only for real gateways) --}}
            @if(!in_array($active, ['cash', 'online', 'bank_transfer']))
                <div class="card">
                    <h4 style="margin: 0 0 16px; font-size: 15px; font-weight: 800;">💱 Currency Configuration</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Supported Currencies</label>
                            <input type="text" name="currencies"
                                   value="{{ $activeSettings['currencies'] ?? $activeGateway['currencies'] }}">
                            <small>Comma-separated ISO currency codes this gateway accepts.</small>
                        </div>
                        <div class="form-group">
                            <label>Default Currency</label>
                            <input type="text" name="default_currency"
                                   value="{{ $activeSettings['default_currency'] ?? $activeGateway['default_currency'] }}">
                            <small>Fallback when system currency isn't supported.</small>
                        </div>
                    </div>
                </div>
            @endif

            {{-- API Credentials --}}
            <div class="card">
                <h4 style="margin: 0 0 16px; font-size: 15px; font-weight: 800;">🔑 API Credentials</h4>

                @if($active === 'cash')
                    <div class="form-group">
                        <label>Payment Instructions (shown to tenant)</label>
                        <textarea name="instructions" rows="4" placeholder="e.g. Please pay the exact amount to the admin on the agreed date.">{{ $activeSettings['instructions'] ?? '' }}</textarea>
                        <small>These instructions will be shown to the customer at checkout.</small>
                    </div>
                @elseif($active === 'online')
                    <div class="form-group">
                        <label>Payment Link (URL)</label>
                        <input type="text" name="payment_link"
                               value="{{ $activeSettings['payment_link'] ?? '' }}"
                               placeholder="https://paypal.me/yourname">
                        <small>Customer will be redirected to this link to pay.</small>
                    </div>
                    <div class="form-group">
                        <label>Instructions (shown to tenant)</label>
                        <textarea name="instructions" rows="3" placeholder="e.g. After payment, please click Mark as Paid.">{{ $activeSettings['instructions'] ?? '' }}</textarea>
                    </div>
                @elseif($active === 'bank_transfer')
                    <p style="color: #6b7280; font-size: 13px;">Bank Transfer does not need API credentials. Configure your bank account details in <strong>Settings → General Settings → Bank Details</strong>.</p>
                @else
                    @foreach($activeGateway['fields'] as $fieldKey => $fieldLabel)
                        <div class="form-group">
                            <label>{{ $fieldLabel }}{{ str_contains(strtolower($fieldLabel), 'secret') ? ' 🔒' : '' }}</label>
                            <input type="{{ str_contains(strtolower($fieldLabel), 'secret') ? 'password' : 'text' }}"
                                   name="{{ $fieldKey }}"
                                   placeholder="{{ $fieldKey === 'public_key' || $fieldKey === 'client_id' ? 'pk_test_...' : 'Leave as-is to keep the current value' }}"
                                   value="{{ $fieldKey !== 'secret_key' && $fieldKey !== 'client_secret' && $fieldKey !== 'webhook_secret' ? ($activeSettings[$fieldKey] ?? '') : '' }}">
                            @if(str_contains(strtolower($fieldLabel), 'secret'))
                                <small>Leave as-is to keep the current value.</small>
                            @endif
                        </div>
                    @endforeach
                @endif

                <button type="submit" class="btn-save">✓ Save {{ $activeGateway['name'] }} Settings</button>

                @if(!in_array($active, ['cash', 'online', 'bank_transfer']))
                    <div class="info-note">✓ Secret keys are encrypted at rest.</div>
                    <div class="warn-note">⚠ Never share your secret keys.</div>
                @endif
            </div>
        </div>

        {{-- Setup Guide Sidebar --}}
        <div>
            <div class="card">
                <h4 style="margin: 0 0 12px; font-size: 15px; font-weight: 800;">ℹ Setup Guide</h4>

                @if($active === 'stripe')
                    <ol class="setup-list">
                        <li>Create an account on Stripe's dashboard.</li>
                        <li>Copy your API keys from the developer settings.</li>
                        <li>Paste them here and save. Start with test/sandbox mode.</li>
                        <li>
                            Set up webhook URL for payment verification:<br>
                            <span class="code-pill">https://your-domain.example/webhook/stripe</span><br>
                            <small style="color:#6b7280;">Events to subscribe to:</small><br>
                            <span class="code-pill">checkout.session.completed</span>
                            <span class="code-pill">checkout.session.async_payment_succeeded</span>
                            <span class="code-pill">checkout.session.async_payment_failed</span>
                        </li>
                        <li>Switch to live mode when ready for production.</li>
                    </ol>
                @elseif($active === 'paypal')
                    <ol class="setup-list">
                        <li>Create an account on PayPal's dashboard.</li>
                        <li>Copy your API keys from the developer settings.</li>
                        <li>Paste them here and save. Start with test/sandbox mode.</li>
                        <li>
                            Set up webhook URL for payment verification:<br>
                            <span class="code-pill">https://your-domain.example/webhook/paypal</span><br>
                            <small style="color:#6b7280;">Events to subscribe to:</small><br>
                            <span class="code-pill">PAYMENT.CAPTURE.COMPLETED</span>
                            <span class="code-pill">PAYMENT.SALE.COMPLETED</span>
                            <span class="code-pill">PAYMENT.CAPTURE.REFUNDED</span>
                        </li>
                        <li>Switch to live mode when ready for production.</li>
                    </ol>
                @elseif($active === 'paystack')
                    <ol class="setup-list">
                        <li>Create an account on Paystack's dashboard.</li>
                        <li>Copy your API keys from the developer settings.</li>
                        <li>Paste them here and save. Start with test/sandbox mode.</li>
                        <li>
                            Set up webhook URL for payment verification:<br>
                            <span class="code-pill">https://your-domain.example/webhook/paystack</span><br>
                            <small style="color:#6b7280;">Events to subscribe to:</small><br>
                            <span class="code-pill">charge.success</span>
                            <span class="code-pill">charge.failed</span>
                            <span class="code-pill">refund.processed</span>
                            <span class="code-pill">transfer.reversed</span>
                        </li>
                        <li>Switch to live mode when ready for production.</li>
                    </ol>
                @elseif($active === 'flutterwave')
                    <ol class="setup-list">
                        <li>Create an account on Flutterwave's dashboard.</li>
                        <li>Copy your API keys from the developer settings.</li>
                        <li>Paste them here and save. Start with test/sandbox mode.</li>
                        <li>Set up webhook URL: <span class="code-pill">https://your-domain.example/webhook/flutterwave</span></li>
                        <li>Switch to live mode when ready for production.</li>
                    </ol>
                @elseif($active === 'mollie')
                    <ol class="setup-list">
                        <li>Create an account on Mollie's dashboard.</li>
                        <li>Copy your API key from the developer settings.</li>
                        <li>Paste it here and save.</li>
                        <li>Set up webhook URL: <span class="code-pill">https://your-domain.example/webhook/mollie</span></li>
                    </ol>
                @elseif($active === 'cash')
                    <ol class="setup-list">
                        <li>Enable Cash Payment using the toggle above.</li>
                        <li>Write clear payment instructions that the customer will see at checkout.</li>
                        <li>Save the settings.</li>
                        <li>Cash payments will appear in your <strong>Pending Payments</strong> section for confirmation.</li>
                    </ol>
                @elseif($active === 'online')
                    <ol class="setup-list">
                        <li>Enable Online Payment using the toggle above.</li>
                        <li>Paste your payment link (PayPal.me, Wise, Stripe Payment Link, etc.).</li>
                        <li>Add instructions shown to the customer.</li>
                        <li>Save the settings.</li>
                    </ol>
                @elseif($active === 'bank_transfer')
                    <ol class="setup-list">
                        <li>Enable the toggle above to show Bank Transfer as a checkout option.</li>
                        <li>Add your bank account details in <strong>General Settings</strong>.</li>
                        <li>Review pending payments in the <strong>Payments</strong> section.</li>
                    </ol>
                @endif

                @if(!in_array($active, ['cash', 'online', 'bank_transfer']))
                    <div class="info-note">✓ Secret keys are encrypted at rest.</div>
                    <div class="warn-note">⚠ Never share your secret keys.</div>
                @endif
            </div>
        </div>
    </div>
</form>

@endsection