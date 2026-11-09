@extends('saas.layout')
@section('title', 'Terms & Conditions')
@section('topbar_title', 'CMS — Terms & Conditions')
@section('content')
@include('saas.cms._styles')

<div class="cms-header">
    <div><h1>Terms &amp; Conditions</h1><p>Public /terms-conditions page content.</p></div>
    <a href="{{ url('/super/cms') }}" class="cms-btn">← Back</a>
</div>

<form method="POST" action="{{ route('super.cms.terms.save') }}">
    @csrf @method('PUT')
    <div class="cms-card">
        <div class="cms-field"><label>Page title</label><input type="text" name="title" value="{{ old('title', $terms['title'] ?? 'Terms & Conditions') }}"></div>
        <div class="cms-field"><label>Body <small>HTML allowed</small></label><textarea name="body" rows="20" style="font-family:monospace; font-size:13px;">{{ old('body', $terms['body'] ?? '') }}</textarea></div>
        @if(!empty($terms['updated_at']))
            <p style="color:#9ca3af; font-size:12px; margin-top:8px;">Last updated: {{ $terms['updated_at'] }}</p>
        @endif
    </div>
    <div class="cms-actions"><button type="submit" class="cms-btn cms-btn-primary">✓ Save Changes</button></div>
</form>
@endsection
