@extends('saas.layout')
@section('title', 'SEO Settings')
@section('topbar_title', 'CMS — SEO Settings')
@section('content')
@include('saas.cms._styles')

<div class="cms-header">
    <div><h1>SEO Settings</h1><p>Meta tags and social previews.</p></div>
    <a href="{{ url('/super/cms') }}" class="cms-btn">← Back</a>
</div>

<form method="POST" action="{{ route('super.cms.seo.save') }}">
    @csrf @method('PUT')

    <div class="cms-card">
        <h2>Meta tags</h2>
        <div class="cms-field"><label>Meta title</label><input type="text" name="meta_title" value="{{ old('meta_title', $seo['meta_title'] ?? '') }}" maxlength="70"></div>
        <div class="cms-field"><label>Meta description</label><textarea name="meta_description" rows="2" maxlength="160">{{ old('meta_description', $seo['meta_description'] ?? '') }}</textarea></div>
        <div class="cms-field"><label>Meta keywords</label><input type="text" name="meta_keywords" value="{{ old('meta_keywords', $seo['meta_keywords'] ?? '') }}"></div>
        <div class="cms-field"><label>Robots</label><input type="text" name="robots" value="{{ old('robots', $seo['robots'] ?? 'index,follow') }}"></div>
    </div>

    <div class="cms-card">
        <h2>Open Graph / Twitter</h2>
        <div class="cms-field"><label>OG Title</label><input type="text" name="og_title" value="{{ old('og_title', $seo['og_title'] ?? '') }}"></div>
        <div class="cms-field"><label>OG Description</label><textarea name="og_description" rows="2">{{ old('og_description', $seo['og_description'] ?? '') }}</textarea></div>
        <div class="cms-field"><label>OG Image URL</label><input type="text" name="og_image" value="{{ old('og_image', $seo['og_image'] ?? '') }}"></div>
        <div class="cms-field"><label>Twitter handle</label><input type="text" name="twitter_handle" value="{{ old('twitter_handle', $seo['twitter_handle'] ?? '') }}"></div>
    </div>

    <div class="cms-card">
        <h2>Analytics</h2>
        <div class="cms-field"><label>Google Analytics ID</label><input type="text" name="google_analytics" value="{{ old('google_analytics', $seo['google_analytics'] ?? '') }}" placeholder="G-XXXXXXXXXX"></div>
    </div>

    <div class="cms-actions"><button type="submit" class="cms-btn cms-btn-primary">✓ Save Changes</button></div>
</form>
@endsection
