<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create your workspace  {{ $settings['app_name'] ?? 'Poraone' }}</title>

    @if(file_exists(public_path('images/super/settings/favicon.png')))
        <link rel="icon" href="{{ asset('images/super/settings/favicon.png') }}" type="image/png">
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; }

        .hero { flex: 0 0 50%; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; padding: 60px; color: #fff; }
        .hero-inner { max-width: 480px; }
        .hero-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.3); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 30px; font-size: 28px; }
        .hero h2 { font-size: 36px; font-weight: 800; line-height: 1.15; margin-bottom: 16px; }
        .hero p { font-size: 15px; color: rgba(255,255,255,0.85); margin-bottom: 30px; line-height: 1.7; }
        .hero ul { list-style: none; }
        .hero li { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; font-size: 14px; }
        .hero li::before { content: '?'; width: 26px; height: 26px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; }

        .panel { flex: 1; background: #fff; display: flex; align-items: center; justify-content: center; padding: 40px; overflow-y: auto; }
        .form-wrap { width: 100%; max-width: 440px; }

        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 30px; text-decoration: none; color: inherit; }
        .brand img { max-height: 44px; border-radius: 10px; }
        .brand span { font-size: 20px; font-weight: 800; color: #111827; }
        .brand-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 20px; }

        h1 { font-size: 26px; font-weight: 800; color: #111827; margin-bottom: 6px; }
        .subtitle { font-size: 14px; color: #6b7280; margin-bottom: 28px; }

        .form-group { margin-bottom: 18px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 7px; }
        input, select { width: 100%; padding: 11px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: inherit; color: #111827; outline: none; }
        input:focus, select:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
        .hint { font-size: 11px; color: #9ca3af; margin-top: 5px; }

        .subdomain-wrap { display: flex; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
        .subdomain-wrap:focus-within { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
        .subdomain-wrap input { border: 0; box-shadow: none; flex: 1; border-radius: 0; }
        .subdomain-suffix { padding: 0 14px; background: #f9fafb; color: #6b7280; font-size: 13px; font-weight: 600; display: flex; align-items: center; border-left: 1px solid #e5e7eb; }

        .cycle-options { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .cycle-option input { display: none; }
        .cycle-card { padding: 12px 14px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; }
        .cycle-option input:checked + .cycle-card { border-color: #10b981; background: #ecfdf5; }
        .cycle-name { font-size: 14px; font-weight: 700; color: #111827; }
        .cycle-price { font-size: 12px; color: #6b7280; margin-top: 2px; }

        .btn { width: 100%; padding: 14px; border: none; border-radius: 10px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(16,185,129,0.25); margin-top: 8px; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(16,185,129,0.35); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .alert { padding: 12px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 18px; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        .back { display: block; text-align: center; margin-top: 16px; color: #6b7280; font-size: 13px; font-weight: 600; text-decoration: none; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #9ca3af; }

        @media (max-width: 900px) { .hero { display: none; } .panel { padding: 30px 20px; } }
    </style>
</head>
<body>

<div class="hero">
    <div class="hero-inner">
        <div class="hero-icon">?</div>
        <h2>Launch your business in minutes</h2>
        <p>Get your own dedicated workspace with a custom subdomain, full inventory management, and everything you need to run your business.</p>
        <ul>
            <li>Dedicated subdomain &amp; database</li>
            <li>POS, inventory, purchases &amp; sales</li>
            <li>Multi-warehouse &amp; barcode support</li>
            <li>Ready in under 60 seconds</li>
        </ul>
    </div>
</div>

<div class="panel">
    <div class="form-wrap">

        <a href="/" class="brand">
            @if(file_exists(public_path('images/super/settings/logo.png')))
                <img src="{{ asset('images/super/settings/logo.png') }}" alt="{{ $settings['app_name'] ?? 'Poraone' }}">
            @else
                <div class="brand-icon">{{ substr($settings['app_name'] ?? 'P', 0, 1) }}</div>
            @endif
            <span>{{ $settings['app_name'] ?? 'Poraone' }}</span>
        </a>

        <h1>Create your workspace</h1>
        <p class="subtitle">Set up your subdomain and admin account to get started.</p>

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ url('/register') }}">
            @csrf

            <div class="form-group">
                <label>Subdomain</label>
                <div class="subdomain-wrap">
                    <input name="subdomain" value="{{ old('subdomain') }}" placeholder="your-company" required>
                    <span class="subdomain-suffix">.{{ env('SAAS_BASE_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost') }}</span>
                </div>
                <p class="hint">Lowercase letters, numbers, and hyphens only.</p>
            </div>

            <div class="form-group">
                <label>Company name</label>
                <input name="company_name" value="{{ old('company_name') }}" placeholder="Acme Inc." required>
            </div>

            <div class="form-group">
                <label>Admin email</label>
                <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="you@company.com" required>
            </div>

            <div class="form-group">
                <label>Phone number (optional)</label>
                <input type="tel" name="owner_phone" value="{{ old('owner_phone') }}" placeholder="+1 555 123 4567">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="admin_password" required minlength="8" placeholder="Min. 8 chars">
                </div>
                <div class="form-group">
                    <label>Confirm</label>
                    <input type="password" name="admin_password_confirmation" required placeholder="Repeat">
                </div>
            </div>

            <div class="form-group">
                <label>Plan</label>
                <select name="plan_id" required>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}" {{ $selectedPlanId == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}  ${{ number_format($p->monthly_price, 2) }}/mo
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Billing cycle</label>
                <div class="cycle-options">
                    <label class="cycle-option">
                        <input type="radio" name="billing_cycle" value="monthly" checked>
                        <div class="cycle-card">
                            <div class="cycle-name">Monthly</div>
                            <div class="cycle-price">Pay per month</div>
                        </div>
                    </label>
                    <label class="cycle-option">
                        <input type="radio" name="billing_cycle" value="yearly">
                        <div class="cycle-card">
                            <div class="cycle-name">Yearly</div>
                            <div class="cycle-price">Save 17%</div>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn">Create workspace ?</button>
        </form>

        <a href="/" class="back">? Back to homepage</a>
        <div class="footer">© {{ date('Y') }} {{ $settings['app_name'] ?? 'Poraone' }}. All rights reserved.</div>
    </div>
</div>

</body>
</html>