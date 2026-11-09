@extends('saas.layout')

@section('title', isset($tenant) ? 'Edit Tenant' : 'Create Tenant')
@section('topbar_title', isset($tenant) ? 'Edit Tenant' : 'Create Tenant')

@section('content')

<style>
    .create-page-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; }
    .create-panel { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(17,24,39,.025); margin-bottom: 20px; }
    .create-panel-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #eef0f4; }
    .create-panel-header h2 { margin: 0; color: #111827; font-size: 16px; font-weight: 750; }
    .create-panel-header span { color: #9ca3af; font-size: 14px; }
    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: #374151; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; color: #111827; background: #fff; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
    .form-group small { display: block; color: #9ca3af; font-size: 11px; margin-top: 6px; }
    .input-with-icon { position: relative; }
    .input-with-icon span { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
    .input-with-suffix { display: flex; }
    .input-with-suffix input { border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0; }
    .input-with-suffix .suffix { display: flex; align-items: center; padding: 0 12px; background: #f3f4f6; border: 1px solid #e5e7eb; border-left: 0; border-top-right-radius: 8px; border-bottom-right-radius: 8px; color: #6b7280; font-size: 13px; }
    .btn-action { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 20px; border-radius: 9px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-action:hover { background: #f9fafb; }
    .btn-primary { color: #fff; border-color: #16a34a; background: #16a34a; }
    .btn-primary:hover { background: #15803d; border-color: #15803d; }
    .how-it-works { position: sticky; top: 90px; }
    .how-title { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .how-item { display: flex; gap: 10px; margin-bottom: 15px; font-size: 13px; color: #6b7280; line-height: 1.5; }
    .how-icon { width: 20px; height: 20px; border-radius: 50%; background: #ecfdf5; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; margin-top: 2px; }
    @media (max-width: 1100px) { .create-page-grid { grid-template-columns: 1fr; } .form-row { grid-template-columns: 1fr; } .how-it-works { position: relative; top: 0; } }
</style>

@php
    $isEdit     = isset($tenant);
    $baseDomain = env('SAAS_BASE_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost');
    $dbPrefix   = env('TENANT_DB_PREFIX', 'stocky_tenant_');
@endphp

<div class="page-heading">
    <div>
        <h1>{{ $isEdit ? 'Edit Tenant' : 'Create Tenant' }}</h1>
        <p>{{ $isEdit ? 'Update the tenant details and workspace settings.' : 'Create a tenant using a database you have already created in MySQL or cPanel.' }}</p>
    </div>
</div>

<div class="create-page-grid">

    <div>
        <div class="create-panel">
            <div class="create-panel-header">
                <span>?</span>
                <h2>Tenant Details</h2>
            </div>

            <form method="post" action="{{ $isEdit ? url('/super/tenants/'.$tenant->id) : url('/super/tenants') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="form-row">
                    <div class="form-group">
                        <label>Company Name*</label>
                        <input name="name" value="{{ old('name', $tenant->name ?? '') }}" placeholder="Acme Inc." required>
                    </div>

                    <div class="form-group">
                        <label>Subdomain*</label>
                        <div class="input-with-suffix">
                            <input name="slug" value="{{ old('slug', $tenant->slug ?? '') }}" placeholder="acme" required>
                            <div class="suffix">.{{ $baseDomain }}</div>
                        </div>
                        <small>Lowercase letters, numbers, and hyphens only.</small>
                    </div>
                </div>

                <div class="create-panel-header" style="margin-top: 30px;">
                    <span>??</span>
                    <h2>Admin Account</h2>
                </div>
                <p style="font-size: 12px; color: #6b7280; margin-top: -10px; margin-bottom: 15px;">
                    These credentials will allow access to the tenant admin area.
                </p>

                <div class="form-row">
                    <div class="form-group">
                        <label>Admin Email*</label>
                        <div class="input-with-icon">
                            <input name="email" type="email" value="{{ old('email', $tenant->email ?? '') }}" placeholder="admin@company.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Admin Password*</label>
                        <div class="input-with-icon">
                            <input name="password" type="password"
                                   placeholder="{{ $isEdit ? 'Leave blank to keep current password' : 'Min. 8 characters' }}"
                                   {{ $isEdit ? '' : 'required' }}>
                            <span>?</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Owner Phone</label>
                        <input name="phone" type="tel" value="{{ old('phone', $tenant->phone ?? '') }}" placeholder="+1 555 123 4567">
                        <small>Optional. Used for SMS subscription reminders.</small>
                    </div>
                </div>

                <div class="create-panel-header" style="margin-top: 30px;">
                    <span>?</span>
                    <h2>Language Preference</h2>
                </div>
                <p style="font-size: 12px; color: #6b7280; margin-top: -10px; margin-bottom: 15px;">
                    Email notifications will be sent in this language (if a translation exists).
                </p>

                <div class="form-row" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label>Language</label>
                        <select name="language">
                            <option value="en" {{ old('language', $tenant->language ?? 'en') == 'en' ? 'selected' : '' }}>English (Default)</option>
                            <option value="es" {{ old('language', $tenant->language ?? '') == 'es' ? 'selected' : '' }}>Spanish</option>
                            <option value="fr" {{ old('language', $tenant->language ?? '') == 'fr' ? 'selected' : '' }}>French</option>
                            <option value="de" {{ old('language', $tenant->language ?? '') == 'de' ? 'selected' : '' }}>German</option>
                        </select>
                    </div>
                </div>

                {{-- Manual tenant database credentials: shared-hosting friendly. --}}
                @unless($isEdit)
                    <div class="create-panel-header" style="margin-top:30px;">
                        <span>DB</span>
                        <h2>Tenant Database (Manual Setup)</h2>
                    </div>
                    <p style="font-size:12px;color:#6b7280;margin-top:-10px;margin-bottom:15px;">
                        Create an empty database and database user in XAMPP/phpMyAdmin or cPanel first, then enter their credentials below. This form will not create a database or delete existing tables.
                    </p>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Database Host*</label>
                            <input name="database_host" value="{{ old('database_host', env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1'))) }}" placeholder="localhost" required>
                        </div>
                        <div class="form-group">
                            <label>Database Port*</label>
                            <input name="database_port" type="number" min="1" max="65535" value="{{ old('database_port', env('TENANT_DB_PORT', env('DB_PORT', 3306))) }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Database Name*</label>
                            <input name="database_name" value="{{ old('database_name') }}" placeholder="stocky_tenant_acme" pattern="[A-Za-z0-9_]+" required>
                            <small>Use the exact database name shown in phpMyAdmin or cPanel.</small>
                        </div>
                        <div class="form-group">
                            <label>Database Username*</label>
                            <input name="database_username" value="{{ old('database_username') }}" placeholder="database_user" pattern="[A-Za-z0-9_@.-]+" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Database Password</label>
                            <input name="database_password" type="password" value="" autocomplete="new-password" placeholder="Enter database user password (if set)">
                            <small>Leave blank only if this database user has no password.</small>
                        </div>
                    </div>
                    @if($errors->has('database_name') || $errors->has('database_host') || $errors->has('database_username') || $errors->has('database_password'))
                        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:16px;font-size:12px;">
                            @foreach(['database_name','database_host','database_username','database_password'] as $field)
                                @foreach($errors->get($field) as $error)<div>{{ $error }}</div>@endforeach
                            @endforeach
                        </div>
                    @endif
                @endunless

                <div class="create-panel-header" style="margin-top: 30px;">
                    <span>?</span>
                    <h2>Subscription</h2>
                </div>
                <p style="font-size: 12px; color: #6b7280; margin-top: -10px; margin-bottom: 15px;">
                    Set the initial subscription billing cycle for the tenant.
                </p>

                <div class="form-row">
                    <div class="form-group">
                        <label>Plan*</label>
                        <select name="plan_id" required>
                            <option value="">-- Select plan --</option>
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}" {{ old('plan_id', $tenant->plan_id ?? '') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} â ${{ $p->monthly_price }}/mo
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Billing Cycle*</label>
                        <select name="billing_cycle" required>
                            <option value="monthly" {{ old('billing_cycle', $tenant->billing_cycle ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly"  {{ old('billing_cycle', $tenant->billing_cycle ?? '') == 'yearly'  ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-action btn-primary">{{ $isEdit ? 'Update Tenant' : 'Create Tenant' }}</button>
                    <a href="{{ url('/super/tenants') }}" class="btn-action">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="create-panel how-it-works">
        <div class="how-title">
            <span style="color: #6366f1;">?</span> How It Works
        </div>

        <div class="how-item">
            <div class="how-icon">?</div>
            <span>Fill in the tenant details, admin credentials, and choose a plan.</span>
        </div>

        <div class="how-item">
            <div class="how-icon">?</div>
            <span>Create an empty MySQL database and user first, then enter the connection details in the form.</span>
        </div>

        <div class="how-item">
            <div class="how-icon">?</div>
            <span>The tenant's schema is migrated, seed data is installed, and the admin user is created.</span>
        </div>

        <div class="how-item">
            <div class="how-icon">?</div>
            <span>Plan module flags are synced to the tenant's <code>settings</code> table.</span>
        </div>

        <div class="how-item" style="color: #d97706;">
            <div class="how-icon" style="background: #fffbeb; color: #d97706;">!</div>
            <span><strong>Important:</strong> The database user must have permission to create and alter tables inside the selected database.</span>
        </div>
    </div>

</div>

@endsection
