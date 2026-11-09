@extends('saas.layout')
@section('title', 'Footer')
@section('topbar_title', 'CMS — Footer')
@section('content')
@include('saas.cms._styles')

<div class="cms-header">
    <div><h1>Footer</h1><p>Public site footer columns, social links, and copyright.</p></div>
    <a href="{{ url('/super/cms') }}" class="cms-btn">← Back</a>
</div>

@php
    $columns = old('columns', $footer['columns'] ?? []);
    if (empty($columns)) { $columns = [['heading'=>'','links'=>'']]; }
    $social = old('social', $footer['social'] ?? []);
    if (empty($social)) { $social = [['platform'=>'','url'=>'']]; }
@endphp

<form method="POST" action="{{ route('super.cms.footer.save') }}">
    @csrf @method('PUT')

    <div class="cms-card">
        <h2>Footer columns</h2>
        <div id="colList">
            @foreach($columns as $i => $col)
                <div class="cms-item">
                    <div class="cms-item-header">Column #{{ $i+1 }}</div>
                    <div class="cms-field"><label>Heading</label><input type="text" name="columns[{{ $i }}][heading]" value="{{ $col['heading'] ?? '' }}"></div>
                    <div class="cms-field"><label>Links <small>One per line: Label|/url</small></label>
                        <textarea name="columns[{{ $i }}][links]" rows="4">{{ $col['links'] ?? '' }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" class="cms-btn" onclick="addCol()">+ Add column</button>
    </div>

    <div class="cms-card">
        <h2>Social links</h2>
        <div id="socList">
            @foreach($social as $i => $s)
                <div class="cms-item" style="display:grid; grid-template-columns:1fr 2fr; gap:14px;">
                    <div class="cms-field"><label>Platform</label><input type="text" name="social[{{ $i }}][platform]" value="{{ $s['platform'] ?? '' }}" placeholder="twitter"></div>
                    <div class="cms-field"><label>URL</label><input type="text" name="social[{{ $i }}][url]" value="{{ $s['url'] ?? '' }}" placeholder="https://twitter.com/..."></div>
                </div>
            @endforeach
        </div>
        <button type="button" class="cms-btn" onclick="addSoc()">+ Add social</button>
    </div>

    <div class="cms-card">
        <h2>Copyright</h2>
        <div class="cms-field"><textarea name="copyright" rows="2">{{ old('copyright', $footer['copyright'] ?? '') }}</textarea></div>
    </div>

    <div class="cms-actions"><button type="submit" class="cms-btn cms-btn-primary">✓ Save Changes</button></div>
</form>

<script>
let colIndex = {{ count($columns) }};
let socIndex = {{ count($social) }};
function addCol() {
    document.getElementById('colList').insertAdjacentHTML('beforeend', `<div class="cms-item">
        <div class="cms-item-header">Column #${colIndex+1}</div>
        <div class="cms-field"><label>Heading</label><input type="text" name="columns[${colIndex}][heading]"></div>
        <div class="cms-field"><label>Links</label><textarea name="columns[${colIndex}][links]" rows="4"></textarea></div>
    </div>`);
    colIndex++;
}
function addSoc() {
    document.getElementById('socList').insertAdjacentHTML('beforeend', `<div class="cms-item" style="display:grid; grid-template-columns:1fr 2fr; gap:14px;">
        <div class="cms-field"><label>Platform</label><input type="text" name="social[${socIndex}][platform]"></div>
        <div class="cms-field"><label>URL</label><input type="text" name="social[${socIndex}][url]"></div>
    </div>`);
    socIndex++;
}
</script>
@endsection
