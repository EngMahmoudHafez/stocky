@extends('saas.layout')
@section('title', 'Pricing Section')
@section('topbar_title', 'CMS — Pricing Section')
@section('content')
@include('saas.cms._styles')

<div class="cms-header">
    <div>
        <h1>Pricing Section</h1>
        <p>Configure how pricing plans are displayed on the landing page.</p>
    </div>
    <a href="{{ url('/super/cms') }}" class="cms-btn">← Back</a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;padding:13px 15px;border-radius:10px;margin-bottom:20px;font-size:13px;">✓ {{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:13px 15px;border-radius:10px;margin-bottom:20px;font-size:13px;">
        <strong>Please check:</strong>
        <ul style="margin:7px 0 0 18px;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ url('/super/cms/pricing') }}">
    @csrf
    @method('PUT')

    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start;">

        {{-- LEFT: FORM --}}
        <div class="cms-card">
            <h2><span style="color:#6b7280;">$</span> Pricing settings</h2>

            <div class="cms-field">
                <label>Section title</label>
                <input type="text" name="title" value="{{ old('title', $pricing['title'] ?? 'Simple, Transparent Pricing') }}">
            </div>

            <div class="cms-field">
                <label>Section subtitle</label>
                <textarea name="subtitle" rows="3">{{ old('subtitle', $pricing['subtitle'] ?? 'Choose the plan that fits your business. Upgrade or downgrade anytime.') }}</textarea>
            </div>

            <div class="cms-field">
                <label>Footer note</label>
                <textarea name="note" rows="2" placeholder="Optional. Text under the pricing grid.">{{ old('note', $pricing['note'] ?? '') }}</textarea>
            </div>

            {{-- ==== TOGGLES (the missing part) ==== --}}
            <div style="margin: 20px 0 6px; padding-top: 16px; border-top: 1px solid #eef0f4;">

                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 12px;">
                    <input type="checkbox" name="show_monthly" value="1"
                           {{ ($pricing['show_monthly'] ?? true) ? 'checked' : '' }}
                           style="width: 16px; height: 16px; accent-color: #16a34a;">
                    <span>Show monthly pricing</span>
                </label>

                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 12px;">
                    <input type="checkbox" name="show_yearly" value="1"
                           {{ ($pricing['show_yearly'] ?? true) ? 'checked' : '' }}
                           style="width: 16px; height: 16px; accent-color: #16a34a;">
                    <span>Show yearly pricing</span>
                </label>

                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">
                    <input type="checkbox" name="load_from_database" value="1"
                           {{ ($pricing['load_from_database'] ?? true) ? 'checked' : '' }}
                           style="width: 16px; height: 16px; accent-color: #16a34a;">
                    <span>Load plans from database</span>
                </label>
                <p style="font-size: 11.5px; color: #9ca3af; margin: 0 0 12px 26px;">
                    When enabled, pricing cards are generated automatically from your <a href="{{ url('/super/plans') }}" style="color:#6366f1;">Plans</a> table.
                </p>

                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 600; color: #374151;">
                    <input type="checkbox" name="show_section" value="1"
                           {{ ($pricing['show_section'] ?? true) ? 'checked' : '' }}
                           style="width: 16px; height: 16px; accent-color: #16a34a;">
                    <span>Show pricing section</span>
                </label>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 26px;">
                <button type="submit" class="cms-btn cms-btn-primary">✓ Save</button>
                <a href="{{ url('/#pricing') }}" target="_blank" rel="noopener" class="cms-btn">↗ Preview</a>
            </div>
        </div>

        {{-- RIGHT: TIPS --}}
        <div class="cms-card">
            <h2>💡 Tips</h2>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="display:flex; gap:10px; font-size:13px; color:#6b7280; margin-bottom:12px;">
                    <span style="color:#16a34a;">✓</span>
                    <span>Enable "Load from database" to automatically sync pricing cards with your Plans.</span>
                </li>
                <li style="display:flex; gap:10px; font-size:13px; color:#6b7280; margin-bottom:12px;">
                    <span style="color:#16a34a;">✓</span>
                    <span>You can show both monthly and yearly toggle on the landing page.</span>
                </li>
                <li style="display:flex; gap:10px; font-size:13px; color:#6b7280;">
                    <span style="color:#16a34a;">✓</span>
                    <span>Manage plans and their limits from the <a href="{{ url('/super/plans') }}" style="color:#6366f1;">Plans</a> page.</span>
                </li>
            </ul>
        </div>
    </div>
</form>

@endsection