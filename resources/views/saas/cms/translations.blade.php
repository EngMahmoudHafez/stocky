@extends('saas.layout')
@section('title', 'Translations')
@section('topbar_title', 'CMS — Translations')
@section('content')
@include('saas.cms._styles')

<div class="cms-header">
    <div><h1>Translations</h1><p>Override CMS strings per language.</p></div>
    <a href="{{ url('/super/cms') }}" class="cms-btn">← Back</a>
</div>

@php
    $langs = ['en'=>'English','fr'=>'Français','ar'=>'العربية','es'=>'Español','hi'=>'हिन्दी','bn'=>'বাংলা','tr'=>'Türkçe','de'=>'Deutsch','pt'=>'Português','ur'=>'اردو'];
@endphp

<div class="cms-card">
    <h2>Existing translations</h2>
    @php $hasAny = false; @endphp
    @foreach($langs as $code => $label)
        @if(!empty($translations[$code]))
            @php $hasAny = true; @endphp
            <h3 style="margin:18px 0 8px; font-size:13px; color:#4338ca;">{{ $label }} ({{ $code }})</h3>
            <table class="table" style="margin-bottom:0;">
                <thead><tr><th>Key</th><th>Value</th></tr></thead>
                <tbody>
                @foreach($translations[$code] as $k => $v)
                    <tr><td><code>{{ $k }}</code></td><td>{{ $v }}</td></tr>
                @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
    @if(!$hasAny)
        <p style="color:#9ca3af; font-size:13px;">No translations yet. Add the first one below.</p>
    @endif
</div>

<div class="cms-card">
    <h2>Add / update a translation</h2>
    <form method="POST" action="{{ route('super.cms.translations.save') }}">
        @csrf @method('PUT')
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="cms-field"><label>Language</label>
                <select name="lang" required>
                    @foreach($langs as $code => $label)
                        <option value="{{ $code }}">{{ $label }} ({{ $code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="cms-field"><label>Key</label><input type="text" name="key" required placeholder="hero.title"></div>
        </div>
        <div class="cms-field"><label>Value</label><textarea name="value" rows="3"></textarea></div>
        <div class="cms-actions"><button type="submit" class="cms-btn cms-btn-primary">✓ Save Translation</button></div>
    </form>
</div>
@endsection
