@extends('saas.layout')
@section('title', 'WhatsApp Settings')
@section('topbar_title', 'WhatsApp Settings')

@section('content')

<style>
    .wa-header { margin-bottom: 24px; }
    .wa-header h1 { margin: 0; font-size: 28px; letter-spacing: -.7px; color: #111827; font-weight: 800; }
    .wa-header p { margin: 8px 0 0; color: #6b7280; font-size: 14px; }

    .wa-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
    @media (max-width: 1100px) { .wa-grid { grid-template-columns: 1fr; } }

    .wa-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 22px; margin-bottom: 20px; }
    .wa-card-head { display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid #eef0f4; margin-bottom: 18px; }
    .wa-card-head h3 { margin: 0; font-size: 15px; font-weight: 750; color: #111827; display: flex; align-items: center; gap: 8px; }
    .wa-card-head h3 span { font-size: 18px; color: #6b7280; }

    .wa-field { margin-bottom: 18px; }
    .wa-field label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .wa-field input[type=text],
    .wa-field select,
    .wa-field textarea {
        width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px;
        font-size: 14px; font-family: inherit; outline: none; background: #fff; color: #111827;
    }
    .wa-field input:focus,
    .wa-field select:focus,
    .wa-field textarea:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22,163,74,.1);
    }
    .wa-field input[readonly] { background: #f9fafb; color: #6b7280; cursor: not-allowed; }
    .wa-field small { display: block; color: #9ca3af; font-size: 11.5px; margin-top: 5px; line-height: 1.5; }

    .wa-toggle-row {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 10px 0;
    }
    .wa-toggle-row input[type=checkbox] {
        width: 18px; height: 18px; accent-color: #16a34a;
        margin-top: 2px; cursor: pointer; flex-shrink: 0;
    }
    .wa-toggle-row label { cursor: pointer; margin: 0; }
    .wa-toggle-row strong { display: block; font-size: 13px; font-weight: 700; color: #111827; margin-bottom: 3px; }
    .wa-toggle-row span { font-size: 12px; color: #6b7280; }

    .wa-template {
        background: #f9fafb; border: 1px solid #eef0f4;
        border-radius: 12px; padding: 18px; margin-bottom: 16px;
    }
    .wa-template-head {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 14px; padding-bottom: 10px;
        border-bottom: 1px solid #eef0f4;
    }
    .wa-template-head strong { font-size: 13px; font-weight: 700; color: #374151; }
    .wa-remove-btn {
        display: inline-flex; align-items: center; gap: 5px;
        background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;
        font-size: 11.5px; font-weight: 700; padding: 5px 11px;
        border-radius: 6px; cursor: pointer;
    }
    .wa-remove-btn:hover { background: #fee2e2; }

    .wa-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 700;
        cursor: pointer; border: 1px solid #e5e7eb; background: #fff; color: #374151;
        text-decoration: none; min-height: 40px;
    }
    .wa-btn:hover { background: #f9fafb; }
    .wa-btn-primary {
        background: linear-gradient(135deg, #6366f1, #7c3aed);
        border-color: #6366f1; color: #fff;
        box-shadow: 0 5px 14px rgba(99,102,241,.20);
    }
    .wa-btn-primary:hover { background: linear-gradient(135deg, #5558e8, #6d28d9); }
    .wa-btn-sm { padding: 6px 12px; font-size: 12px; min-height: 32px; }

    .wa-usage { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 22px; }
    .wa-usage h3 { margin: 0 0 14px; font-size: 15px; font-weight: 750; color: #111827; display: flex; align-items: center; gap: 8px; }
    .wa-usage h3 span { font-size: 18px; color: #6b7280; }
    .wa-usage-empty { font-size: 12.5px; color: #9ca3af; padding: 12px 0; }
    .wa-usage-number { font-size: 36px; font-weight: 800; color: #111827; letter-spacing: -1px; }
    .wa-usage-label { font-size: 12px; color: #6b7280; margin-top: 4px; }

    .wa-info-banner {
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;
        padding: 12px 16px; border-radius: 10px; font-size: 12.5px;
        display: flex; gap: 10px; align-items: flex-start;
        margin-bottom: 18px; line-height: 1.6;
    }
    .wa-info-banner code {
        background: rgba(30, 64, 175, 0.1); padding: 1px 6px; border-radius: 4px;
        font-size: 11.5px; font-family: ui-monospace, monospace;
    }

    /* DARK MODE */
    body.dark-mode .wa-card,
    body.dark-mode .wa-usage { background: #1e293b !important; border-color: #334155 !important; }
    body.dark-mode .wa-card-head { border-bottom-color: #334155 !important; }
    body.dark-mode .wa-card-head h3 { color: #fff !important; }
    body.dark-mode .wa-field label { color: #cbd5e1 !important; }
    body.dark-mode .wa-field input[type=text],
    body.dark-mode .wa-field select,
    body.dark-mode .wa-field textarea {
        background: #0f172a !important; border-color: #334155 !important; color: #fff !important;
    }
    body.dark-mode .wa-toggle-row strong { color: #fff !important; }
    body.dark-mode .wa-toggle-row span { color: #94a3b8 !important; }
    body.dark-mode .wa-template { background: #0f172a !important; border-color: #334155 !important; }
    body.dark-mode .wa-template-head { border-bottom-color: #334155 !important; }
    body.dark-mode .wa-template-head strong { color: #cbd5e1 !important; }
    body.dark-mode .wa-usage h3 { color: #fff !important; }
    body.dark-mode .wa-usage-number { color: #fff !important; }
</style>

@php
    $templates = old('templates', $settings['templates'] ?? []);
    if (!is_array($templates) || empty($templates)) {
        $templates = [
            ['key' => 'invoice_created',  'name' => 'Invoice Created',   'message' => 'Hello customer, your invoice has been created.'],
            ['key' => 'payment_received', 'name' => 'Payment Received',  'message' => 'Hi customer, we received your payment.'],
            ['key' => 'order_created',    'name' => 'Order Confirmation', 'message' => 'Hello customer, your order has been confirmed.'],
        ];
    }
@endphp

<div class="wa-header">
    <h1>WhatsApp Settings</h1>
    <p>Enable the WhatsApp Business API integration, choose a provider, manage default templates and monitor usage across tenants.</p>
</div>

@if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">
        <strong>Please check:</strong>
        <ul style="margin:7px 0 0 18px;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ url('/super/settings/whatsapp') }}">
    @csrf
    @method('PUT')

    <div class="wa-grid">

        {{-- LEFT COLUMN --}}
        <div>

            {{-- INTEGRATION CARD --}}
            <div class="wa-card">
                <div class="wa-card-head">
                    <h3><span>💬</span> Integration</h3>
                </div>

                <div class="wa-toggle-row">
                    <input type="checkbox" name="enabled" id="wa_enabled" value="1"
                           {{ !empty($settings['enabled']) ? 'checked' : '' }}>
                    <label for="wa_enabled">
                        <strong>Enable WhatsApp integration globally</strong>
                        <span>When off, tenants cannot send WhatsApp messages even if connected.</span>
                    </label>
                </div>

                <div class="wa-field" style="margin-top: 18px;">
                    <label>Provider</label>
                    <select name="provider">
                        <option value="meta_cloud_api" {{ ($settings['provider'] ?? '') === 'meta_cloud_api' ? 'selected' : '' }}>
                            Meta Cloud API
                        </option>
                    </select>
                    <small>Only the official Meta WhatsApp Business Cloud API is supported in this version.</small>
                </div>

                <div class="wa-field" style="margin-bottom: 0;">
                    <label>Tenant webhook URL</label>
                    <input type="text" value="{{ url('/whatsapp/webhook/{tenant_id}') }}" readonly>
                    <small>Each tenant configures their Meta app webhook to this URL with their own tenant ID. Status callbacks (delivered / read / failed) flow back here.</small>
                </div>
            </div>

            {{-- DEFAULT TEMPLATES CARD --}}
            <div class="wa-card">
                <div class="wa-card-head">
                    <h3><span>📄</span> Default Templates</h3>
                    <button type="button" class="wa-btn wa-btn-sm" onclick="addTemplate()">
                        + Add
                    </button>
                </div>

                <div class="wa-info-banner">
                    <span>ℹ️</span>
                    <span>
                        Seeded into every newly provisioned tenant. Variables:
                        <code>@{{customer_name}}</code>,
                        <code>@{{invoice_number}}</code>,
                        <code>@{{total_amount}}</code>,
                        <code>@{{company_name}}</code>,
                        <code>@{{payment_status}}</code>,
                        <code>@{{order_status}}</code>.
                    </span>
                </div>

                <div id="templatesList">
                    @foreach($templates as $i => $tpl)
                        @php
                            $tplKey  = $tpl['key'] ?? '';
                            $tplName = $tpl['name'] ?? '';
                            $tplMsg  = $tpl['message'] ?? '';
                        @endphp
                        <div class="wa-template" data-index="{{ $i }}">
                            <div class="wa-template-head">
                                <strong>Template #{{ $i + 1 }}</strong>
                                <button type="button" class="wa-remove-btn" onclick="this.closest('.wa-template').remove()">
                                    🗑 Remove
                                </button>
                            </div>

                            <div class="wa-field">
                                <label>Key</label>
                                <input type="text" name="templates[{{ $i }}][key]"
                                       value="{{ $tplKey }}" placeholder="invoice_created">
                            </div>

                            <div class="wa-field">
                                <label>Name</label>
                                <input type="text" name="templates[{{ $i }}][name]"
                                       value="{{ $tplName }}" placeholder="Invoice Created">
                            </div>

                            <div class="wa-field" style="margin-bottom: 0;">
                                <label>Message</label>
                                <textarea name="templates[{{ $i }}][message]" rows="3"
                                          placeholder="Hello customer, your invoice...">{{ $tplMsg }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="submit" class="wa-btn wa-btn-primary">
                    ✓ Save Settings
                </button>
            </div>
        </div>

        {{-- RIGHT COLUMN: USAGE --}}
        <div>
            <div class="wa-usage">
                <h3><span>📊</span> Usage This Month</h3>

                @if(empty($usageCount) || $usageCount === 0)
                    <div class="wa-usage-empty">No WhatsApp messages sent by any tenant this month yet.</div>
                @else
                    <div class="wa-usage-number">{{ number_format($usageCount) }}</div>
                    <div class="wa-usage-label">messages sent this month</div>
                @endif
            </div>
        </div>

    </div>
</form>

<script>
    let templateIndex = {{ is_array($templates) ? count($templates) : 0 }};

    function addTemplate() {
        const list = document.getElementById('templatesList');
        const html = `
            <div class="wa-template" data-index="${templateIndex}">
                <div class="wa-template-head">
                    <strong>Template #${templateIndex + 1}</strong>
                    <button type="button" class="wa-remove-btn" onclick="this.closest('.wa-template').remove()">
                        🗑 Remove
                    </button>
                </div>

                <div class="wa-field">
                    <label>Key</label>
                    <input type="text" name="templates[${templateIndex}][key]" placeholder="custom_key">
                </div>

                <div class="wa-field">
                    <label>Name</label>
                    <input type="text" name="templates[${templateIndex}][name]" placeholder="Custom Template">
                </div>

                <div class="wa-field" style="margin-bottom: 0;">
                    <label>Message</label>
                    <textarea name="templates[${templateIndex}][message]" rows="3" placeholder="Hello customer, ..."></textarea>
                </div>
            </div>
        `;
        list.insertAdjacentHTML('beforeend', html);
        templateIndex++;
    }
</script>

@endsection