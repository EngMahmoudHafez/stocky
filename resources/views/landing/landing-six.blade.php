<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['app_name'] ?? $cms['seo']['meta_title'] ?? 'Poraone — Inventory & Stock Management' }}</title>
    <meta name="description" content="{{ $cms['seo']['meta_description'] ?? '' }}">

    @if(!empty($cms['seo']['og_image']))
        <meta property="og:image" content="{{ $cms['seo']['og_image'] }}">
    @endif

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Inter, sans-serif;
            background: #fff;
            color: #172033;
            line-height: 1.55;
        }
        a { color: inherit; text-decoration: none; }

        /* Container */
        .wrap { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

        /* Top nav */
        .nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(255,255,255,.9); backdrop-filter: blur(10px);
            border-bottom: 1px solid #eef0f4;
            padding: 16px 0;
        }
        .nav-inner { display: flex; justify-content: space-between; align-items: center; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 20px; }
        .brand-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800;
        }
        .nav-cta {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 18px; border-radius: 9px;
            background: #16a34a; color: #fff;
            font-size: 13px; font-weight: 700;
        }
        .nav-cta:hover { background: #15803d; }

        /* Hero */
        .hero { padding: 100px 0 80px; text-align: center; }
        .hero .badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 999px;
            background: #eef2ff; color: #4338ca;
            font-size: 12px; font-weight: 700;
            margin-bottom: 24px;
        }
        .hero h1 {
            font-size: clamp(34px, 5vw, 60px);
            line-height: 1.1; letter-spacing: -1.5px;
            font-weight: 800; color: #0f172a;
            max-width: 900px; margin: 0 auto 20px;
        }
        .hero p.lead {
            font-size: 18px; color: #6b7280;
            max-width: 640px; margin: 0 auto 32px;
        }
        .hero .btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 26px; border-radius: 10px;
            font-size: 14px; font-weight: 700;
            border: 1px solid transparent;
        }
        .btn-primary { background: #16a34a; color: #fff; }
        .btn-primary:hover { background: #15803d; }
        .btn-outline { background: #fff; color: #374151; border-color: #e5e7eb; }
        .btn-outline:hover { background: #f9fafb; }
        .hero ul {
            list-style: none; display: flex; gap: 30px; justify-content: center;
            margin-top: 40px; flex-wrap: wrap;
        }
        .hero ul li { display: flex; align-items: center; gap: 8px; color: #4b5563; font-size: 14px; }
        .hero ul li::before { content: "✓"; color: #16a34a; font-weight: 800; }

        /* Sections */
        section.block { padding: 80px 0; }
        section.block.alt { background: #f9fafb; }
        .section-title { text-align: center; margin-bottom: 50px; }
        .section-title h2 { font-size: clamp(26px, 3.5vw, 40px); font-weight: 800; letter-spacing: -1px; color: #0f172a; margin-bottom: 12px; }
        .section-title p { color: #6b7280; font-size: 16px; max-width: 620px; margin: 0 auto; }

        /* Feature grid */
        .grid { display: grid; gap: 22px; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 900px) { .grid-3, .grid-4 { grid-template-columns: 1fr; } }

        .feature {
            background: #fff; border: 1px solid #eef0f4; border-radius: 14px;
            padding: 26px 22px;
        }
        .feature .ico {
            width: 48px; height: 48px; border-radius: 12px;
            background: #eef2ff; color: #4338ca;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; margin-bottom: 16px;
        }
        .feature h3 { font-size: 17px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .feature p { font-size: 14px; color: #6b7280; }

        /* Stats bar */
        .stats-bar { padding: 50px 0; background: #0f172a; color: #fff; }
        .stats-bar .grid { text-align: center; }
        .stats-bar .stat .val { font-size: 40px; font-weight: 800; letter-spacing: -1px; }
        .stats-bar .stat .lab { color: #94a3b8; font-size: 13px; margin-top: 6px; text-transform: uppercase; letter-spacing: 1px; }

        /* Steps */
        .steps { display: grid; gap: 30px; grid-template-columns: repeat(3, 1fr); }
        @media (max-width: 900px) { .steps { grid-template-columns: 1fr; } }
        .step .num {
            width: 40px; height: 40px; border-radius: 50%;
            background: #16a34a; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; margin-bottom: 16px;
        }
        .step h3 { font-size: 17px; font-weight: 700; margin-bottom: 8px; }
        .step p { color: #6b7280; font-size: 14px; }

        /* Testimonials */
        .testimonial {
            background: #fff; border: 1px solid #eef0f4; border-radius: 14px; padding: 26px;
        }
        .testimonial .stars { color: #f59e0b; font-size: 14px; margin-bottom: 10px; }
        .testimonial .quote { font-size: 14px; color: #374151; margin-bottom: 18px; }
        .testimonial .who { display: flex; align-items: center; gap: 12px; }
        .testimonial .who .av {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800;
        }
        .testimonial .who .name { font-weight: 700; font-size: 13px; }
        .testimonial .who .role { font-size: 11px; color: #9ca3af; }

        /* FAQ */
        .faq-item { border-bottom: 1px solid #eef0f4; padding: 20px 0; }
        .faq-item summary { cursor: pointer; font-weight: 700; font-size: 15px; color: #0f172a; list-style: none; display: flex; justify-content: space-between; }
        .faq-item summary::-webkit-details-marker { display: none; }
        .faq-item summary::after { content: "+"; font-size: 20px; color: #6b7280; }
        .faq-item[open] summary::after { content: "−"; }
        .faq-item .answer { padding-top: 12px; color: #6b7280; font-size: 14px; }

        /* CTA */
        .cta-block {
            padding: 80px 0; text-align: center;
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            color: #fff;
        }
        .cta-block h2 { font-size: clamp(26px, 3.5vw, 42px); font-weight: 800; letter-spacing: -1px; margin-bottom: 14px; }
        .cta-block p { color: rgba(255,255,255,.85); font-size: 16px; margin-bottom: 30px; }
        .cta-block .btn-primary { background: #fff; color: #4338ca; }
        .cta-block .btn-primary:hover { background: #f3f4f6; }

        /* Footer */
        footer { padding: 60px 0 30px; background: #0f172a; color: #94a3b8; font-size: 13px; }
        footer .cols { display: grid; gap: 40px; grid-template-columns: 2fr 1fr 1fr 1fr; margin-bottom: 40px; }
        @media (max-width: 900px) { footer .cols { grid-template-columns: 1fr 1fr; } }
        footer h4 { color: #fff; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; }
        footer a { color: #94a3b8; display: block; margin-bottom: 10px; }
        footer a:hover { color: #fff; }
        footer .bottom { border-top: 1px solid #1e293b; padding-top: 24px; text-align: center; font-size: 12px; }
        footer .social { display: flex; gap: 14px; }
        footer .social a { margin: 0; }
    </style>
{{-- THEME-CSS-START --}}
@include("landing._partials.theme-css")
{{-- THEME-CSS-END --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="layout-agency">

{{-- ============ NAV ============ --}}
<nav class="nav">
    <div class="wrap nav-inner">
        <a href="/" class="brand">
            {{-- FIXED: Direct path to public logo --}}
            @if(file_exists(public_path('images/super/settings/logo.png')))
                <img src="{{ asset('images/super/settings/logo.png') }}" alt="{{ $settings['app_name'] ?? 'Poraone' }}" style="max-height: 36px; border-radius: 8px;">
            @else
                <span class="brand-icon">{{ strtoupper(substr($settings['app_name'] ?? 'P', 0, 1)) }}</span>
            @endif
            {{ $settings['app_name'] ?? 'Poraone' }}
        </a>
        <a href="/login" class="nav-cta">Sign in →</a>
    </div>
</nav>

{{-- ============ HERO ============ --}}
@if(!isset($cms['hero']['is_active']) || $cms['hero']['is_active'])
<section class="hero">
    <div class="wrap">
        @if(!empty($cms['hero']['badge']))
            <span class="badge">✓ {{ $cms['hero']['badge'] }}</span>
        @endif
        <h1>{{ $cms['hero']['title'] ?? 'Manage your business smarter.' }}</h1>
        <p class="lead">{{ $cms['hero']['subtitle'] ?? 'Streamline inventory, track sales, and grow your business — all from one powerful dashboard.' }}</p>

        <div class="btns">
            @if(!empty($cms['hero']['cta_primary']))
                <a href="{{ $cms['hero']['cta_primary_url'] ?? '/register' }}" class="btn btn-primary">
                    {{ $cms['hero']['cta_primary'] }} →
                </a>
            @endif
            @if(!empty($cms['hero']['cta_secondary']))
                <a href="{{ $cms['hero']['cta_secondary_url'] ?? '/demo' }}" class="btn btn-outline">
                    {{ $cms['hero']['cta_secondary'] }}
                </a>
            @endif
        </div>

        <ul>
            @foreach(['bullet_1','bullet_2','bullet_3'] as $b)
                @if(!empty($cms['hero'][$b]))
                    <li>{{ $cms['hero'][$b] }}</li>
                @endif
            @endforeach
        </ul>
    </div>
</section>
@endif

{{-- ============ FEATURES ============ --}}
@php $features = App\Models\CmsItem::section('features')->where('is_active',1)->orderBy('sort_order')->get(); @endphp
@if($features->count())
<section class="block">
    <div class="wrap">
        <div class="section-title">
            <h2>{{ $cms['features']['title'] ?? 'Everything you need' }}</h2>
            <p>{{ $cms['features']['subtitle'] ?? '' }}</p>
        </div>
        <div class="grid grid-3">
            @foreach($features as $f)
                <div class="feature">
                    <div class="ico"><i class="{{ $f->icon ?? 'bi bi-star' }}"></i></div>
                    <h3>{{ $f->title }}</h3>
                    <p>{{ $f->body ?? $f->subtitle }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ STATS ============ --}}
@php $stats = App\Models\CmsItem::section('stats')->where('is_active',1)->orderBy('sort_order')->get(); @endphp
@if($stats->count())
<section class="stats-bar">
    <div class="wrap">
        <div class="grid grid-4">
            @foreach($stats as $s)
                <div class="stat">
                    <div class="val">{{ $s->value }}</div>
                    <div class="lab">{{ $s->title }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ HOW IT WORKS ============ --}}
@php $steps = App\Models\CmsItem::section('steps')->where('is_active',1)->orderBy('sort_order')->get(); @endphp
@if($steps->count())
<section class="block alt">
    <div class="wrap">
        <div class="section-title">
            <h2>{{ $cms['how_it_works']['title'] ?? 'How it works' }}</h2>
            <p>{{ $cms['how_it_works']['subtitle'] ?? '' }}</p>
        </div>
        <div class="steps">
            @foreach($steps as $i => $st)
                <div class="step">
                    <div class="num">{{ $i+1 }}</div>
                    <h3>{{ $st->title }}</h3>
                    <p>{{ $st->body ?? $st->subtitle }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ PRICING ============ --}}
@php
    $plans = \App\Models\SaaS\Plan::where('is_active', 1)->orderBy('monthly_price')->get();
@endphp

@if($plans->count())
<section class="pricing-section block" id="pricing">
    <div class="wrap">
        <header class="pricing-head">
            <p class="eyebrow">Pricing</p>
            <h2>{{ $cms['pricing']['title'] ?? 'Simple, Transparent Pricing' }}</h2>
            <p>{{ $cms['pricing']['subtitle'] ?? 'Choose the plan that fits your business. Upgrade or downgrade anytime.' }}</p>
        </header>

        <div class="pricing-grid">
            @foreach($plans as $i => $plan)
                @php
                    $isFeatured = ($plans->count() === 3 && $i === 1);
                    $features   = is_array($plan->features)
                        ? $plan->features
                        : (json_decode($plan->features ?? '[]', true) ?: []);
                    $limits     = is_array($plan->limits)
                        ? $plan->limits
                        : (json_decode($plan->limits ?? '[]', true) ?: []);
                @endphp

                <article class="pricing-card {{ $isFeatured ? 'featured' : '' }}">
                    @if($isFeatured)
                        <span class="badge">Most Popular</span>
                    @endif

                    <h3 class="pricing-card__name">{{ $plan->name }}</h3>
                    <p class="pricing-card__hint">{{ $plan->description ?? 'For growing teams' }}</p>

                    <div class="pricing-card__price">
                        @if((float) $plan->monthly_price === 0.0)
                            <span class="pricing-card__amount free">Free</span>
                        @else
                            <span class="pricing-card__amount">${{ number_format($plan->monthly_price, 2) }}</span>
                            <span class="pricing-card__per">/ mo</span>
                        @endif
                    </div>

                    @if((float) $plan->yearly_price > 0)
                        <p class="pricing-card__year">
                            or ${{ number_format($plan->yearly_price, 2) }} / yr
                            <span class="pricing-card__save">Save 17%</span>
                        </p>
                    @endif

                    <ul class="pricing-card__features">
                        {{-- Usage limits first --}}
                        @foreach($limits as $limitKey => $limitValue)
                            @if($limitValue !== null && $limitValue !== '' && $limitValue !== 0)
                                <li>
                                    <i class="bi bi-check-lg"></i>
                                    <span>{{ ucfirst($limitKey) }}: {{ $limitValue === -1 ? 'Unlimited' : $limitValue }}</span>
                                </li>
                            @endif
                        @endforeach

                        {{-- Feature toggles --}}
                        @foreach($features as $feature)
                            @if($feature !== '*')
                                <li>
                                    <i class="bi bi-check-lg"></i>
                                    <span>{{ ucwords(str_replace(['_', '-'], ' ', $feature)) }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                    <a href="/register?plan={{ $plan->id }}" class="pricing-card__cta">
                        {{ (float) $plan->monthly_price === 0.0 ? 'Get Started' : 'Choose Plan' }}
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
{{-- ============ TESTIMONIALS ============ --}}
@php $testis = App\Models\CmsItem::section('testimonials')->where('is_active',1)->orderBy('sort_order')->get(); @endphp
@if($testis->count())
<section class="block">
    <div class="wrap">
        <div class="section-title">
            <h2>{{ $cms['testimonials']['title'] ?? 'Loved by teams' }}</h2>
            <p>{{ $cms['testimonials']['subtitle'] ?? '' }}</p>
        </div>
        <div class="grid grid-3">
            @foreach($testis as $t)
                <div class="testimonial">
                    <div class="stars">{{ str_repeat('★', (int)($t->rating ?? 5)) }}</div>
                    <p class="quote">"{{ $t->body ?? $t->subtitle }}"</p>
                    <div class="who">
                        <div class="av">{{ strtoupper(substr($t->title ?? 'A', 0, 1)) }}</div>
                        <div>
                            <div class="name">{{ $t->title }}</div>
                            <div class="role">{{ $t->subtitle }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ FAQ ============ --}}
@php $faqs = App\Models\CmsItem::section('faq')->where('is_active',1)->orderBy('sort_order')->get(); @endphp
@if($faqs->count())
<section class="block alt">
    <div class="wrap" style="max-width: 800px;">
        <div class="section-title">
            <h2>{{ $cms['faq']['title'] ?? 'Frequently asked questions' }}</h2>
            <p>{{ $cms['faq']['subtitle'] ?? '' }}</p>
        </div>
        @foreach($faqs as $q)
            <details class="faq-item">
                <summary>{{ $q->title }}</summary>
                <div class="answer">{{ $q->body }}</div>
            </details>
        @endforeach
    </div>
</section>
@endif

{{-- ============ CTA ============ --}}
@if(!empty($cms['cta']['title']))
<section class="cta-block">
    <div class="wrap">
        <h2>{{ $cms['cta']['title'] }}</h2>
        <p>{{ $cms['cta']['subtitle'] ?? '' }}</p>
        @if(!empty($cms['cta']['button_text']))
            <a href="{{ $cms['cta']['button_url'] ?? '/register' }}" class="btn btn-primary">
                {{ $cms['cta']['button_text'] }} →
            </a>
        @endif
    </div>
</section>
@endif

{{-- ============ FOOTER ============ --}}
<footer>
    <div class="wrap">
        <div class="cols">
            <div>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px; color:#fff; font-weight:800; font-size:18px;">
                    {{-- FIXED: Direct path to public logo --}}
                    @if(file_exists(public_path('images/super/settings/logo.png')))
                        <img src="{{ asset('images/super/settings/logo.png') }}" alt="{{ $settings['app_name'] ?? 'Poraone' }}" style="max-height: 36px; border-radius: 8px;">
                    @else
                        <span class="brand-icon">{{ strtoupper(substr($settings['app_name'] ?? 'P', 0, 1)) }}</span>
                    @endif
                    {{ $settings['app_name'] ?? 'Poraone' }}
                </div>
                <p style="line-height:1.7; max-width:320px;">
                    {{ $cms['footer']['copyright'] ?? 'Modern inventory and stock management for teams of every size.' }}
                </p>
            </div>
            <div>
                <h4>Product</h4>
                <a href="#features">Features</a>
                <a href="#pricing">Pricing</a>
                <a href="/login">Sign in</a>
            </div>
            <div>
                <h4>Company</h4>
                <a href="/super/cms/privacy-policy">Privacy</a>
                <a href="/super/cms/terms-conditions">Terms</a>
            </div>
            <div>
                <h4>Follow</h4>
                <div class="social">
                    @forelse(App\Models\CmsItem::section('footer_social')->where('is_active',1)->get() as $s)
                        <a href="{{ $s->url }}" target="_blank" rel="noopener">●</a>
                    @empty
                        <span style="color:#64748b;">—</span>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="bottom">
            {{ $cms['footer']['copyright'] ?? '© '.date('Y').' Poraone. All rights reserved.' }}
        </div>
    </div>
</footer>

</body>
</html><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cms['seo']['meta_title'] ?? 'Stocky — Inventory & Stock Management' }}</title>
    <meta name="description" content="{{ $cms['seo']['meta_description'] ?? '' }}">

    @if(!empty($cms['seo']['og_image']))
        <meta property="og:image" content="{{ $cms['seo']['og_image'] }}">
    @endif

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Inter, sans-serif;
            background: #fff;
            color: #172033;
            line-height: 1.55;
        }
        a { color: inherit; text-decoration: none; }

        /* Container */
        .wrap { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

        /* Top nav */
        .nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(255,255,255,.9); backdrop-filter: blur(10px);
            border-bottom: 1px solid #eef0f4;
            padding: 16px 0;
        }
        .nav-inner { display: flex; justify-content: space-between; align-items: center; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 20px; }
        .brand-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800;
        }
        .nav-cta {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 18px; border-radius: 9px;
            background: #16a34a; color: #fff;
            font-size: 13px; font-weight: 700;
        }
        .nav-cta:hover { background: #15803d; }

        /* Hero */
        .hero { padding: 100px 0 80px; text-align: center; }
        .hero .badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 999px;
            background: #eef2ff; color: #4338ca;
            font-size: 12px; font-weight: 700;
            margin-bottom: 24px;
        }
        .hero h1 {
            font-size: clamp(34px, 5vw, 60px);
            line-height: 1.1; letter-spacing: -1.5px;
            font-weight: 800; color: #0f172a;
            max-width: 900px; margin: 0 auto 20px;
        }
        .hero p.lead {
            font-size: 18px; color: #6b7280;
            max-width: 640px; margin: 0 auto 32px;
        }
        .hero .btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 26px; border-radius: 10px;
            font-size: 14px; font-weight: 700;
            border: 1px solid transparent;
        }
        .btn-primary { background: #16a34a; color: #fff; }
        .btn-primary:hover { background: #15803d; }
        .btn-outline { background: #fff; color: #374151; border-color: #e5e7eb; }
        .btn-outline:hover { background: #f9fafb; }
        .hero ul {
            list-style: none; display: flex; gap: 30px; justify-content: center;
            margin-top: 40px; flex-wrap: wrap;
        }
        .hero ul li { display: flex; align-items: center; gap: 8px; color: #4b5563; font-size: 14px; }
        .hero ul li::before { content: "✓"; color: #16a34a; font-weight: 800; }

        /* Sections */
        section.block { padding: 80px 0; }
        section.block.alt { background: #f9fafb; }
        .section-title { text-align: center; margin-bottom: 50px; }
        .section-title h2 { font-size: clamp(26px, 3.5vw, 40px); font-weight: 800; letter-spacing: -1px; color: #0f172a; margin-bottom: 12px; }
        .section-title p { color: #6b7280; font-size: 16px; max-width: 620px; margin: 0 auto; }

        /* Feature grid */
        .grid { display: grid; gap: 22px; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 900px) { .grid-3, .grid-4 { grid-template-columns: 1fr; } }

        .feature {
            background: #fff; border: 1px solid #eef0f4; border-radius: 14px;
            padding: 26px 22px;
        }
        .feature .ico {
            width: 48px; height: 48px; border-radius: 12px;
            background: #eef2ff; color: #4338ca;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; margin-bottom: 16px;
        }
        .feature h3 { font-size: 17px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .feature p { font-size: 14px; color: #6b7280; }

        /* Stats bar */
        .stats-bar { padding: 50px 0; background: #0f172a; color: #fff; }
        .stats-bar .grid { text-align: center; }
        .stats-bar .stat .val { font-size: 40px; font-weight: 800; letter-spacing: -1px; }
        .stats-bar .stat .lab { color: #94a3b8; font-size: 13px; margin-top: 6px; text-transform: uppercase; letter-spacing: 1px; }

        /* Steps */
        .steps { display: grid; gap: 30px; grid-template-columns: repeat(3, 1fr); }
        @media (max-width: 900px) { .steps { grid-template-columns: 1fr; } }
        .step .num {
            width: 40px; height: 40px; border-radius: 50%;
            background: #16a34a; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; margin-bottom: 16px;
        }
        .step h3 { font-size: 17px; font-weight: 700; margin-bottom: 8px; }
        .step p { color: #6b7280; font-size: 14px; }

        /* Testimonials */
        .testimonial {
            background: #fff; border: 1px solid #eef0f4; border-radius: 14px; padding: 26px;
        }
        .testimonial .stars { color: #f59e0b; font-size: 14px; margin-bottom: 10px; }
        .testimonial .quote { font-size: 14px; color: #374151; margin-bottom: 18px; }
        .testimonial .who { display: flex; align-items: center; gap: 12px; }
        .testimonial .who .av {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800;
        }
        .testimonial .who .name { font-weight: 700; font-size: 13px; }
        .testimonial .who .role { font-size: 11px; color: #9ca3af; }

        /* FAQ */
        .faq-item { border-bottom: 1px solid #eef0f4; padding: 20px 0; }
        .faq-item summary { cursor: pointer; font-weight: 700; font-size: 15px; color: #0f172a; list-style: none; display: flex; justify-content: space-between; }
        .faq-item summary::-webkit-details-marker { display: none; }
        .faq-item summary::after { content: "+"; font-size: 20px; color: #6b7280; }
        .faq-item[open] summary::after { content: "−"; }
        .faq-item .answer { padding-top: 12px; color: #6b7280; font-size: 14px; }

        /* CTA */
        .cta-block {
            padding: 80px 0; text-align: center;
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            color: #fff;
        }
        .cta-block h2 { font-size: clamp(26px, 3.5vw, 42px); font-weight: 800; letter-spacing: -1px; margin-bottom: 14px; }
        .cta-block p { color: rgba(255,255,255,.85); font-size: 16px; margin-bottom: 30px; }
        .cta-block .btn-primary { background: #fff; color: #4338ca; }
        .cta-block .btn-primary:hover { background: #f3f4f6; }

        /* Footer */
        footer { padding: 60px 0 30px; background: #0f172a; color: #94a3b8; font-size: 13px; }
        footer .cols { display: grid; gap: 40px; grid-template-columns: 2fr 1fr 1fr 1fr; margin-bottom: 40px; }
        @media (max-width: 900px) { footer .cols { grid-template-columns: 1fr 1fr; } }
        footer h4 { color: #fff; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; }
        footer a { color: #94a3b8; display: block; margin-bottom: 10px; }
        footer a:hover { color: #fff; }
        footer .bottom { border-top: 1px solid #1e293b; padding-top: 24px; text-align: center; font-size: 12px; }
        footer .social { display: flex; gap: 14px; }
        footer .social a { margin: 0; }
    </style>
{{-- THEME-CSS-START --}}
@include("landing._partials.theme-css")
{{-- THEME-CSS-END --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="layout-agency">

{{-- ============ NAV ============ --}}
<nav class="nav">
    <div class="wrap nav-inner">
        <a href="/" class="brand">
            <span class="brand-icon">S</span>
            {{ $cms['seo']['meta_title'] ?? 'Stocky' }}
        </a>
        <a href="/login" class="nav-cta">Sign in →</a>
    </div>
</nav>

{{-- ============ HERO ============ --}}
@if(!isset($cms['hero']['is_active']) || $cms['hero']['is_active'])
<section class="hero">
    <div class="wrap">
        @if(!empty($cms['hero']['badge']))
            <span class="badge">✓ {{ $cms['hero']['badge'] }}</span>
        @endif
        <h1>{{ $cms['hero']['title'] ?? 'Manage your business smarter.' }}</h1>
        <p class="lead">{{ $cms['hero']['subtitle'] ?? 'Streamline inventory, track sales, and grow your business — all from one powerful dashboard.' }}</p>

        <div class="btns">
            @if(!empty($cms['hero']['cta_primary']))
                <a href="{{ $cms['hero']['cta_primary_url'] ?? '/register' }}" class="btn btn-primary">
                    {{ $cms['hero']['cta_primary'] }} →
                </a>
            @endif
            @if(!empty($cms['hero']['cta_secondary']))
                <a href="{{ $cms['hero']['cta_secondary_url'] ?? '/demo' }}" class="btn btn-outline">
                    {{ $cms['hero']['cta_secondary'] }}
                </a>
            @endif
        </div>

        <ul>
            @foreach(['bullet_1','bullet_2','bullet_3'] as $b)
                @if(!empty($cms['hero'][$b]))
                    <li>{{ $cms['hero'][$b] }}</li>
                @endif
            @endforeach
        </ul>
    </div>
</section>
@endif

{{-- ============ FEATURES ============ --}}
@php $features = App\Models\CmsItem::section('features')->where('is_active',1)->orderBy('sort_order')->get(); @endphp
@if($features->count())
<section class="block">
    <div class="wrap">
        <div class="section-title">
            <h2>{{ $cms['features']['title'] ?? 'Everything you need' }}</h2>
            <p>{{ $cms['features']['subtitle'] ?? '' }}</p>
        </div>
        <div class="grid grid-3">
            @foreach($features as $f)
                <div class="feature">
                    <div class="ico"><i class="{{ $f->icon ?? 'bi bi-star' }}"></i></div>
                    <h3>{{ $f->title }}</h3>
                    <p>{{ $f->body ?? $f->subtitle }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ STATS ============ --}}
@php $stats = App\Models\CmsItem::section('stats')->where('is_active',1)->orderBy('sort_order')->get(); @endphp
@if($stats->count())
<section class="stats-bar">
    <div class="wrap">
        <div class="grid grid-4">
            @foreach($stats as $s)
                <div class="stat">
                    <div class="val">{{ $s->value }}</div>
                    <div class="lab">{{ $s->title }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ HOW IT WORKS ============ --}}
@php $steps = App\Models\CmsItem::section('steps')->where('is_active',1)->orderBy('sort_order')->get(); @endphp
@if($steps->count())
<section class="block alt">
    <div class="wrap">
        <div class="section-title">
            <h2>{{ $cms['how_it_works']['title'] ?? 'How it works' }}</h2>
            <p>{{ $cms['how_it_works']['subtitle'] ?? '' }}</p>
        </div>
        <div class="steps">
            @foreach($steps as $i => $st)
                <div class="step">
                    <div class="num">{{ $i+1 }}</div>
                    <h3>{{ $st->title }}</h3>
                    <p>{{ $st->body ?? $st->subtitle }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ PRICING ============ --}}
@php
    $plans = \App\Models\SaaS\Plan::where('is_active', 1)->orderBy('monthly_price')->get();
@endphp

@if($plans->count())
<section class="pricing-section block" id="pricing">
    <div class="wrap">
        <header class="pricing-head">
            <p class="eyebrow">Pricing</p>
            <h2>{{ $cms['pricing']['title'] ?? 'Simple, Transparent Pricing' }}</h2>
            <p>{{ $cms['pricing']['subtitle'] ?? 'Choose the plan that fits your business. Upgrade or downgrade anytime.' }}</p>
        </header>

        <div class="pricing-grid">
            @foreach($plans as $i => $plan)
                @php
                    $isFeatured = ($plans->count() === 3 && $i === 1);
                    $features   = is_array($plan->features)
                        ? $plan->features
                        : (json_decode($plan->features ?? '[]', true) ?: []);
                    $limits     = is_array($plan->limits)
                        ? $plan->limits
                        : (json_decode($plan->limits ?? '[]', true) ?: []);
                @endphp

                <article class="pricing-card {{ $isFeatured ? 'featured' : '' }}">
                    @if($isFeatured)
                        <span class="badge">Most Popular</span>
                    @endif

                    <h3 class="pricing-card__name">{{ $plan->name }}</h3>
                    <p class="pricing-card__hint">{{ $plan->description ?? 'For growing teams' }}</p>

                    <div class="pricing-card__price">
                        @if((float) $plan->monthly_price === 0.0)
                            <span class="pricing-card__amount free">Free</span>
                        @else
                            <span class="pricing-card__amount">${{ number_format($plan->monthly_price, 2) }}</span>
                            <span class="pricing-card__per">/ mo</span>
                        @endif
                    </div>

                    @if((float) $plan->yearly_price > 0)
                        <p class="pricing-card__year">
                            or ${{ number_format($plan->yearly_price, 2) }} / yr
                            <span class="pricing-card__save">Save 17%</span>
                        </p>
                    @endif

                    <ul class="pricing-card__features">
                        {{-- Usage limits first --}}
                        @foreach($limits as $limitKey => $limitValue)
                            @if($limitValue !== null && $limitValue !== '' && $limitValue !== 0)
                                <li>
                                    <i class="bi bi-check-lg"></i>
                                    <span>{{ ucfirst($limitKey) }}: {{ $limitValue === -1 ? 'Unlimited' : $limitValue }}</span>
                                </li>
                            @endif
                        @endforeach

                        {{-- Feature toggles --}}
                        @foreach($features as $feature)
                            @if($feature !== '*')
                                <li>
                                    <i class="bi bi-check-lg"></i>
                                    <span>{{ ucwords(str_replace(['_', '-'], ' ', $feature)) }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                    <a href="/register?plan={{ $plan->id }}" class="pricing-card__cta">
                        {{ (float) $plan->monthly_price === 0.0 ? 'Get Started' : 'Choose Plan' }}
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
{{-- ============ TESTIMONIALS ============ --}}
@php $testis = App\Models\CmsItem::section('testimonials')->where('is_active',1)->orderBy('sort_order')->get(); @endphp
@if($testis->count())
<section class="block">
    <div class="wrap">
        <div class="section-title">
            <h2>{{ $cms['testimonials']['title'] ?? 'Loved by teams' }}</h2>
            <p>{{ $cms['testimonials']['subtitle'] ?? '' }}</p>
        </div>
        <div class="grid grid-3">
            @foreach($testis as $t)
                <div class="testimonial">
                    <div class="stars">{{ str_repeat('★', (int)($t->rating ?? 5)) }}</div>
                    <p class="quote">"{{ $t->body ?? $t->subtitle }}"</p>
                    <div class="who">
                        <div class="av">{{ strtoupper(substr($t->title ?? 'A', 0, 1)) }}</div>
                        <div>
                            <div class="name">{{ $t->title }}</div>
                            <div class="role">{{ $t->subtitle }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ FAQ ============ --}}
@php $faqs = App\Models\CmsItem::section('faq')->where('is_active',1)->orderBy('sort_order')->get(); @endphp
@if($faqs->count())
<section class="block alt">
    <div class="wrap" style="max-width: 800px;">
        <div class="section-title">
            <h2>{{ $cms['faq']['title'] ?? 'Frequently asked questions' }}</h2>
            <p>{{ $cms['faq']['subtitle'] ?? '' }}</p>
        </div>
        @foreach($faqs as $q)
            <details class="faq-item">
                <summary>{{ $q->title }}</summary>
                <div class="answer">{{ $q->body }}</div>
            </details>
        @endforeach
    </div>
</section>
@endif

{{-- ============ CTA ============ --}}
@if(!empty($cms['cta']['title']))
<section class="cta-block">
    <div class="wrap">
        <h2>{{ $cms['cta']['title'] }}</h2>
        <p>{{ $cms['cta']['subtitle'] ?? '' }}</p>
        @if(!empty($cms['cta']['button_text']))
            <a href="{{ $cms['cta']['button_url'] ?? '/register' }}" class="btn btn-primary">
                {{ $cms['cta']['button_text'] }} →
            </a>
        @endif
    </div>
</section>
@endif

{{-- ============ FOOTER ============ --}}
<footer>
    <div class="wrap">
        <div class="cols">
            <div>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px; color:#fff; font-weight:800; font-size:18px;">
                    <span class="brand-icon">S</span>
                    {{ $cms['seo']['meta_title'] ?? 'Stocky' }}
                </div>
                <p style="line-height:1.7; max-width:320px;">
                    {{ $cms['footer']['copyright'] ?? 'Modern inventory and stock management for teams of every size.' }}
                </p>
            </div>
            <div>
                <h4>Product</h4>
                <a href="#features">Features</a>
                <a href="#pricing">Pricing</a>
                <a href="/login">Sign in</a>
            </div>
            <div>
                <h4>Company</h4>
                <a href="/super/cms/privacy-policy">Privacy</a>
                <a href="/super/cms/terms-conditions">Terms</a>
            </div>
            <div>
                <h4>Follow</h4>
                <div class="social">
                    @forelse(App\Models\CmsItem::section('footer_social')->where('is_active',1)->get() as $s)
                        <a href="{{ $s->url }}" target="_blank" rel="noopener">●</a>
                    @empty
                        <span style="color:#64748b;">—</span>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="bottom">
            {{ $cms['footer']['copyright'] ?? '© '.date('Y').' Stocky. All rights reserved.' }}
        </div>
    </div>
</footer>

</body>
</html>

