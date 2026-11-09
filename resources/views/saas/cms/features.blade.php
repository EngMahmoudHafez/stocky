@extends('saas.layout')
@section('title', 'Features')
@section('topbar_title', 'CMS — Features')

@section('content')

@php
    $section = 'features';
    $meta    = ['singular' => 'Feature', 'plural' => 'Feature items'];

    $sectionSettings = $cms['features'] ?? [];
    $showSection     = $sectionSettings['show_section'] ?? true;
    $sectionTitle    = $sectionSettings['title'] ?? 'Everything You Need';
    $sectionSubtitle = $sectionSettings['subtitle'] ?? 'Powerful features to run your entire inventory operation from one place.';

    $items = \App\Models\CmsItem::section($section)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();
@endphp

<style>
    .cms-page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 22px; flex-wrap: wrap; }
    .cms-page-head h1 { margin: 0; font-size: 24px; font-weight: 800; color: #111827; letter-spacing: -.4px; }
    .cms-page-head p { margin: 6px 0 0; color: #6b7280; font-size: 13px; }
    .cms-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 22px; margin-bottom: 20px; }
    .cms-card-head { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #eef0f4; font-weight: 750; font-size: 15px; color: #111827; }
    .cms-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px; }
    @media (max-width: 800px) { .cms-row { grid-template-columns: 1fr; } }
    .cms-field label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .cms-field input[type=text], .cms-field select, .cms-field textarea { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; background: #fff; color: #111827; }
    .cms-field input:focus, .cms-field select:focus, .cms-field textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
    .cms-field small { display: block; color: #9ca3af; font-size: 11px; margin-top: 5px; }
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
    .cms-modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; padding-top: 16px; border-top: 1px solid #eef0f4; }
</style>

<div class="cms-page-head">
    <div>
        <h1>Features Section</h1>
        <p>Manage feature cards displayed on the landing page.</p>
    </div>
    <button type="button" class="cms-btn cms-btn-primary" onclick="openFeatureModal()">
        + Add Feature
    </button>
</div>

{{-- ========== SECTION HEADER FORM ========== --}}
<form method="POST" action="{{ url('/super/cms/features/section') }}">
    @csrf
    <div class="cms-card">
        <div class="cms-card-head">⚙ Section header</div>

        <div class="cms-row">
            <div class="cms-field">
                <label>Section title</label>
                <input type="text" name="title" value="{{ $sectionTitle }}">
            </div>
            <div class="cms-field">
                <label>Section subtitle</label>
                <input type="text" name="subtitle" value="{{ $sectionSubtitle }}">
            </div>
        </div>

        <div class="cms-field" style="margin-bottom: 18px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500;">
                <input type="checkbox" name="show_section" value="1" {{ $showSection ? 'checked' : '' }} style="width: auto;">
                <span>Show features section</span>
            </label>
        </div>

        <button type="submit" class="cms-btn">Save section</button>
    </div>
</form>

{{-- ========== FEATURE ITEMS TABLE ========== --}}
<div class="cms-card">
    <div class="cms-card-head">📋 Feature items</div>

    @if($items->isEmpty())
        <div class="cms-empty">
            No features yet.
            <div style="margin-top: 14px;">
                <button type="button" class="cms-btn cms-btn-primary" onclick="openFeatureModal()">
                    + Add your first feature
                </button>
            </div>
        </div>
    @else
        <table class="cms-table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Title</th>
                    <th>Icon / Image</th>
                    <th style="width: 110px;">Status</th>
                    <th style="width: 150px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $item->title ?: '—' }}</strong></td>
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
                            <button type="button" class="cms-btn cms-btn-xs"
                                    onclick='openFeatureModal(@json($item))'>Edit</button>

                            <form method="POST" action="{{ url('/super/cms/features/'.$item->id.'/toggle') }}" style="display: inline; margin: 0;">
                                @csrf
                                <button type="submit" class="cms-btn cms-btn-xs">
                                    {{ $item->is_active ? 'Hide' : 'Show' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ url('/super/cms/features/'.$item->id) }}" style="display: inline; margin: 0;"
                                  onsubmit="return confirm('Delete this feature?');">
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

{{-- ========== MODAL ========== --}}
<div class="cms-modal-bg" id="featureModalBg">
    <div class="cms-modal" onclick="event.stopPropagation();">
        <h3 id="featureModalTitle">Add Feature</h3>
        <form method="POST" id="featureForm" action="{{ url('/super/cms/features') }}">
            @csrf
            <input type="hidden" name="_method" id="featureMethod" value="POST">

            <div class="cms-field" style="margin-bottom: 14px;">
                <label>Title *</label>
                <input type="text" name="title" id="f_title" required placeholder="Multi-Warehouse">
            </div>

            <div class="cms-field" style="margin-bottom: 14px;">
                <label>Description</label>
                <textarea name="body" id="f_body" rows="3" placeholder="Manage stock across unlimited warehouses."></textarea>
            </div>

            <div class="cms-field" style="margin-bottom: 14px;">
                <label>Bootstrap Icon class</label>
                <input type="text" name="icon" id="f_icon" placeholder="bi bi-building">
                <small>e.g. <code>bi bi-building</code>, <code>bi bi-shop-window</code>, <code>bi bi-graph-up-arrow</code></small>
            </div>

            <div class="cms-field" style="margin-bottom: 14px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500;">
                    <input type="checkbox" name="is_active" id="f_is_active" value="1" checked style="width: auto;">
                    <span>Active (visible on landing page)</span>
                </label>
            </div>

            <div class="cms-modal-actions">
                <button type="button" class="cms-btn" onclick="closeFeatureModal()">Cancel</button>
                <button type="submit" class="cms-btn cms-btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openFeatureModal(item) {
        const form     = document.getElementById('featureForm');
        const method   = document.getElementById('featureMethod');
        const title    = document.getElementById('featureModalTitle');

        if (item) {
            title.textContent  = 'Edit Feature';
            method.value       = 'PUT';
            form.action        = '/super/cms/features/' + item.id;
            document.getElementById('f_title').value     = item.title || '';
            document.getElementById('f_body').value      = item.body || '';
            document.getElementById('f_icon').value      = item.icon || '';
            document.getElementById('f_is_active').checked = !!item.is_active;
        } else {
            title.textContent  = 'Add Feature';
            method.value       = 'POST';
            form.action        = '/super/cms/features';
            form.reset();
            document.getElementById('f_is_active').checked = true;
        }

        document.getElementById('featureModalBg').classList.add('open');
    }

    function closeFeatureModal() {
        document.getElementById('featureModalBg').classList.remove('open');
    }

    document.getElementById('featureModalBg').addEventListener('click', function (e) {
        if (e.target === this) closeFeatureModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeFeatureModal();
    });
</script>

@endsection
