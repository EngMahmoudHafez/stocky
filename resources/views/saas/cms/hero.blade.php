@extends('saas.layout')
@section('title', 'Hero Section')
@section('topbar_title', 'CMS — Hero Section')
@section('content')
@include('saas.cms._styles')

<div class="cms-header">
    <div><h1>Hero Section</h1><p>Top banner on the public homepage.</p></div>
    <a href="{{ url('/super/cms') }}" class="cms-btn">← Back</a>
</div>

<form method="POST" action="{{ route('super.cms.hero.save') }}">
    @csrf @method('PUT')
    <div class="cms-card">
        <h2>Hero content</h2>

        <div class="cms-field"><label>Badge</label>
            <input type="text" name="badge" value="{{ old('badge', $hero['badge'] ?? '') }}" placeholder="Secure & Reliable">
        </div>

        <div class="cms-field"><label>Title *</label>
            <input type="text" name="title" value="{{ old('title', $hero['title'] ?? '') }}" placeholder="Manage your business smarter.">
        </div>

        <div class="cms-field"><label>Subtitle</label>
            <textarea name="subtitle" rows="3" placeholder="Streamline inventory, track sales, and grow your business.">{{ old('subtitle', $hero['subtitle'] ?? '') }}</textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="cms-field"><label>Primary CTA text</label>
                <input type="text" name="cta_primary" value="{{ old('cta_primary', $hero['cta_primary'] ?? '') }}" placeholder="Get Started">
            </div>
            <div class="cms-field"><label>Primary CTA URL</label>
                <input type="text" name="cta_primary_url" value="{{ old('cta_primary_url', $hero['cta_primary_url'] ?? '') }}" placeholder="/register">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="cms-field"><label>Secondary CTA text</label>
                <input type="text" name="cta_secondary" value="{{ old('cta_secondary', $hero['cta_secondary'] ?? '') }}" placeholder="Watch demo">
            </div>
            <div class="cms-field"><label>Secondary CTA URL</label>
                <input type="text" name="cta_secondary_url" value="{{ old('cta_secondary_url', $hero['cta_secondary_url'] ?? '') }}" placeholder="/demo">
            </div>
        </div>

        <div class="cms-field"><label>Hero image URL</label>
            <input type="text" name="hero_image" value="{{ old('hero_image', $hero['hero_image'] ?? '') }}" placeholder="https://...">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
            <div class="cms-field"><label>Bullet 1</label><input type="text" name="bullet_1" value="{{ old('bullet_1', $hero['bullet_1'] ?? '') }}"></div>
            <div class="cms-field"><label>Bullet 2</label><input type="text" name="bullet_2" value="{{ old('bullet_2', $hero['bullet_2'] ?? '') }}"></div>
            <div class="cms-field"><label>Bullet 3</label><input type="text" name="bullet_3" value="{{ old('bullet_3', $hero['bullet_3'] ?? '') }}"></div>
        </div>
    </div>

    <div class="cms-actions"><button type="submit" class="cms-btn cms-btn-primary">✓ Save Changes</button></div>
</form>
@endsection
