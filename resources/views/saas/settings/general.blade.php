@extends('saas.layout')
@section('title', 'General Settings')
@section('topbar_title', 'General Settings')

@section('content')

<style>
    .gs-header { margin-bottom: 24px; }
    .gs-header h1 { margin: 0; font-size: 28px; letter-spacing: -.7px; color: #111827; }
    .gs-header p { margin: 8px 0 0; color: #6b7280; font-size: 14px; }

    .gs-layout { display: grid; grid-template-columns: 260px 1fr; gap: 20px; align-items: start; }

    /* Left nav */
    .gs-nav { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 10px; }
    .gs-nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 10px; color: #6b7280; font-size: 13px; font-weight: 600; text-decoration: none; transition: all .15s ease; }
    .gs-nav-item:hover { background: #f9fafb; color: #111827; }
    .gs-nav-item.active { background: #ecfdf5; color: #16a34a; }
    .gs-nav-icon { width: 18px; text-align: center; font-size: 14px; }

    /* Right content */
    .gs-content { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 24px; }
    .gs-content-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #eef0f4; }
    .gs-content-header h2 { margin: 0; font-size: 20px; font-weight: 800; color: #111827; }
    .gs-content-header p { margin: 6px 0 0; color: #6b7280; font-size: 13px; }

    .btn-save { display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; background: #16a34a; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-save:hover { background: #15803d; }
    .btn-save:disabled { background: #86efac; cursor: not-allowed; }

    .btn-soft { display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; background: #fff; color: #374151; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; }
    .btn-soft:hover { background: #f9fafb; }
    .btn-danger-soft { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
    .btn-danger-soft:hover { background: #fee2e2; }

    /* Form */
    .form-group { margin-bottom: 18px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 18px; }
    .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="url"],
    .form-group input[type="number"],
    .form-group input[type="password"],
    .form-group select,
    .form-group textarea { width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; font-family: inherit; color: #111827; background: #fff; }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.1); }
    .form-group small { display: block; color: #9ca3af; font-size: 11px; margin-top: 4px; }

    /* Toggle check */
    .check-row { display: flex; align-items: flex-start; gap: 10px; padding: 14px 0; border-bottom: 1px solid #f0f1f4; }
    .check-row:last-child { border-bottom: none; }
    .check-row input[type="checkbox"] { width: 18px; height: 18px; accent-color: #16a34a; margin-top: 2px; cursor: pointer; flex-shrink: 0; }
    .check-row .check-text strong { display: block; font-size: 13px; font-weight: 700; color: #111827; margin-bottom: 3px; }
    .check-row .check-text span { font-size: 12px; color: #6b7280; }

    /* Languages checkboxes */
    .lang-grid { display: flex; flex-wrap: wrap; gap: 12px 24px; margin-top: 10px; }
    .lang-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151; }
    .lang-item input[type="checkbox"] { width: 16px; height: 16px; accent-color: #16a34a; cursor: pointer; }
    .lang-badge { background: #f3f4f6; color: #6b7280; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; margin-left: 6px; }

    /* Upload box */
    .upload-box { border: 2px dashed #d1d5db; border-radius: 12px; padding: 30px 20px; text-align: center; background: #f9fafb; margin-bottom: 12px; transition: all .15s ease; }
    .upload-box:hover { border-color: #16a34a; background: #f0fdf4; }
    .upload-box .upload-icon { font-size: 40px; margin-bottom: 10px; color: #9ca3af; }
    .upload-box .upload-title { font-size: 13px; font-weight: 700; color: #374151; }
    .upload-box .upload-sub { font-size: 11px; color: #9ca3af; margin-top: 4px; }
    .upload-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 12px; }

    /* Backup table */
    .backup-table { width: 100%; border-collapse: collapse; }
    .backup-table th { text-align: left; font-size: 11px; text-transform: uppercase; color: #9ca3af; letter-spacing: .5px; padding: 12px; border-bottom: 1px solid #eef0f4; font-weight: 700; }
    .backup-table td { padding: 14px 12px; border-bottom: 1px solid #f0f1f4; font-size: 13px; color: #374151; }
    .backup-table tr:last-child td { border-bottom: none; }
    .backup-actions { text-align: right; }
    .icon-btn { display: inline-flex; width: 32px; height: 32px; align-items: center; justify-content: center; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; cursor: pointer; margin-left: 4px; text-decoration: none; color: #6b7280; font-size: 14px; }
    .icon-btn:hover { background: #f9fafb; }
    .icon-btn.danger { color: #dc2626; }
    .icon-btn.danger:hover { background: #fef2f2; }

    .info-banner { background: #ecfdf5; border: 1px solid #bbf7d0; color: #065f46; padding: 12px 16px; border-radius: 10px; font-size: 12px; display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px; }

    @media (max-width: 1100px) {
        .gs-layout { grid-template-columns: 1fr; }
        .form-row, .form-row-3 { grid-template-columns: 1fr; }
    }
</style>

<div class="gs-header">
    <h1>General Settings</h1>
    <p>Manage your application branding, company information, and contact details.</p>
</div>

@if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div class="gs-layout">

    {{-- LEFT NAV --}}
    <nav class="gs-nav">
        <a href="?tab=general"  class="gs-nav-item {{ $tab === 'general' ? 'active' : '' }}"><span class="gs-nav-icon">⚙</span> General</a>
         <a href="?tab=branding" class="gs-nav-item {{ $tab === 'branding' ? 'active' : '' }}"><span class="gs-nav-icon">🎨</span> Branding</a> 
        <a href="?tab=appearance" class="gs-nav-item {{ $tab === 'appearance' ? 'active' : '' }}"><span class="gs-nav-icon">✏</span> Appearance</a>
        <a href="?tab=company" class="gs-nav-item {{ $tab === 'company' ? 'active' : '' }}"><span class="gs-nav-icon">🏢</span> Company</a>
        <!-- <a href="?tab=tenant" class="gs-nav-item {{ $tab === 'tenant' ? 'active' : '' }}"><span class="gs-nav-icon">📊</span> Tenant Settings</a> -->
        <a href="?tab=reminders" class="gs-nav-item {{ $tab === 'reminders' ? 'active' : '' }}"><span class="gs-nav-icon">⏰</span> Reminders</a>
        <a href="?tab=landing" class="gs-nav-item {{ $tab === 'landing' ? 'active' : '' }}"><span class="gs-nav-icon">🌐</span> Landing Page</a>
        <a href="?tab=payments" class="gs-nav-item {{ $tab === 'payments' ? 'active' : '' }}"><span class="gs-nav-icon">💳</span> Payments</a>
        <a href="?tab=backup" class="gs-nav-item {{ $tab === 'backup' ? 'active' : '' }}"><span class="gs-nav-icon">🛡</span> Backup</a>
        <a href="?tab=system" class="gs-nav-item {{ $tab === 'system' ? 'active' : '' }}"><span class="gs-nav-icon">⚙</span> System</a>
        <a href="?tab=update" class="gs-nav-item {{ $tab === 'update' ? 'active' : '' }}"><span class="gs-nav-icon">☁</span> Update App</a>
    </nav>

    {{-- RIGHT CONTENT --}}
    <div class="gs-content">

        @if($tab === 'backup')

            {{-- ==================================================
                 BACKUP — No form wrapper, uses JS actions
                 ================================================== --}}
            <div class="gs-content-header">
                <div>
                    <h2>Backup</h2>
                    <p>Create, download and restore database backups securely.</p>
                </div>
                <button type="button" class="btn-save" id="createBackupBtn" onclick="createBackup()">
                    <span id="backupBtnLabel">+ Create Backup Now</span>
                </button>
            </div>

            <div style="margin-bottom: 16px;">
                <strong style="font-size:14px; font-weight:800; color:#111827;">📦 Database Backups</strong>
                <p style="margin:4px 0 0; font-size:12px; color:#9ca3af;">
                    Generate a full SQL backup of the central database. Files are stored outside the public directory and can only be downloaded by authenticated super admins.
                </p>
            </div>

            <div class="info-banner">
                <span>🛡</span>
                <span>Backups are kept outside the public folder and served through a secured, authenticated download link. File names are strictly validated to prevent path traversal.</span>
            </div>

            <table class="backup-table" id="backupTable">
                <thead>
                    <tr>
                        <th>FILE</th>
                        <th>SIZE</th>
                        <th>CREATED</th>
                        <th style="text-align:right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="backupTableBody">
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 40px; color:#9ca3af;">
                            Loading backups...
                        </td>
                    </tr>
                </tbody>
            </table>

        @else

            {{-- ==================================================
                 ALL OTHER TABS — wrapped in the main form
                 ================================================== --}}
            <form method="POST" action="{{ url('/super/settings') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tab" value="{{ $tab }}">

                {{-- GENERAL --}}
                @if($tab === 'general')
                    <div class="gs-content-header">
                        <div><h2>General</h2><p>Application name, currency and core defaults.</p></div>
                        <button type="submit" class="btn-save">✓ Save Settings</button>
                    </div>

                    <div class="form-group">
                        <label>App Name *</label>
                        <input type="text" name="app_name" value="{{ $settings['app_name'] }}" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Currency Code *</label>
                            <input type="text" name="currency_code" value="{{ $settings['currency_code'] }}" required>
                            <small>ISO 4217 code (e.g. USD, EUR, NGN, GHS, ZAR).</small>
                        </div>
                        <div class="form-group">
                            <label>Currency Symbol *</label>
                            <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] }}" required>
                            <small>Displayed before amounts in invoices and UI.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Default Language</label>
                        <select name="default_language">
                            @foreach(['en'=>'English (EN)','fr'=>'French (FR)','ar'=>'Arabic (AR)','es'=>'Spanish (ES)','hi'=>'Hindi (HI)','bn'=>'Bengali (BN)','tr'=>'Turkish (TR)','de'=>'German (DE)','pt'=>'Portuguese (PT)','ur'=>'Urdu (UR)'] as $code => $label)
                                <option value="{{ $code }}" {{ $settings['default_language'] === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <small>Used for the landing page and super admin dashboard when a visitor has not chosen a language.</small>
                    </div>

                    <div class="form-group">
                        <label>Available Languages</label>
                        <small style="margin-bottom:8px;">Enable or disable the languages shown in the language selector. The default language is always enabled.</small>
                        <div class="lang-grid">
                            @foreach(['en'=>'🇬🇧 English (EN)','fr'=>'🇫🇷 French (FR)','ar'=>'🇸🇦 Arabic (AR)','es'=>'🇪🇸 Spanish (ES)','hi'=>'🇮🇳 Hindi (HI)','bn'=>'🇧🇩 Bengali (BN)','tr'=>'🇹🇷 Turkish (TR)','de'=>'🇩🇪 German (DE)','pt'=>'🇵🇹 Portuguese (PT)','ur'=>'🇵🇰 Urdu (UR)'] as $code => $label)
                                <label class="lang-item">
                                    <input type="checkbox" name="available_languages[]" value="{{ $code }}" {{ in_array($code, $settings['available_languages'] ?? []) ? 'checked' : '' }}>
                                    {!! $label !!}
                                    @if($code === 'en')<span class="lang-badge">Default</span>@endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- BRANDING --}}
                @if($tab === 'branding')
                    <div class="gs-content-header">
                        <div><h2>Branding</h2><p>Logo and favicon used across the platform.</p></div>
                        <button type="submit" class="btn-save">✓ Save Settings</button>
                    </div>

                    <div class="form-group">
                        <label>Logo</label>
                        <small style="margin-bottom:8px;">Displayed in the sidebar and emails. Recommended: 200×50px, PNG or SVG.</small>
                        <div class="upload-box">
                            <div class="upload-icon">🖼</div>
                            {{-- FIXED: Pointing directly to the public folder --}}
                            @if(file_exists(public_path('images/super/settings/logo.png')))
                                <img src="{{ asset('images/super/settings/logo.png') }}" alt="Logo" style="max-height: 60px; margin-bottom: 8px;">
                            @else
                                <div class="upload-title">Click to upload logo</div>
                                <div class="upload-sub">PNG, JPG, SVG, WEBP — max 2 MB</div>
                            @endif
                        </div>
                        <input type="file" name="logo" accept="image/*">
                    </div>

                    <div class="form-group" style="margin-top: 24px;">
                        <label>Favicon</label>
                        <small style="margin-bottom:8px;">Shown in browser tabs. Recommended: 32×32px or 64×64px, PNG or ICO.</small>
                        <div class="upload-box">
                            <div class="upload-icon">🌐</div>
                            {{-- FIXED: Pointing directly to the public folder --}}
                            @if(file_exists(public_path('images/super/settings/favicon.png')))
                                <img src="{{ asset('images/super/settings/favicon.png') }}" alt="Favicon" style="max-height: 40px; margin-bottom: 8px;">
                            @else
                                <div class="upload-title">Click to upload favicon</div>
                                <div class="upload-sub">PNG, ICO, SVG — max 1 MB</div>
                            @endif
                        </div>
                        <input type="file" name="favicon" accept="image/*">
                    </div>
                @endif

                {{-- APPEARANCE --}}
                @if($tab === 'appearance')
                    <div class="gs-content-header">
                        <div><h2>Appearance</h2><p>Theme controls, dashboard footer text, and visibility options.</p></div>
                        <button type="submit" class="btn-save">✓ Save Settings</button>
                    </div>

                    <div class="check-row">
                        <input type="checkbox" name="show_site_name" id="show_site_name" {{ $settings['show_site_name'] ? 'checked' : '' }}>
                        <label for="show_site_name" class="check-text">
                            <strong>Aa Show site name</strong>
                            <span>Display the application name next to the brand logo in the sidebar and on the landing page.</span>
                        </label>
                    </div>

                    <div class="check-row">
                        <input type="checkbox" name="show_theme_customizer" id="show_theme_customizer" {{ $settings['show_theme_customizer'] ? 'checked' : '' }}>
                        <label for="show_theme_customizer" class="check-text">
                            <strong>🎨 Show theme customizer button</strong>
                            <span>When enabled, a floating palette button is shown at the bottom-right of the super admin dashboard so you can change the primary color on the fly.</span>
                        </label>
                    </div>

                    <div class="form-group" style="margin-top: 24px;">
                        <label>Dashboard footer text</label>
                        <input type="text" name="dashboard_footer_text" value="{{ $settings['dashboard_footer_text'] }}">
                        <small>Optional text shown at the bottom of every super admin page. Leave empty to hide the footer.</small>
                    </div>
                @endif

                {{-- COMPANY --}}
                @if($tab === 'company')
                    <div class="gs-content-header">
                        <div><h2>Company</h2><p>Contact details displayed on invoices, the landing page, and emails.</p></div>
                        <button type="submit" class="btn-save">✓ Save Settings</button>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" name="company_name" value="{{ $settings['company_name'] }}">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" value="{{ $settings['phone'] }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="{{ $settings['email'] }}">
                        </div>
                        <div class="form-group">
                            <label>Website URL</label>
                            <input type="url" name="website" value="{{ $settings['website'] }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" value="{{ $settings['address'] }}">
                    </div>
                @endif

                {{-- TENANT SETTINGS --}}
                @if($tab === 'tenant')
                    <div class="gs-content-header">
                        <div><h2>Tenant Settings</h2><p>Defaults applied to NEW tenants when they are provisioned.</p></div>
                        <button type="submit" class="btn-save">✓ Save Settings</button>
                    </div>

                    <div class="info-banner">
                        <span>ℹ</span>
                        <span><strong>Defaults for newly created tenants.</strong><br>These values pre-fill the tenant setup screens. Editing them here does not modify existing tenant settings.</span>
                    </div>

                    <div class="form-group">
                        <label>Tenant Application Name</label>
                        <input type="text" name="tenant_app_name" value="{{ $settings['tenant_app_name'] }}">
                        <small>Shown on the tenant dashboard and in the browser title bar.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Tenant Company Name</label>
                            <input type="text" name="tenant_company_name" value="{{ $settings['tenant_company_name'] }}">
                        </div>
                        <div class="form-group">
                            <label>Tenant Email</label>
                            <input type="email" name="tenant_email" value="{{ $settings['tenant_email'] }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Tenant Phone</label>
                            <input type="text" name="tenant_phone" value="{{ $settings['tenant_phone'] }}">
                        </div>
                        <div class="form-group">
                            <label>Tenant Address</label>
                            <input type="text" name="tenant_address" value="{{ $settings['tenant_address'] }}">
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label>Tenant Currency Code</label>
                            <input type="text" name="tenant_currency_code" value="{{ $settings['tenant_currency_code'] }}">
                        </div>
                        <div class="form-group">
                            <label>Tenant Currency Symbol</label>
                            <input type="text" name="tenant_currency_symbol" value="{{ $settings['tenant_currency_symbol'] }}">
                        </div>
                        <div class="form-group">
                            <label>Tenant Default Language</label>
                            <select name="tenant_default_language">
                                <option value="en">English (EN)</option>
                                <option value="fr">French (FR)</option>
                                <option value="ar">Arabic (AR)</option>
                                <option value="es">Spanish (ES)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>🚫 Reserved Subdomains</label>
                        <textarea name="reserved_subdomains" rows="5">{{ $settings['reserved_subdomains'] }}</textarea>
                        <small>Common system subdomains are always reserved, even if not listed here.</small>
                    </div>

                    <div class="check-row">
                        <input type="checkbox" name="demo_tenant_generator" id="demo_tenant_generator" {{ $settings['demo_tenant_generator'] ? 'checked' : '' }}>
                        <label for="demo_tenant_generator" class="check-text">
                            <strong>✎ Demo Tenant Generator</strong>
                            <span>When enabled, newly created tenants are automatically populated with sample demo data.</span>
                        </label>
                    </div>
                @endif

                {{-- REMINDERS --}}
                @if($tab === 'reminders')
                    <div class="gs-content-header">
                        <div><h2>Reminders</h2><p>Automatic reminders sent to tenants before their subscription or trial expires.</p></div>
                        <button type="submit" class="btn-save">✓ Save Settings</button>
                    </div>

                    <div class="check-row">
                        <input type="checkbox" name="reminders_enabled" id="reminders_enabled" {{ $settings['reminders_enabled'] ? 'checked' : '' }}>
                        <label for="reminders_enabled" class="check-text">
                            <strong>📩 Enable subscription expiry reminders</strong>
                            <span>When on, tenants receive a reminder before their paid subscription ends.</span>
                        </label>
                    </div>

                    <div class="form-group" style="margin-top: 14px;">
                        <label>Reminder days (before expiry)</label>
                        <input type="text" name="reminder_days" value="{{ $settings['reminder_days'] }}">
                        <small>Days before the expiry date to send a reminder. Comma-separated, e.g. 7, 3, 1.</small>
                    </div>

                    <div class="check-row">
                        <input type="checkbox" name="trial_reminders_enabled" id="trial_reminders_enabled" {{ $settings['trial_reminders_enabled'] ? 'checked' : '' }}>
                        <label for="trial_reminders_enabled" class="check-text">
                            <strong>⏱ Enable trial-ending reminders</strong>
                            <span>When on, tenants on a free trial receive a reminder before the trial ends.</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>Trial reminder days (before trial ends)</label>
                        <input type="text" name="trial_reminder_days" value="{{ $settings['trial_reminder_days'] }}">
                    </div>
                @endif

                {{-- LANDING PAGE --}}
                @if($tab === 'landing')
                    <div class="gs-content-header">
                        <div><h2>Landing Page</h2><p>Public landing page behavior and visibility.</p></div>
                        <button type="submit" class="btn-save">✓ Save Settings</button>
                    </div>

                    <div class="check-row">
                        <input type="checkbox" name="show_admin_login_link" id="show_admin_login_link" {{ $settings['show_admin_login_link'] ? 'checked' : '' }}>
                        <label for="show_admin_login_link" class="check-text">
                            <strong>↪ Show admin login link</strong>
                            <span>Display the super admin login link in the landing page footer.</span>
                        </label>
                    </div>
                @endif

                {{-- PAYMENTS --}}
                @if($tab === 'payments')
                    <div class="gs-content-header">
                        <div><h2>Payments</h2><p>Bank details for offline / wire transfer payments.</p></div>
                        <button type="submit" class="btn-save">✓ Save Settings</button>
                    </div>

                    <div class="form-row">
                        <div class="form-group"><label>Bank Name</label><input type="text" name="bank_name" value="{{ $settings['bank_name'] }}"></div>
                        <div class="form-group"><label>Account Holder Name</label><input type="text" name="account_holder_name" value="{{ $settings['account_holder_name'] }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Account Number</label><input type="text" name="account_number" value="{{ $settings['account_number'] }}"></div>
                        <div class="form-group"><label>IBAN</label><input type="text" name="iban" value="{{ $settings['iban'] }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>SWIFT / BIC Code</label><input type="text" name="swift_code" value="{{ $settings['swift_code'] }}"></div>
                        <div class="form-group"><label>Branch</label><input type="text" name="branch" value="{{ $settings['branch'] }}"></div>
                    </div>
                    <div class="form-group">
                        <label>Payment Instructions</label>
                        <textarea name="payment_instructions" rows="3">{{ $settings['payment_instructions'] }}</textarea>
                    </div>
                @endif

                {{-- SYSTEM --}}
                @if($tab === 'system')
                    <div class="gs-content-header">
                        <div><h2>System</h2><p>Core system settings and diagnostics.</p></div>
                        <button type="submit" class="btn-save">✓ Save Settings</button>
                    </div>

                    <div class="form-group">
                        <label>Timezone</label>
                        <input type="text" name="system_timezone" value="{{ $settings['system_timezone'] }}" placeholder="UTC">
                    </div>

                    <div class="check-row">
                        <input type="checkbox" name="system_debug" id="system_debug" {{ $settings['system_debug'] ? 'checked' : '' }}>
                        <label for="system_debug" class="check-text">
                            <strong>Enable debug mode</strong>
                            <span>When enabled, detailed errors are shown. Only enable during development.</span>
                        </label>
                    </div>
                @endif

                {{-- UPDATE APP --}}
                @if($tab === 'update')
                    <div class="gs-content-header">
                        <div><h2>Update App</h2><p>Check for and install application updates.</p></div>
                        <button type="submit" class="btn-save">✓ Save Settings</button>
                    </div>

                    <div class="form-group">
                        <label>Update Channel</label>
                        <select name="update_channel">
                            <option value="stable" {{ $settings['update_channel'] === 'stable' ? 'selected' : '' }}>Stable</option>
                            <option value="beta" {{ $settings['update_channel'] === 'beta' ? 'selected' : '' }}>Beta</option>
                        </select>
                    </div>
                @endif

            </form>

        @endif

    </div>
</div>

<script>
    // ==========================================================
    // BACKUP — JavaScript
    // ==========================================================
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('backupTable')) {
            loadBackups();
        }
    });

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function formatDate(timestamp) {
        const d = new Date(timestamp * 1000);
        const pad = n => String(n).padStart(2, '0');
        return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate())
             + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    }

    function loadBackups() {
        fetch('/super/settings/general/backup', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => renderBackups(data.files || []))
        .catch(err => {
            console.error('Load backups failed:', err);
            document.getElementById('backupTableBody').innerHTML =
                '<tr><td colspan="4" style="text-align:center; padding:40px; color:#dc2626;">Failed to load backups.</td></tr>';
        });
    }

    function renderBackups(files) {
        const tbody = document.getElementById('backupTableBody');
        if (!files.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:#9ca3af;">No backups yet. Click <strong>Create Backup Now</strong> to generate your first backup.</td></tr>';
            return;
        }

        tbody.innerHTML = files.map(file => `
            <tr>
                <td><code style="background:#f3f4f6; padding:3px 8px; border-radius:4px; font-size:12px;">${file.name}</code></td>
                <td>${formatBytes(file.size)}</td>
                <td>${formatDate(file.created)}</td>
                <td style="text-align:right;">
                    <a href="/super/settings/general/backup/${file.name}/download" class="icon-btn" title="Download">⬇</a>
                    <button type="button" class="icon-btn danger" onclick="deleteBackup('${file.name}')" title="Delete">🗑</button>
                </td>
            </tr>
        `).join('');
    }

    function createBackup() {
        const btn = document.getElementById('createBackupBtn');
        const label = document.getElementById('backupBtnLabel');
        btn.disabled = true;
        label.textContent = 'Creating backup...';

        fetch('/super/settings/general/backup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({})
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            label.textContent = '+ Create Backup Now';
            if (data.success) {
                loadBackups();
            } else {
                alert('Backup failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            btn.disabled = false;
            label.textContent = '+ Create Backup Now';
            console.error(err);
            alert('Backup failed. Check console.');
        });
    }

    function deleteBackup(filename) {
        if (!confirm('Delete backup "' + filename + '"? This cannot be undone.')) return;

        fetch('/super/settings/general/backup/' + filename, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) loadBackups();
            else alert('Delete failed: ' + (data.message || 'Unknown error'));
        })
        .catch(err => {
            console.error(err);
            alert('Delete failed. Check console.');
        });
    }
</script>

@endsection