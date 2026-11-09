<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Super Admin') - {{ config('app.name', 'Poraone') }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; min-height: 100%; font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f6f7fb; color: #172033; }
        a { text-decoration: none; color: inherit; }
        button, input, select { font: inherit; }

        .saas-app { min-height: 100vh; display: flex; }

        /* =========== SIDEBAR =========== */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #ffffff;
            color: #374151;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #e7e9ef;
            overflow-y: auto;
        }

        .brand { height: 76px; padding: 0 24px; display: flex; align-items: center; border-bottom: 1px solid #eef0f4; position: sticky; top: 0; background: #fff; z-index: 10; }
        .brand-icon { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; margin-right: 12px; color: #fff; box-shadow: 0 8px 20px rgba(99,102,241,.25); }
        .brand-text { font-size: 20px; font-weight: 800; letter-spacing: -.4px; color: #111827; }
        .brand-subtitle { font-size: 11px; color: #9ca3af; margin-top: 2px; }

        .sidebar-section { padding: 20px 14px 8px; }
        .sidebar-label { color: #9ca3af; font-size: 10px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase; padding: 0 12px 9px; }

        .nav-item { height: 42px; display: flex; align-items: center; gap: 12px; padding: 0 13px; margin: 2px 0; border-radius: 10px; color: #6b7280; font-size: 14px; font-weight: 600; transition: all .15s ease; }
        .nav-item:hover { background: #f3f4f6; color: #111827; }
        .nav-item.active { background: #eef2ff; color: #6366f1; }
        .nav-icon { width: 22px; text-align: center; font-size: 16px; }

        .sidebar-bottom { margin-top: auto; padding: 14px; border-top: 1px solid #eef0f4; }
        .admin-card { display: flex; align-items: center; padding: 11px; border-radius: 11px; background: #f9fafb; }
        .admin-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-weight: 800; margin-right: 10px; color: #fff; }
        .admin-name { font-size: 13px; font-weight: 700; color: #111827; }
        .admin-role { font-size: 11px; color: #9ca3af; margin-top: 2px; }
        .logout-button { width: 100%; border: 0; background: transparent; color: #6b7280; cursor: pointer; padding: 10px 8px 2px; font-size: 13px; text-align: left; }
        .logout-button:hover { color: #111827; }

        /* =========== MAIN =========== */
        .main { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }

        /* =========== TOPBAR =========== */
        .topbar { height: 76px; background: #fff; border-bottom: 1px solid #e7e9ef; display: flex; align-items: center; justify-content: space-between; padding: 0 34px; position: sticky; top: 0; z-index: 50; }
        .topbar-title { font-size: 18px; font-weight: 750; color: #111827; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }

        /* =========== DROPDOWN =========== */
        .topbar-dropdown { position: relative; display: inline-block; }

        .topbar-icon-btn {
            height: 38px;
            min-width: 38px;
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 10px;
            color: #6b7280;
            background: transparent;
            border: 1px solid transparent;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            position: relative;
        }
        .topbar-icon-btn:hover {
            background: #f3f4f6;
            color: #111827;
        }

        /* Profile button */
        .topbar-profile-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px 5px 5px;
            border-radius: 10px;
            background: #f9fafb;
            border: 1px solid #eef0f4;
            cursor: pointer;
            font-family: inherit;
            color: #374151;
            font-size: 12px;
            font-weight: 600;
        }
        .topbar-profile-btn:hover {
            background: #f3f4f6;
        }

        /* Dropdown menu */
        .topbar-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: #fff;
            border: 1px solid #e7e9ef;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(17, 24, 39, 0.08);
            z-index: 1000;
            min-width: 200px;
            overflow: hidden;
            padding: 6px 0;
        }
        .topbar-dropdown-menu.open { display: block; }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background .12s ease;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-family: inherit;
        }
        .dropdown-item:hover {
            background: #f9fafb;
            color: #16a34a;
        }

        .dropdown-divider {
            height: 1px;
            background: #eef0f4;
            margin: 4px 0;
        }

        /* Notification badge */
        .notif-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: #dc2626;
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            padding: 1px 5px;
            border-radius: 999px;
            min-width: 16px;
            text-align: center;
            display: none;
        }
        .notif-badge.show { display: inline-block; }

        .status-pill { display: inline-flex; align-items: center; gap: 7px; padding: 7px 11px; border-radius: 999px; background: #ecfdf3; color: #15803d; font-size: 12px; font-weight: 700; }
        .status-dot { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; }

        .content { padding: 34px; max-width: 1500px; margin: 0 auto; }

        /* =========== COMMON =========== */
        .page-heading { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
        .page-heading h1 { margin: 0; font-size: 28px; line-height: 1.2; letter-spacing: -.7px; color: #111827; }
        .page-heading p { margin: 8px 0 0; color: #6b7280; font-size: 14px; }

        .button { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 15px; border-radius: 9px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 700; cursor: pointer; }
        .button:hover { background: #f9fafb; }
        .button-primary { color: #fff; border-color: #6366f1; background: linear-gradient(135deg, #6366f1, #7c3aed); box-shadow: 0 5px 14px rgba(99,102,241,.20); }
        .button-primary:hover { background: linear-gradient(135deg, #5558e8, #6d28d9); }

        .alert { padding: 13px 15px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; }
        .alert-success { background: #ecfdf5; border: 1px solid #bbf7d0; color: #166534; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* =========== CARDS & TABLES =========== */
        .card {
            background: #fff;
            border: 1px solid #e7e9ef;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(17,24,39,.025);
            overflow: hidden;
        }

        .pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            text-align: left;
            border-bottom: 1px solid #eee;
            color: #6b7280;
            padding: 10px;
            font-size: 12px;
            text-transform: uppercase;
        }

        .table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 22px;
        }
        .stat-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 20px; }
        .stat-top { display: flex; align-items: center; gap: 11px; margin-bottom: 17px; }
        .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; font-weight: 800; }
        .stat-icon-purple { background: #eef2ff; color: #6366f1; }
        .stat-icon-green { background: #ecfdf5; color: #16a34a; }
        .stat-icon-orange { background: #fff7ed; color: #ea580c; }
        .stat-label { color: #6b7280; font-size: 13px; font-weight: 650; }
        .stat-number { color: #111827; font-size: 31px; line-height: 1; font-weight: 800; letter-spacing: -.8px; }

        /* ==========================================================
           DARK MODE
        ========================================================== */
        body.dark-mode {
            background: #0f172a !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .sidebar {
            background: #1e293b !important;
            border-right-color: #334155 !important;
        }

        body.dark-mode .brand {
            background: #1e293b !important;
            border-bottom-color: #334155 !important;
        }

        body.dark-mode .brand-text { color: #fff !important; }
        body.dark-mode .brand-subtitle { color: #94a3b8 !important; }

        body.dark-mode .nav-item { color: #94a3b8 !important; }
        body.dark-mode .nav-item:hover {
            background: #334155 !important;
            color: #fff !important;
        }
        body.dark-mode .nav-item.active {
            background: rgba(99, 102, 241, 0.2) !important;
            color: #a5b4fc !important;
        }

        body.dark-mode .admin-card {
            background: #334155 !important;
        }
        body.dark-mode .admin-name { color: #fff !important; }
        body.dark-mode .admin-role { color: #94a3b8 !important; }

        body.dark-mode .logout-button { color: #94a3b8 !important; }
        body.dark-mode .logout-button:hover { color: #fff !important; }

        body.dark-mode .topbar {
            background: #1e293b !important;
            border-bottom-color: #334155 !important;
        }

        body.dark-mode .topbar-title { color: #fff !important; }

        body.dark-mode .topbar-icon-btn {
            color: #94a3b8 !important;
        }
        body.dark-mode .topbar-icon-btn:hover {
            background: #334155 !important;
            color: #fff !important;
        }

        body.dark-mode .topbar-profile-btn {
            background: #334155 !important;
            border-color: #475569 !important;
            color: #fff !important;
        }

        body.dark-mode .topbar-dropdown-menu {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .dropdown-item {
            color: #e2e8f0 !important;
        }
        body.dark-mode .dropdown-item:hover {
            background: #334155 !important;
            color: #a5b4fc !important;
        }

        body.dark-mode .dropdown-divider {
            background: #334155 !important;
        }

        body.dark-mode .content {
            background: #0f172a !important;
        }

        body.dark-mode .card,
        body.dark-mode .stat-card {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode h1,
        body.dark-mode h2,
        body.dark-mode h3,
        body.dark-mode .stat-number,
        body.dark-mode .page-heading h1 {
            color: #fff !important;
        }

        body.dark-mode .page-heading p,
        body.dark-mode .stat-label {
            color: #94a3b8 !important;
        }

        body.dark-mode .table th {
            background: #1e293b !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .table td {
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .form-group input,
        body.dark-mode .form-group select,
        body.dark-mode .form-group textarea {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #fff !important;
        }

        body.dark-mode .form-group label {
            color: #cbd5e1 !important;
        }

        body.dark-mode .alert-success {
            background: rgba(16, 185, 129, 0.1) !important;
            border-color: rgba(16, 185, 129, 0.3) !important;
            color: #6ee7b7 !important;
        }

        body.dark-mode .alert-error {
            background: rgba(239, 68, 68, 0.1) !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
            color: #fca5a5 !important;
        }

        /* =========== RESPONSIVE =========== */
        @media (max-width: 900px) {
            .sidebar { width: 220px; }
            .main { margin-left: 220px; width: calc(100% - 220px); }
            .content { padding: 24px; }
        }
        @media (max-width: 700px) {
            .sidebar { position: relative; width: 100%; min-height: auto; }
            .saas-app { display: block; }
            .main { margin-left: 0; width: 100%; }
            .sidebar-bottom { display: none; }
            .topbar { position: relative; padding: 0 20px; }
            .content { padding: 20px; }
            .stats-grid { grid-template-columns: 1fr; }
        }
        @stack('styles')
    </style>
</head>

<body>
<div class="saas-app">

    <!-- ==================================================
         SIDEBAR
         ================================================== -->
    <aside class="sidebar">
        <div class="brand">
            {{-- DYNAMIC LOGO & TEXT --}}
            @if(file_exists(public_path('images/super/settings/logo.png')))
                <img src="{{ asset('images/super/settings/logo.png') }}" alt="Logo" style="max-height: 40px; margin-right: 12px; border-radius: 8px;">
            @else
                <div class="brand-icon">{{ substr($settings['app_name'] ?? 'P', 0, 1) }}</div>
            @endif
            <div>
                <div class="brand-text">{{ $settings['app_name'] ?? 'Poraone' }}</div>
                <div class="brand-subtitle">Super Admin</div>
            </div>
        </div>

        {{-- ========== MAIN ========== --}}
        <div class="sidebar-section">
            <div class="sidebar-label">Main</div>

            <a href="{{ url('/super') }}" class="nav-item {{ request()->is('super') ? 'active' : '' }}">
                <span class="nav-icon">▦</span><span>Dashboard</span>
            </a>

            @php $pendingCount = \App\Models\SaaS\Tenant::where('status','pending')->count(); @endphp
            <a href="{{ url('/super/tenants') }}" class="nav-item {{ request()->is('super/tenants*') ? 'active' : '' }}">
                <span class="nav-icon">▣</span><span>Tenants/Users</span>
                @if($pendingCount > 0)
                    <span style="margin-left:auto; background:#dc2626; color:#fff; font-size:10px; font-weight:800; padding:2px 7px; border-radius:999px;">{{ $pendingCount }}</span>
                @endif
            </a>

            <a href="{{ url('/super/plans') }}" class="nav-item {{ request()->is('super/plans*') ? 'active' : '' }}">
                <span class="nav-icon">◇</span><span>Plans</span>
            </a>
            <a href="{{ url('/super/subscriptions') }}" class="nav-item {{ (request()->is('super/subscriptions') || (request()->is('super/subscriptions/*') && !request()->is('super/subscriptions/reminders*'))) ? 'active' : '' }}">
                <span class="nav-icon">▤</span><span>Subscriptions</span>
            </a>
            <a href="{{ url('/super/subscriptions/reminders') }}" class="nav-item {{ request()->is('super/subscriptions/reminders*') ? 'active' : '' }}">
                <span class="nav-icon">⚙</span><span>Reminders</span>
            </a>
            <a href="{{ url('/super/admins') }}" class="nav-item {{ request()->is('super/admins*') ? 'active' : '' }}">
                <span class="nav-icon">♙</span><span>Super Admins</span>
            </a>
        </div>

        {{-- ========== PAYMENTS ========== --}}
        <div class="sidebar-section">
            <div class="sidebar-label">Payments</div>

            <a href="{{ url('/super/payments') }}" class="nav-item {{ request()->is('super/payments') ? 'active' : '' }}">
                <span class="nav-icon">◫</span><span>Overview</span>
            </a>
            <a href="{{ url('/super/payments/transactions') }}" class="nav-item {{ request()->is('super/payments/transactions*') ? 'active' : '' }}">
                <span class="nav-icon">▣</span><span>Transactions</span>
            </a>
            <a href="{{ url('/super/payments/pending-registrations') }}" class="nav-item {{ request()->is('super/payments/pending-registrations*') ? 'active' : '' }}">
                <span class="nav-icon">⟳</span><span>Pending Payments</span>
            </a>
            <a href="{{ url('/super/payments/invoices') }}" class="nav-item {{ request()->is('super/payments/invoices*') ? 'active' : '' }}">
                <span class="nav-icon">▤</span><span>Invoices</span>
            </a>
        </div>

        {{-- ========== REPORTS ========== --}}
        <div class="sidebar-section">
            <div class="sidebar-label">Reports</div>
            <a href="{{ url('/super/reports') }}" class="nav-item {{ request()->is('super/reports*') ? 'active' : '' }}">
                <span class="nav-icon">📊</span><span>Reports</span>
            </a>
        </div>

        {{-- ========== CMS ========== --}}
        <div class="sidebar-section">
            <div class="sidebar-label">CMS</div>

            <a href="{{ url('/super/cms') }}" class="nav-item {{ request()->is('super/cms') ? 'active' : '' }}">
                <span class="nav-icon">🖼</span><span>Landing Page</span>
            </a>
            <a href="{{ url('/super/cms/landing-pages') }}" class="nav-item {{ request()->is('super/cms/landing-pages*') ? 'active' : '' }}">
                <span class="nav-icon">▦</span><span>Landing layouts</span>
            </a>
            <a href="{{ url('/super/cms/hero') }}" class="nav-item {{ request()->is('super/cms/hero*') ? 'active' : '' }}">
                <span class="nav-icon">✦</span><span>Hero Section</span>
            </a>
            <a href="{{ url('/super/cms/features') }}" class="nav-item {{ request()->is('super/cms/features*') ? 'active' : '' }}">
                <span class="nav-icon">▩</span><span>Features</span>
            </a>
            <a href="{{ url('/super/cms/pricing') }}" class="nav-item {{ request()->is('super/cms/pricing*') ? 'active' : '' }}">
                <span class="nav-icon">$</span><span>Pricing Section</span>
            </a>
            <a href="{{ url('/super/cms/stats') }}" class="nav-item {{ request()->is('super/cms/stats*') ? 'active' : '' }}">
                <span class="nav-icon">▤</span><span>Stats / Trust Bar</span>
            </a>
            <a href="{{ url('/super/cms/how-it-works') }}" class="nav-item {{ request()->is('super/cms/how-it-works*') ? 'active' : '' }}">
                <span class="nav-icon">⇄</span><span>How It Works</span>
            </a>
            <a href="{{ url('/super/cms/testimonials') }}" class="nav-item {{ request()->is('super/cms/testimonials*') ? 'active' : '' }}">
                <span class="nav-icon">❝</span><span>Testimonials</span>
            </a>
            <a href="{{ url('/super/cms/faq') }}" class="nav-item {{ request()->is('super/cms/faq*') ? 'active' : '' }}">
                <span class="nav-icon">?</span><span>FAQ</span>
            </a>
            <a href="{{ url('/super/cms/cta') }}" class="nav-item {{ request()->is('super/cms/cta*') ? 'active' : '' }}">
                <span class="nav-icon">📣</span><span>CTA Section</span>
            </a>
            <a href="{{ url('/super/cms/footer') }}" class="nav-item {{ request()->is('super/cms/footer*') ? 'active' : '' }}">
                <span class="nav-icon">▭</span><span>Footer</span>
            </a>
            <a href="{{ url('/super/cms/seo') }}" class="nav-item {{ request()->is('super/cms/seo*') ? 'active' : '' }}">
                <span class="nav-icon">⌕</span><span>SEO Settings</span>
            </a>
            <a href="{{ url('/super/cms/privacy-policy') }}" class="nav-item {{ request()->is('super/cms/privacy-policy*') ? 'active' : '' }}">
                <span class="nav-icon">🛡</span><span>Privacy Policy</span>
            </a>
            <a href="{{ url('/super/cms/terms-conditions') }}" class="nav-item {{ request()->is('super/cms/terms-conditions*') ? 'active' : '' }}">
                <span class="nav-icon">▤</span><span>Terms &amp; Conditions</span>
            </a>
            <a href="{{ url('/super/cms/translations') }}" class="nav-item {{ request()->is('super/cms/translations*') ? 'active' : '' }}">
                <span class="nav-icon">🌐</span><span>Translations</span>
            </a>
        </div>

        {{-- ========== SETTINGS ========== --}}
        <div class="sidebar-section">
            <div class="sidebar-label">Settings</div>
            <a href="{{ url('/super/settings') }}" class="nav-item {{ request()->is('super/settings') || request()->is('super/settings/general*') ? 'active' : '' }}">
                <span class="nav-icon">⚙</span><span>General Settings</span>
            </a>
            <a href="{{ url('/super/settings/payment-gateways') }}" class="nav-item {{ request()->is('super/settings/payment-gateways*') ? 'active' : '' }}">
                <span class="nav-icon">💳</span><span>Payment Gateways</span>
            </a>
            <a href="{{ url('/super/settings/whatsapp') }}" class="nav-item {{ request()->is('super/settings/whatsapp*') ? 'active' : '' }}">
                <span class="nav-icon">💬</span><span>WhatsApp Settings</span>
            </a>
        </div>

        <div class="sidebar-bottom">
            <div class="admin-card">
                <div class="admin-avatar">{{ substr($settings['app_name'] ?? 'P', 0, 1) }}</div>
                <div>
                    <div class="admin-name">{{ session('saas_super_admin_name', 'Super Admin') }}</div>
                    <div class="admin-role">Platform Administrator</div>
                </div>
            </div>
            <form method="POST" action="{{ url('/super/logout') }}">
                @csrf
                <button type="submit" class="logout-button">⇥ &nbsp; Log out</button>
            </form>
        </div>
    </aside>

    <!-- ==================================================
         MAIN
         ================================================== -->
    <main class="main">
        <header class="topbar">
            <div class="topbar-title">@yield('topbar_title', 'Super Admin')</div>

            <div class="topbar-right">

                {{-- Dark Mode Toggle --}}
                <button type="button" class="topbar-icon-btn" id="themeToggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                    <span id="themeIcon">🌙</span>
                </button>

                {{-- Notifications --}}
                <div class="topbar-dropdown">
                    <button type="button" class="topbar-icon-btn" onclick="toggleDropdown('notifMenu')" title="Notifications">
                        🔔
                        <span class="notif-badge" id="notifBadge">0</span>
                    </button>
                    <div class="topbar-dropdown-menu" id="notifMenu" style="width: 300px;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid #eef0f4; font-weight: 700; font-size: 13px;">
                            Notifications
                        </div>
                        <div style="padding: 30px 16px; text-align: center; color: #9ca3af; font-size: 13px;">
                            <div style="font-size: 30px; margin-bottom: 8px;">🔔</div>
                            No new notifications
                            <div style="font-size: 11px; margin-top: 4px;">You're all caught up.</div>
                        </div>
                    </div>
                </div>

                {{-- Profile Dropdown --}}
                <div class="topbar-dropdown">
                    <button type="button" class="topbar-profile-btn" onclick="toggleDropdown('profileMenu')">
                        <div class="admin-avatar" style="width: 30px; height: 30px; font-size: 13px; margin-right: 0;">{{ substr($settings['app_name'] ?? 'P', 0, 1) }}</div>
                        <span style="max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ session('saas_super_admin_name', 'Super Admin') }}</span>
                        <span style="font-size: 9px; color: #9ca3af;">▼</span>
                    </button>
                    <div class="topbar-dropdown-menu" id="profileMenu" style="width: 240px;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid #eef0f4;">
                            <div style="font-weight: 700; font-size: 13px; color: #111827;">
                                {{ session('saas_super_admin_name', 'Super Admin') }}
                            </div>
                            <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">
                                {{ session('saas_super_admin_email', 'admin@poraone.com') }}
                            </div>
                        </div>
                        <a href="{{ url('/super') }}" class="dropdown-item">🏠 Dashboard</a>
                        <a href="{{ url('/super/admins') }}" class="dropdown-item">👤 My Profile</a>
                        <a href="{{ url('/super/settings') }}" class="dropdown-item">⚙ Settings</a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ url('/super/logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item" style="color: #dc2626;">
                                ⇥ Log out
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </header>

        <section class="content">
            @if(session('success'))
                <div class="alert alert-success">✓ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    <strong>Please check the following:</strong>
                    <ul style="margin:7px 0 0 18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </section>
    </main>
</div>

<script>
    /* ==========================================================
       DROPDOWN TOGGLE
    ========================================================== */
    function toggleDropdown(id) {
        const target = document.getElementById(id);
        const isOpen = target.classList.contains('open');

        document.querySelectorAll('.topbar-dropdown-menu').forEach(m => m.classList.remove('open'));

        if (!isOpen) target.classList.add('open');
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.topbar-dropdown')) {
            document.querySelectorAll('.topbar-dropdown-menu').forEach(m => m.classList.remove('open'));
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.topbar-dropdown-menu').forEach(m => m.classList.remove('open'));
        }
    });

    /* ==========================================================
       DARK MODE TOGGLE
    ========================================================== */
    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('saas_theme', isDark ? 'dark' : 'light');
        document.getElementById('themeIcon').textContent = isDark ? '☀' : '🌙';
    }

    (function () {
        const saved = localStorage.getItem('saas_theme');
        if (saved === 'dark') {
            document.body.classList.add('dark-mode');
            const icon = document.getElementById('themeIcon');
            if (icon) icon.textContent = '☀';
        }
    })();
</script>

@stack('scripts')
</body>
</html>