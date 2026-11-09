@extends('saas.layout')
@section('title', 'Landing page layouts')
@section('topbar_title', 'CMS — Landing Layouts')
@section('content')

@php
    $activeTpl = $cms['landing_template'] ?? 'default';
    $layouts = [
        'default'       => ['Classic',    'Full-width layout with floating navigation.'],
        'landing-two'   => ['Modern',     'Teal accent theme with floating navigation.'],
        'landing-three' => ['Structured', 'Section-based layout with hero, features, pricing, and CTA.'],
        'landing-four'  => ['Dark',       'Dark theme with teal accent and glass header.'],
        'landing-five'  => ['Light',      'Bright layout with alternating feature rows and timeline.'],
        'landing-six'   => ['Agency',     'Navy and mint theme with service cards and pricing.'],
    ];
@endphp

<div class="page-heading">
    <div>
        <h1>Landing page layouts</h1>
        <p>Pick the public homepage design. All layouts use the same CMS content.</p>
    </div>
    <a href="{{ url('/super/cms') }}" class="button">← Back</a>
</div>

<form method="POST" action="{{ route('super.cms.landing-pages.save') }}">
    @csrf
    @method('PUT')

    <div class="card" style="padding: 24px; margin-bottom: 20px;">
        <div style="border-bottom: 1px solid #eef0f4; padding-bottom: 14px; margin-bottom: 18px;">
            <h2 style="margin: 0; font-size: 16px; font-weight: 700;">
                <span style="color: #6b7280;">▦</span> Active layout
            </h2>
        </div>

        <p style="color: #6b7280; font-size: 13px; margin: 0 0 20px;">
            Visitors always open your landing at the site root URL. Legacy paths such as /landing-two through /landing-six redirect home.
        </p>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($layouts as $val => $meta)
                @php $isActive = $activeTpl === $val; @endphp
                <div style="border: {{ $isActive ? '2px solid #6366f1' : '1px solid #e5e7eb' }}; border-radius: 12px; padding: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: flex-start; gap: 12px; flex: 1;">
                            <input
                                type="radio"
                                name="landing_template"
                                id="tpl_{{ $val }}"
                                value="{{ $val }}"
                                {{ $isActive ? 'checked' : '' }}
                                style="width: 18px; height: 18px; margin-top: 4px; accent-color: #6366f1; cursor: pointer;"
                            >
                            <label for="tpl_{{ $val }}" style="cursor: pointer; flex: 1;">
                                <span style="font-weight: 700; display: block; font-size: 14px; color: #111827;">{{ $meta[0] }}</span>
                                <span style="color: #6b7280; font-size: 12.5px; display: block; margin-top: 4px;">{{ $meta[1] }}</span>
                                @if($isActive)
                                    <span style="display: inline-block; background: #6366f1; color: #fff; font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 999px; margin-top: 8px;">Live on /</span>
                                @endif
                            </label>
                        </div>
                        <div style="flex-shrink: 0;">
                            <a href="{{ url('/?_preview='.$val) }}" target="_blank" rel="noopener noreferrer"
                               class="button" style="min-height: 34px; padding: 0 12px; font-size: 12px;">
                                ↗ Preview
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <p style="color: #9ca3af; font-size: 12px; margin: 22px 0 0;">
            ℹ️ Preview opens the homepage with the selected layout. Non-default layouts require a super admin session to preview.
        </p>
    </div>

    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <button type="submit" class="button button-primary">✓ Save Changes</button>
        <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="button">
            👁 Open live site
        </a>
    </div>
</form>

@endsection