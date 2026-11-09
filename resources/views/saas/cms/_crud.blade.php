@extends('saas.layout')
@section('title', $meta['plural'])
@section('topbar_title', 'CMS — '.$meta['plural'])
@section('content')

<style>
    .crud-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:24px; }
    .crud-header h1 { margin:0; font-size:26px; color:#111827; }
    .crud-header p { margin:6px 0 0; color:#6b7280; font-size:13px; }
    .crud-card { background:#fff; border:1px solid #e7e9ef; border-radius:14px; padding:22px; margin-bottom:20px; }
    .crud-card h2 { margin:0 0 14px; font-size:16px; font-weight:750; color:#111827; padding-bottom:12px; border-bottom:1px solid #eef0f4; display:flex; align-items:center; gap:8px; }
    .crud-btn { display:inline-flex; align-items:center; gap:7px; min-height:40px; padding:0 16px; border-radius:9px; border:1px solid #e5e7eb; background:#fff; color:#374151; font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; }
    .crud-btn:hover { background:#f9fafb; }
    .crud-btn-primary { color:#fff; border-color:#16a34a; background:#16a34a; }
    .crud-btn-primary:hover { background:#15803d; }
    .crud-btn-xs { min-height:30px; padding:0 10px; font-size:12px; }
    .crud-table { width:100%; border-collapse:collapse; }
    .crud-table th { text-align:left; padding:12px; font-size:11px; text-transform:uppercase; color:#6b7280; border-bottom:1px solid #eef0f4; font-weight:700; letter-spacing:.5px; }
    .crud-table td { padding:14px 12px; border-bottom:1px solid #f0f1f4; font-size:14px; color:#374151; vertical-align:middle; }
    .crud-table tr:hover td { background:#fafaff; }
    .crud-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:11px; font-weight:700; }
    .crud-pill-active   { background:#ecfdf5; color:#16a34a; }
    .crud-pill-inactive { background:#fef2f2; color:#dc2626; }
    .crud-icon { display:inline-flex; align-items:center; gap:6px; color:#6b7280; font-size:13px; font-family:monospace; }
    .crud-actions { display:inline-flex; gap:6px; }
    .crud-modal-backdrop { position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(2px); z-index:9000; display:none; align-items:center; justify-content:center; }
    .crud-modal-backdrop.open { display:flex; }
    .crud-modal { background:#fff; border-radius:16px; width:min(560px, 92vw); max-height:90vh; overflow-y:auto; padding:26px; box-shadow:0 25px 60px rgba(0,0,0,.25); }
    .crud-modal h3 { margin:0 0 18px; font-size:18px; font-weight:750; color:#111827; }
    .crud-field { margin-bottom:14px; }
    .crud-field label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; }
    .crud-field input[type=text], .crud-field input[type=url], .crud-field input[type=number], .crud-field select, .crud-field textarea {
        width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; font-family:inherit; outline:none; background:#fff;
    }
    .crud-field input:focus, .crud-field select:focus, .crud-field textarea:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
    .crud-field small { color:#9ca3af; font-size:11px; margin-top:5px; display:block; }
    .crud-modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:22px; padding-top:16px; border-top:1px solid #eef0f4; }
    .crud-empty { padding:40px 20px; text-align:center; color:#9ca3af; font-size:13px; }
</style>

<div class="crud-header">
    <div>
        <h1>{{ $meta['plural'] }}</h1>
        <p>Manage {{ strtolower($meta['plural']) }} displayed on the landing page.</p>
    </div>
    <button type="button" class="crud-btn crud-btn-primary" onclick="openCrudModal()">
        <span style="font-size:16px;">+</span> Add {{ $meta['singular'] }}
    </button>
</div>

@if(session('success'))<div class="alert alert-success" style="background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;padding:13px 15px;border-radius:10px;margin-bottom:20px;font-size:13px;">✓ {{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-error" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:13px 15px;border-radius:10px;margin-bottom:20px;font-size:13px;"><ul style="margin:0 0 0 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="crud-card">
    <h2>📋 {{ $meta['plural'] }} <span style="margin-left:auto; color:#9ca3af; font-weight:600; font-size:12px;">{{ $items->count() }} item{{ $items->count() == 1 ? '' : 's' }}</span></h2>

    @if($items->isEmpty())
        <div class="crud-empty">
            No {{ strtolower($meta['plural']) }} yet.
            <div style="margin-top:12px;"><button type="button" class="crud-btn crud-btn-primary" onclick="openCrudModal()">+ Add your first one</button></div>
        </div>
    @else
        <table class="crud-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    @if(in_array('title', $fields))   <th>Title</th>@endif
                    @if(in_array('subtitle', $fields))<th>Subtitle</th>@endif
                    @if(in_array('body', $fields))    <th>Body</th>@endif
                    @if(in_array('value', $fields))   <th>Value</th>@endif
                    @if(in_array('rating', $fields))  <th>Rating</th>@endif
                    @if(in_array('icon', $fields))    <th>Icon</th>@endif
                    @if(in_array('url', $fields))     <th>URL</th>@endif
                    <th style="width:100px;">Status</th>
                    <th style="width:140px; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        @if(in_array('title', $fields))   <td><strong>{{ \Illuminate\Support\Str::limit($item->title, 40) ?: '—' }}</strong></td>@endif
                        @if(in_array('subtitle', $fields))<td>{{ \Illuminate\Support\Str::limit($item->subtitle, 40) ?: '—' }}</td>@endif
                        @if(in_array('body', $fields))    <td>{{ \Illuminate\Support\Str::limit($item->body, 50) ?: '—' }}</td>@endif
                        @if(in_array('value', $fields))   <td><strong>{{ $item->value ?: '—' }}</strong></td>@endif
                        @if(in_array('rating', $fields))  <td>{{ $item->rating ? str_repeat('★', (int)$item->rating) : '—' }}</td>@endif
                        @if(in_array('icon', $fields))    <td>@if($item->icon)<span class="crud-icon"><i class="{{ $item->icon }}"></i> {{ $item->icon }}</span>@else — @endif</td>@endif
                        @if(in_array('url', $fields))     <td>{{ \Illuminate\Support\Str::limit($item->url, 30) ?: '—' }}</td>@endif
                        <td>
                            @if($item->is_active)<span class="crud-pill crud-pill-active">● Active</span>
                            @else<span class="crud-pill crud-pill-inactive">● Inactive</span>@endif
                        </td>
                        <td style="text-align:right;">
                            <div class="crud-actions">
                                <button type="button" class="crud-btn crud-btn-xs" onclick='openCrudModal(@json($item))'>Edit</button>
                                <form method="POST" action="{{ route('super.cms.'.$section.'.toggle', $item->id) }}" style="display:inline; margin:0;">
                                    @csrf <button type="submit" class="crud-btn crud-btn-xs">{{ $item->is_active ? 'Hide' : 'Show' }}</button>
                                </form>
                                <form method="POST" action="{{ route('super.cms.'.$section.'.destroy', $item->id) }}" style="display:inline; margin:0;" onsubmit="return confirm('Delete this {{ strtolower($meta['singular']) }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="crud-btn crud-btn-xs" style="color:#dc2626; border-color:#fecaca; background:#fef2f2;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ========== MODAL ========== --}}
<div class="crud-modal-backdrop" id="crudModalBackdrop">
    <div class="crud-modal" onclick="event.stopPropagation();">
        <h3 id="crudModalTitle">Add {{ $meta['singular'] }}</h3>
        <form method="POST" id="crudForm" action="{{ route('super.cms.'.$section.'.store') }}">
            @csrf
            <input type="hidden" name="_method" id="crudMethod" value="POST">
            <input type="hidden" name="id" id="crudId" value="">

            @if(in_array('title', $fields))
                <div class="crud-field"><label>Title</label><input type="text" name="title" id="f_title"></div>
            @endif
            @if(in_array('subtitle', $fields))
                <div class="crud-field"><label>Subtitle / Description</label><input type="text" name="subtitle" id="f_subtitle"></div>
            @endif
            @if(in_array('body', $fields))
                <div class="crud-field"><label>Body</label><textarea name="body" id="f_body" rows="4"></textarea></div>
            @endif
            @if(in_array('value', $fields))
                <div class="crud-field"><label>Value</label><input type="text" name="value" id="f_value" placeholder="10K+"></div>
            @endif
            @if(in_array('rating', $fields))
                <div class="crud-field"><label>Rating (1-5)</label><input type="number" name="rating" id="f_rating" min="1" max="5"></div>
            @endif
            @if(in_array('icon', $fields))
                <div class="crud-field">
                    <label>Bootstrap Icon class</label>
                    <input type="text" name="icon" id="f_icon" placeholder="bi bi-star">
                    <small>e.g. bi bi-building, bi bi-cart, bi bi-star</small>
                </div>
            @endif
            @if(in_array('color', $fields))
                <div class="crud-field">
                    <label>Color</label>
                    <select name="color" id="f_color">
                        <option value="">—</option>
                        <option value="green">green</option>
                        <option value="blue">blue</option>
                        <option value="amber">amber</option>
                        <option value="purple">purple</option>
                        <option value="teal">teal</option>
                        <option value="red">red</option>
                    </select>
                </div>
            @endif
            @if(in_array('image', $fields))
                <div class="crud-field"><label>Image URL</label><input type="text" name="image" id="f_image"></div>
            @endif
            @if(in_array('url', $fields))
                <div class="crud-field"><label>URL</label><input type="text" name="url" id="f_url"></div>
            @endif
            @if(in_array('is_active', $fields))
                <div class="crud-field">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_active" id="f_is_active" value="1" checked style="width:auto;">
                        <span>Active (visible on landing page)</span>
                    </label>
                </div>
            @endif

            <div class="crud-modal-actions">
                <button type="button" class="crud-btn" onclick="closeCrudModal()">Cancel</button>
                <button type="submit" class="crud-btn crud-btn-primary" id="crudSubmit">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCrudModal(item) {
    const form = document.getElementById('crudForm');
    const method = document.getElementById('crudMethod');
    const idField = document.getElementById('crudId');
    const title = document.getElementById('crudModalTitle');
    const submit = document.getElementById('crudSubmit');

    if (item) {
        title.textContent = 'Edit {{ $meta["singular"] }}';
        method.value = 'PUT';
        form.action = '{{ url("/super/cms/'.$section) }}/' + item.id;
        idField.value = item.id;
        document.getElementById('f_title')    && (document.getElementById('f_title').value    = item.title    || '');
        document.getElementById('f_subtitle') && (document.getElementById('f_subtitle').value = item.subtitle || '');
        document.getElementById('f_body')     && (document.getElementById('f_body').value     = item.body     || '');
        document.getElementById('f_value')    && (document.getElementById('f_value').value    = item.value    || '');
        document.getElementById('f_rating')   && (document.getElementById('f_rating').value   = item.rating   || '');
        document.getElementById('f_icon')     && (document.getElementById('f_icon').value     = item.icon     || '');
        document.getElementById('f_color')    && (document.getElementById('f_color').value    = item.color    || '');
        document.getElementById('f_image')    && (document.getElementById('f_image').value    = item.image    || '');
        document.getElementById('f_url')      && (document.getElementById('f_url').value      = item.url      || '');
        document.getElementById('f_is_active')&& (document.getElementById('f_is_active').checked = !!item.is_active);
    } else {
        title.textContent = 'Add {{ $meta["singular"] }}';
        method.value = 'POST';
        form.action = '{{ route("super.cms.".$section.".store") }}';
        idField.value = '';
        form.reset();
        document.getElementById('f_is_active') && (document.getElementById('f_is_active').checked = true);
    }
    document.getElementById('crudModalBackdrop').classList.add('open');
}
function closeCrudModal() {
    document.getElementById('crudModalBackdrop').classList.remove('open');
}
document.getElementById('crudModalBackdrop').addEventListener('click', function(e){
    if (e.target === this) closeCrudModal();
});
document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeCrudModal();
});
</script>

@endsection
