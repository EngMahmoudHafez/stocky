@extends('saas.layout')
@section('title', 'Stats / Trust Bar')
@section('topbar_title', 'CMS — Stats / Trust Bar')

@section('content')

@php
    $section = 'stats';
    $meta    = ['singular' => 'Stat', 'plural' => 'Stat items'];
    $items   = \App\Models\CmsItem::section($section)->orderBy('sort_order')->orderBy('id')->get();
@endphp

<style>
    .cms-page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 22px; flex-wrap: wrap; }
    .cms-page-head h1 { margin: 0; font-size: 24px; font-weight: 800; color: #111827; letter-spacing: -.4px; }
    .cms-page-head p { margin: 6px 0 0; color: #6b7280; font-size: 13px; }
    .cms-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 22px; margin-bottom: 20px; }
    .cms-card-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #eef0f4; font-weight: 750; font-size: 15px; color: #111827; }
    .cms-btn { display: inline-flex; align-items: center; gap: 7px; min-height: 40px; padding: 0 16px; border-radius: 9px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; }
    .cms-btn:hover { background: #f9fafb; }
    .cms-btn-primary { color: #fff; border-color: #16a34a; background: #16a34a; }
    .cms-btn-primary:hover { background: #15803d; border-color: #15803d; }
    .cms-btn-xs { min-height: 30px; padding: 0 10px; font-size: 12px; }
    .cms-table { width: 100%; border-collapse: collapse; }
    .cms-table th { text-align: left; padding: 12px; font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; letter-spacing: .6px; border-bottom: 1px solid #eef0f4; }
    .cms-table td { padding: 14px 12px; border-bottom: 1px solid #f0f1f4; font-size: 14px; color: #374151; vertical-align: middle; }
    .cms-table tr:hover td { background: #fafaff; }
    .cms-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .cms-pill-active { background: #ecfdf5; color: #16a34a; }
    .cms-pill-inactive { background: #fef2f2; color: #dc2626; }
    .cms-icon-cell { font-family: ui-monospace, monospace; color: #6b7280; font-size: 13px; }
    .cms-empty { padding: 40px 20px; text-align: center; color: #9ca3af; font-size: 13px; }
    .cms-modal-bg { position: fixed; inset: 0; background: rgba(15,23,42,.55); backdrop-filter: blur(2px); z-index: 9000; display: none; align-items: center; justify-content: center; }
    .cms-modal-bg.open { display: flex; }
    .cms-modal { background: #fff; border-radius: 16px; width: min(560px, 92vw); max-height: 90vh; overflow-y: auto; padding: 26px; box-shadow: 0 25px 60px rgba(0,0,0,.25); }
    .cms-modal h3 { margin: 0 0 18px; font-size: 18px; font-weight: 750; color: #111827; }
    .cms-field { margin-bottom: 14px; }
    .cms-field label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .cms-field input[type=text], .cms-field select, .cms-field textarea { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; background: #fff; color: #111827; }
    .cms-field input:focus, .cms-field select:focus, .cms-field textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
    .cms-field small { display: block; color: #9ca3af; font-size: 11px; margin-top: 5px; }
    .cms-modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; padding-top: 16px; border-top: 1px solid #eef0f4; }
</style>

<div class="cms-page-head">
    <div>
        <h1>Stats / Trust Bar</h1>
        <p>Manage the trust bar stats shown below the hero section.</p>
    </div>
    <button type="button" class="cms-btn cms-btn-primary" onclick="openStatModal()">
        + Add Stat
    </button>
</div>

@if(session('success'))
    <div style="background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;padding:13px 15px;border-radius:10px;margin-bottom:20px;font-size:13px;">✓ {{ session('success') }}</div>
@endif
@if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:13px 15px;border-radius:10px;margin-bottom:20px;font-size:13px;">
        <strong>Please check:</strong>
        <ul style="margin:7px 0 0 18px;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="cms-card">
    <div class="cms-card-head">
        <span>📊 Stat items</span>
        <span style="color:#9ca3af; font-weight:600; font-size:12px;">{{ $items->count() }} item{{ $items->count() === 1 ? '' : 's' }}</span>
    </div>

    @if($items->isEmpty())
        <div class="cms-empty">
            No stats yet.
            <div style="margin-top: 14px;">
                <button type="button" class="cms-btn cms-btn-primary" onclick="openStatModal()">
                    + Add your first stat
                </button>
            </div>
        </div>
    @else
        <table class="cms-table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Value</th>
                    <th>Label</th>
                    <th>Icon Class</th>
                    <th style="width: 110px;">Status</th>
                    <th style="width: 150px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $item->value ?: '—' }}</strong></td>
                        <td>{{ $item->title ?: '—' }}</td>
                        <td class="cms-icon-cell">
                            @if($item->icon)
                                <i class="{{ $item->icon }}"></i> &nbsp;{{ $item->icon }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($item->is_active)
                                <span class="cms-pill cms-pill-active">● Active</span>
                            @else
                                <span class="cms-pill cms-pill-inactive">● Inactive</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="cms-btn cms-btn-xs" onclick='openStatModal(@json($item))'>Edit</button>

                            <form method="POST" action="{{ url('/super/cms/stats/'.$item->id.'/toggle') }}" style="display: inline; margin: 0;">
                                @csrf
                                <button type="submit" class="cms-btn cms-btn-xs">
                                    {{ $item->is_active ? 'Hide' : 'Show' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ url('/super/cms/stats/'.$item->id) }}" style="display: inline; margin: 0;"
                                  onsubmit="return confirm('Delete this stat?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="cms-btn cms-btn-xs"
                                        style="color: #dc2626; border-color: #fecaca; background: #fef2f2;">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- MODAL --}}
<div class="cms-modal-bg" id="statModalBg">
    <div class="cms-modal" onclick="event.stopPropagation();">
        <h3 id="statModalTitle">Add Stat</h3>
        <form method="POST" id="statForm" action="{{ url('/super/cms/stats') }}">
            @csrf
            <input type="hidden" name="_method" id="statMethod" value="POST">

            <div class="cms-field">
                <label>Value *</label>
                <input type="text" name="value" id="s_value" required placeholder="10K+">
            </div>

            <div class="cms-field">
                <label>Label *</label>
                <input type="text" name="title" id="s_title" required placeholder="Businesses">
            </div>

            <div class="cms-field">
                <label>Bootstrap Icon class</label>
                <input type="text" name="icon" id="s_icon" placeholder="bi bi-building">
                <small>e.g. bi bi-building, bi bi-box-seam, bi bi-shield-check, bi bi-star-fill</small>
            </div>

            <div class="cms-field">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:500;">
                    <input type="checkbox" name="is_active" id="s_is_active" value="1" checked style="width:auto;">
                    <span>Active (visible on landing page)</span>
                </label>
            </div>

            <div class="cms-modal-actions">
                <button type="button" class="cms-btn" onclick="closeStatModal()">Cancel</button>
                <button type="submit" class="cms-btn cms-btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openStatModal(item) {
        const form   = document.getElementById('statForm');
        const method = document.getElementById('statMethod');
        const title  = document.getElementById('statModalTitle');

        if (item) {
            title.textContent = 'Edit Stat';
            method.value      = 'PUT';
            form.action       = '/super/cms/stats/' + item.id;
            document.getElementById('s_value').value     = item.value || '';
            document.getElementById('s_title').value     = item.title || '';
            document.getElementById('s_icon').value      = item.icon || '';
            document.getElementById('s_is_active').checked = !!item.is_active;
        } else {
            title.textContent = 'Add Stat';
            method.value      = 'POST';
            form.action       = '/super/cms/stats';
            form.reset();
            document.getElementById('s_is_active').checked = true;
        }

        document.getElementById('statModalBg').classList.add('open');
    }

    function closeStatModal() {
        document.getElementById('statModalBg').classList.remove('open');
    }

    document.getElementById('statModalBg').addEventListener('click', function (e) {
        if (e.target === this) closeStatModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeStatModal();
    });
</script>

@endsection
