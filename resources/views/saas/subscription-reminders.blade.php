@extends('saas.layout')

@section('title', 'Subscription Reminders')
@section('topbar_title', 'Subscription Reminders')

@section('content')

<style>
    .rem-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
    .rem-page-header h1 { margin: 0; font-size: 28px; line-height: 1.2; letter-spacing: -.7px; color: #111827; }
    .rem-page-header p { margin: 8px 0 0; color: #6b7280; font-size: 14px; }
    .rem-header-actions { display: flex; gap: 10px; }
    .btn-outline { display: inline-flex; align-items: center; gap: 6px; min-height: 38px; padding: 0 16px; border-radius: 9px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; }
    .btn-outline:hover { background: #f9fafb; }

    .rem-filter-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px 14px 0 0; padding: 14px 18px; display: flex; align-items: center; gap: 14px; border-bottom: none; }
    .rem-filter-card .search-wrap { flex: 1; position: relative; }
    .rem-filter-card .search-wrap span { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 14px; }
    .rem-filter-card input[type="text"] { width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; }
    .rem-filter-card input[type="text"]:focus { border-color: #16a34a; }
    .rem-filter-card select { padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; color: #374151; min-width: 150px; background: #fff; }
    .rem-filter-card .rem-count { margin-left: auto; color: #9ca3af; font-size: 12px; }

    .rem-table-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 0 0 14px 14px; overflow: hidden; box-shadow: 0 2px 8px rgba(17,24,39,.025); }
    .rem-table { width: 100%; border-collapse: collapse; }
    .rem-table th { text-align: left; border-bottom: 1px solid #eef0f4; color: #6b7280; padding: 14px 20px; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; background: #fff; }
    .rem-table td { padding: 16px 20px; border-bottom: 1px solid #f0f1f4; font-size: 14px; color: #374151; vertical-align: middle; }

    /* Empty state */
    .empty-state {
        padding: 80px 30px;
        text-align: center;
        background: #f9fafb;
    }
    .empty-icon-wrap {
        width: 60px;
        height: 60px;
        margin: 0 auto 20px;
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .empty-icon-wrap::after {
        content: '';
        position: absolute;
        inset: -6px;
        border-radius: 20px;
        border: 1px dashed #e5e7eb;
        pointer-events: none;
    }
    .empty-icon-wrap svg {
        width: 26px;
        height: 26px;
        stroke: #9ca3af;
        stroke-width: 2;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .empty-title { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 6px; }
    .empty-subtitle { font-size: 13px; color: #9ca3af; }
</style>

<div class="rem-page-header">
    <div>
        <h1>Subscription Reminders</h1>
        <p>Log of automatic expiry and trial reminders sent to tenants.</p>
    </div>
    <div class="rem-header-actions">
        <a href="#" class="btn-outline">⚙ Reminder Settings</a>
        <a href="{{ url('/super/subscriptions/reminders') }}" class="btn-outline">↻ Refresh</a>
    </div>
</div>

<div class="rem-filter-card">
    <div class="search-wrap">
        <span>🔍</span>
        <input type="text" id="remSearch" placeholder="Search tenant or email...">
    </div>
    <select id="remType">
        <option value="">All types</option>
        <option value="expiry_reminder">Expiry reminder</option>
        <option value="trial_reminder">Trial reminder</option>
        <option value="expired_notice">Expired notice</option>
        <option value="plan_ended">Plan ended</option>
    </select>
    <select id="remStatus">
        <option value="">All statuses</option>
        <option value="sent">Sent</option>
        <option value="failed">Failed</option>
        <option value="pending">Pending</option>
    </select>
    <span class="rem-count">{{ $reminders->count() }} reminder{{ $reminders->count() === 1 ? '' : 's' }}</span>
</div>

<div class="rem-table-card">
    <table class="rem-table" id="remTable">
        <thead>
            <tr>
                <th>TENANT</th>
                <th>TYPE</th>
                <th>CHANNEL</th>
                <th>TIMING</th>
                <th>RECIPIENT</th>
                <th>STATUS</th>
                <th>SENT</th>
            </tr>
        </thead>
        <tbody>
            @if($reminders->count() > 0)
                @foreach($reminders as $rem)
                    <tr>
                        <td>{{ $rem->tenant_name }}</td>
                        <td>{{ $rem->type }}</td>
                        <td>{{ $rem->channel }}</td>
                        <td>{{ $rem->timing }}</td>
                        <td>{{ $rem->recipient }}</td>
                        <td>{{ $rem->status }}</td>
                        <td>{{ $rem->sent_at }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" style="padding: 0; border: none;">
                        <div class="empty-state">
                            <div class="empty-icon-wrap">
                                <!-- Bell with slash icon -->
                                <svg viewBox="0 0 24 24">
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                    <path d="M18.63 13A17.89 17.89 0 0 1 18 8"/>
                                    <path d="M6.26 6.26A5.86 5.86 0 0 0 6 8c0 7-3 9-3 9h14"/>
                                    <path d="M18 8a6 6 0 0 0-9.33-5"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </div>
                            <div class="empty-title">No reminds yet</div>
                            <div class="empty-subtitle">Reminders will appear here once the daily check sends them.</div>
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<script>
    // Live filters (used once reminders data is populated)
    document.getElementById('remSearch').addEventListener('keyup', filterReminders);
    document.getElementById('remType').addEventListener('change', filterReminders);
    document.getElementById('remStatus').addEventListener('change', filterReminders);

    function filterReminders() {
        const search = document.getElementById('remSearch').value.toLowerCase();
        const type = document.getElementById('remType').value.toLowerCase();
        const status = document.getElementById('remStatus').value.toLowerCase();
        const rows = document.querySelectorAll('#remTable tbody tr');

        rows.forEach(row => {
            if (row.cells.length < 7) return;
            const tenant = row.cells[0].innerText.toLowerCase();
            const rowType = row.cells[1].innerText.toLowerCase();
            const rowStatus = row.cells[5].innerText.toLowerCase();

            const matchSearch = tenant.includes(search);
            const matchType = !type || rowType.includes(type.replace('_', ' '));
            const matchStatus = !status || rowStatus.includes(status);

            row.style.display = (matchSearch && matchType && matchStatus) ? '' : 'none';
        });
    }
</script>

@endsection