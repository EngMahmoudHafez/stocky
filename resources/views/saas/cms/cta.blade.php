@extends('saas.layout')
@section('title', 'CTA Section')
@section('topbar_title', 'CMS — CTA Section')
@section('content')
@include('saas.cms._styles')

<div class="cms-header">
    <div><h1>CTA Section</h1><p>Bottom call-to-action banner.</p></div>
    <a href="{{ url('/super/cms') }}" class="cms-btn">← Back</a>
</div>

<form method="POST" action="{{ route('super.cms.cta.save') }}">
    @csrf @method('PUT')
    <div class="cms-card">
        <h2>Content</h2>
        <div class="cms-field"><label>Title</label><input type="text" name="title" value="{{ old('title', $cta['title'] ?? '') }}"></div>
        <div class="cms-field"><label>Subtitle</label><textarea name="subtitle" rows="2">{{ old('subtitle', $cta['subtitle'] ?? '') }}</textarea></div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="cms-field"><label>Button text</label><input type="text" name="button_text" value="{{ old('button_text', $cta['button_text'] ?? '') }}"></div>
            <div class="cms-field"><label>Button URL</label><input type="text" name="button_url" value="{{ old('button_url', $cta['button_url'] ?? '') }}"></div>
        </div>
    </div>
    <div class="cms-actions"><button type="submit" class="cms-btn cms-btn-primary">✓ Save Changes</button></div>
</form>
@endsection
