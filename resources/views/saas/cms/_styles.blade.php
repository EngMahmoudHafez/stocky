<style>
    .cms-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:24px; }
    .cms-header h1 { margin:0; font-size:26px; letter-spacing:-.5px; color:#111827; }
    .cms-header p  { margin:6px 0 0; color:#6b7280; font-size:13px; }
    .cms-card { background:#fff; border:1px solid #e7e9ef; border-radius:14px; padding:22px; margin-bottom:20px; }
    .cms-card h2 { margin:0 0 14px; font-size:16px; font-weight:750; color:#111827; padding-bottom:12px; border-bottom:1px solid #eef0f4; }
    .cms-field { margin-bottom:16px; }
    .cms-field label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; }
    .cms-field input[type=text],
    .cms-field input[type=url],
    .cms-field input[type=email],
    .cms-field input[type=number],
    .cms-field select,
    .cms-field textarea {
        width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px;
        outline:none; font-size:14px; font-family:inherit; background:#fff; color:#111827;
    }
    .cms-field input:focus, .cms-field select:focus, .cms-field textarea:focus {
        border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1);
    }
    .cms-field small { display:block; color:#9ca3af; font-size:11px; margin-top:5px; }
    .cms-actions { display:flex; gap:10px; margin-top:20px; }
    .cms-btn { display:inline-flex; align-items:center; gap:7px; min-height:42px; padding:0 18px;
               border-radius:9px; border:1px solid #e5e7eb; background:#fff; color:#374151;
               font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; }
    .cms-btn:hover { background:#f9fafb; }
    .cms-btn-primary { color:#fff; border-color:#16a34a; background:#16a34a; }
    .cms-btn-primary:hover { background:#15803d; }
    .cms-btn-outline { color:#4338ca; border-color:#c7d2fe; background:#eef2ff; }
    .cms-item { border:1px solid #eef0f4; border-radius:10px; padding:14px; margin-bottom:12px; background:#fafbff; }
    .cms-item-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; font-weight:700; font-size:13px; color:#4338ca; }
    .cms-radio-row { display:flex; gap:14px; align-items:flex-start; padding:16px; border:1px solid #eef0f4; border-radius:12px; margin-bottom:12px; }
    .cms-radio-row.active { border-color:#6366f1; background:#f5f3ff; }
    .cms-radio-row input[type=radio] { margin-top:4px; accent-color:#6366f1; }
    .cms-radio-row .info { flex:1; }
    .cms-radio-row .info strong { display:block; font-size:14px; color:#111827; margin-bottom:4px; }
    .cms-radio-row .info span { color:#6b7280; font-size:12px; }
    .cms-badge { display:inline-block; padding:3px 9px; border-radius:999px; font-size:10px; font-weight:700; background:#6366f1; color:#fff; margin-left:8px; }
</style>
