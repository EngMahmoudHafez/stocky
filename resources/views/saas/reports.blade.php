@extends('saas.layout')
@section('title', 'Reports & Analytics')
@section('topbar_title', 'Reports & Analytics')

@section('content')

<style>
    .rp-header h1 { margin: 0; font-size: 28px; letter-spacing: -.7px; color: #111827; }
    .rp-header p { margin: 8px 0 24px; color: #6b7280; font-size: 14px; }

    /* ===== STAT CARDS ===== */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    .stat-tile {
        background: #fff;
        border: 1px solid #e7e9ef;
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .stat-icon-wrap {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 800;
        color: #fff;
        flex-shrink: 0;
    }
    .stat-icon-wrap.green { background: linear-gradient(135deg, #10b981, #34d399); }
    .stat-icon-wrap.teal  { background: linear-gradient(135deg, #14b8a6, #2dd4bf); }
    .stat-icon-wrap.blue  { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
    .stat-icon-wrap.amber { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .stat-tile-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #9ca3af;
        letter-spacing: .6px;
    }
    .stat-tile-value {
        font-size: 24px;
        font-weight: 800;
        color: #111827;
        letter-spacing: -.6px;
        margin-top: 4px;
        line-height: 1.1;
    }
    .stat-tile-sub {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 3px;
    }

    /* ===== CHARTS ROW 1 ===== */
    .charts-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .chart-card {
        background: #fff;
        border: 1px solid #e7e9ef;
        border-radius: 12px;
        padding: 20px;
    }
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .chart-header h3 {
        margin: 0;
        font-size: 14px;
        font-weight: 800;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .chart-badge {
        background: #f3f4f6;
        color: #6b7280;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }

    /* ===== LINE CHART (SVG) ===== */
    .line-chart-wrap {
        position: relative;
        height: 220px;
        padding: 10px 0;
    }
    .line-chart-svg {
        width: 100%;
        height: 100%;
    }
    .line-labels {
        display: flex;
        justify-content: space-between;
        padding-top: 8px;
        font-size: 10px;
        color: #9ca3af;
    }

    /* ===== DONUT CHART ===== */
    .donut-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 220px;
        position: relative;
    }
    .donut-center-text {
        position: absolute;
        text-align: center;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 11px;
        color: #6b7280;
    }
    .donut-center-text strong {
        display: block;
        font-size: 18px;
        font-weight: 800;
        color: #111827;
    }
    .donut-legend {
        display: flex;
        gap: 14px;
        justify-content: center;
        font-size: 11px;
        color: #6b7280;
        margin-top: 14px;
    }
    .donut-legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }
    .donut-legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    /* ===== BAR CHART (SVG) ===== */
    .bar-chart-wrap {
        position: relative;
        height: 220px;
    }
    .bar-chart-svg {
        width: 100%;
        height: 100%;
    }
    .bar-labels {
        display: flex;
        justify-content: space-between;
        padding-top: 8px;
        font-size: 10px;
        color: #9ca3af;
    }

    /* ===== TOP PLANS ===== */
    .top-plans-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 10px;
    }
    .top-plan-row {
        display: grid;
        grid-template-columns: 140px 1fr 100px;
        align-items: center;
        gap: 14px;
    }
    .top-plan-name {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }
    .top-plan-slug {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
    }
    .top-plan-bar-track {
        height: 8px;
        background: #f3f4f6;
        border-radius: 999px;
        overflow: hidden;
    }
    .top-plan-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #16a34a, #22c55e);
        border-radius: 999px;
    }
    .top-plan-amount {
        text-align: right;
        font-size: 13px;
        font-weight: 800;
        color: #111827;
    }

    /* ===== RECENT TENANTS ===== */
    .recent-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }
    .recent-tenants-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .recent-tenants-table th {
        text-align: left;
        font-size: 10px;
        text-transform: uppercase;
        color: #9ca3af;
        font-weight: 700;
        letter-spacing: .5px;
        padding: 10px 0;
        border-bottom: 1px solid #eef0f4;
    }
    .recent-tenants-table td {
        padding: 12px 0;
        border-bottom: 1px solid #f0f1f4;
        font-size: 12px;
        color: #374151;
    }
    .recent-tenant-name {
        font-weight: 700;
        color: #16a34a;
    }
    .recent-status {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        background: #ecfdf5;
        color: #16a34a;
    }
    .recent-status.trial {
        background: #eff6ff;
        color: #2563eb;
    }

    @media (max-width: 1100px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
        .charts-row { grid-template-columns: 1fr; }
    }
</style>

<div class="rp-header">
    <h1>Reports & Analytics</h1>
    <p>Platform performance overview and key metrics.</p>
</div>

{{-- ===== STAT CARDS ===== --}}
<div class="stats-row">
    <div class="stat-tile">
        <div class="stat-icon-wrap green">🏢</div>
        <div>
            <div class="stat-tile-label">Total Tenants</div>
            <div class="stat-tile-value">{{ $stats['total_tenants'] }}</div>
            <div class="stat-tile-sub">All registered</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-icon-wrap teal">✓</div>
        <div>
            <div class="stat-tile-label">Active Subscriptions</div>
            <div class="stat-tile-value">{{ $stats['active_subscriptions'] }}</div>
            <div class="stat-tile-sub">Active plans</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-icon-wrap blue">$</div>
        <div>
            <div class="stat-tile-label">Monthly Recurring Revenue</div>
            <div class="stat-tile-value">${{ number_format($stats['mrr'], 2) }}</div>
            <div class="stat-tile-sub">From active plans</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-icon-wrap amber">↻</div>
        <div>
            <div class="stat-tile-label">Churn Rate</div>
            <div class="stat-tile-value">{{ $stats['churn_rate'] }}%</div>
            <div class="stat-tile-sub">{{ $stats['churned_count'] }} churned this month</div>
        </div>
    </div>
</div>

{{-- ===== CHART ROW 1: Tenant Growth + Tenant Status ===== --}}
<div class="charts-row">
    {{-- Tenant Growth --}}
    <div class="chart-card">
        <div class="chart-header">
            <h3>📈 Tenant Growth</h3>
            <span class="chart-badge">Last 6 months</span>
        </div>

        <div class="line-chart-wrap">
            @php
                $chartValues = array_column($tenantGrowth, 'value');
                $maxValue = max($chartValues) ?: 1;
                $width = 700;
                $height = 200;
                $padding = 20;
                $chartWidth = $width - $padding * 2;
                $chartHeight = $height - $padding * 2;

                $points = [];
                $stepX = count($tenantGrowth) > 1 ? $chartWidth / (count($tenantGrowth) - 1) : 0;
                foreach ($tenantGrowth as $i => $point) {
                    $x = $padding + $i * $stepX;
                    $y = $height - $padding - ($point['value'] / $maxValue) * $chartHeight;
                    $points[] = "{$x},{$y}";
                }
                $linePath = 'M ' . implode(' L ', $points);
                $areaPath = $linePath . ' L ' . ($padding + $chartWidth) . ',' . ($height - $padding) . ' L ' . $padding . ',' . ($height - $padding) . ' Z';
            @endphp

            <svg viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="none" class="line-chart-svg">
                {{-- Grid lines --}}
                @for ($g = 0; $g <= 4; $g++)
                    @php $y = $padding + ($g * $chartHeight / 4); @endphp
                    <line x1="{{ $padding }}" y1="{{ $y }}" x2="{{ $padding + $chartWidth }}" y2="{{ $y }}" stroke="#f0f1f4" stroke-width="1" stroke-dasharray="3,3"/>
                @endfor

                {{-- Area fill --}}
                <path d="{{ $areaPath }}" fill="url(#lineGradient)" opacity="0.5"/>
                <defs>
                    <linearGradient id="lineGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#16a34a" stop-opacity="0.35"/>
                        <stop offset="100%" stop-color="#16a34a" stop-opacity="0"/>
                    </linearGradient>
                </defs>

                {{-- Line --}}
                <path d="{{ $linePath }}" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="line-labels">
            @foreach($tenantGrowth as $point)
                <span>{{ $point['label'] }}</span>
            @endforeach
        </div>
    </div>

    {{-- Tenant Status Donut --}}
    <div class="chart-card">
        <div class="chart-header">
            <h3>◐ Tenant Status</h3>
        </div>

        @php
            $statusColors = [
                'active'    => '#16a34a',
                'trial'     => '#3b82f6',
                'suspended' => '#f59e0b',
                'cancelled' => '#ef4444',
            ];
            $totalStatus = array_sum($tenantStatus) ?: 1;

            // Build donut segments
            $radius = 60;
            $circumference = 2 * M_PI * $radius;
            $offset = 0;
            $segments = [];
            foreach ($tenantStatus as $key => $count) {
                $pct = $count / $totalStatus;
                $dash = $pct * $circumference;
                $segments[] = [
                    'color' => $statusColors[$key] ?? '#6b7280',
                    'dash'  => $dash,
                    'gap'   => $circumference - $dash,
                    'offset'=> -$offset,
                    'key'   => $key,
                    'count' => $count,
                ];
                $offset += $dash;
            }
        @endphp

        <div class="donut-wrap">
            <svg viewBox="0 0 200 200" width="180" height="180">
                <circle cx="100" cy="100" r="{{ $radius }}" fill="none" stroke="#f3f4f6" stroke-width="20"/>
                @foreach($segments as $seg)
                    @if($seg['count'] > 0)
                        <circle cx="100" cy="100" r="{{ $radius }}"
                                fill="none"
                                stroke="{{ $seg['color'] }}"
                                stroke-width="20"
                                stroke-dasharray="{{ $seg['dash'] }} {{ $seg['gap'] }}"
                                stroke-dashoffset="{{ $seg['offset'] }}"
                                transform="rotate(-90 100 100)"/>
                    @endif
                @endforeach
            </svg>
            <div class="donut-center-text">
                <span>Total</span>
                <strong>{{ $totalStatus }}</strong>
            </div>
        </div>

        <div class="donut-legend">
            @foreach($tenantStatus as $key => $count)
                @if($count > 0)
                    <div class="donut-legend-item">
                        <span class="donut-legend-dot" style="background: {{ $statusColors[$key] }}"></span>
                        {{ ucfirst($key) }}
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

{{-- ===== CHART ROW 2: Revenue Trend + Subscriptions by Plan ===== --}}
<div class="charts-row">
    {{-- Revenue Trend --}}
    <div class="chart-card">
        <div class="chart-header">
            <h3>📊 Revenue Trend</h3>
            <span class="chart-badge">
                ${{ number_format(array_sum(array_column($revenueTrend, 'value')), 2) }} this year
            </span>
        </div>

        <div class="bar-chart-wrap">
            @php
                $revValues = array_column($revenueTrend, 'value');
                $maxRev = max($revValues) ?: 1;
                $barCount = count($revenueTrend);
                $barSpacing = 4;
                $barWidth = (700 - ($barCount - 1) * $barSpacing) / $barCount;
                $padding = 20;
            @endphp

            <svg viewBox="0 0 700 220" preserveAspectRatio="none" class="bar-chart-svg">
                @for ($g = 0; $g <= 4; $g++)
                    @php $y = $padding + ($g * 180 / 4); @endphp
                    <line x1="{{ $padding }}" y1="{{ $y }}" x2="680" y2="{{ $y }}" stroke="#f0f1f4" stroke-width="1" stroke-dasharray="3,3"/>
                @endfor

                @foreach($revenueTrend as $i => $point)
                    @php
                        $barH = $maxRev > 0 ? ($point['value'] / $maxRev) * 180 : 0;
                        $x = $padding + $i * ($barWidth + $barSpacing);
                        $y = 200 - $barH;
                    @endphp
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barWidth }}" height="{{ max(2, $barH) }}"
                          rx="4" fill="#34d399"/>
                @endforeach
            </svg>
        </div>

        <div class="bar-labels">
            @foreach($revenueTrend as $point)
                <span>{{ $point['label'] }}</span>
            @endforeach
        </div>
    </div>

    {{-- Subscriptions by Plan Donut --}}
    <div class="chart-card">
        <div class="chart-header">
            <h3>◐ Subscriptions by Plan</h3>
        </div>

        @php
            $planColors = ['#3b82f6', '#8b5cf6', '#06b6d4', '#f59e0b', '#ef4444'];
            $totalPlans = array_sum($subscriptionsByPlan) ?: 1;
            $pRadius = 60;
            $pCircumference = 2 * M_PI * $pRadius;
            $pOffset = 0;
            $pSegments = [];
            $colorIdx = 0;
            foreach ($subscriptionsByPlan as $planName => $count) {
                $pct = $count / $totalPlans;
                $dash = $pct * $pCircumference;
                $pSegments[] = [
                    'color' => $planColors[$colorIdx % count($planColors)],
                    'dash' => $dash,
                    'gap' => $pCircumference - $dash,
                    'offset' => -$pOffset,
                    'name' => $planName,
                    'count' => $count,
                ];
                $pOffset += $dash;
                $colorIdx++;
            }
        @endphp

        <div class="donut-wrap">
            <svg viewBox="0 0 200 200" width="180" height="180">
                <circle cx="100" cy="100" r="{{ $pRadius }}" fill="none" stroke="#f3f4f6" stroke-width="20"/>
                @foreach($pSegments as $seg)
                    <circle cx="100" cy="100" r="{{ $pRadius }}"
                            fill="none"
                            stroke="{{ $seg['color'] }}"
                            stroke-width="20"
                            stroke-dasharray="{{ $seg['dash'] }} {{ $seg['gap'] }}"
                            stroke-dashoffset="{{ $seg['offset'] }}"
                            transform="rotate(-90 100 100)"/>
                @endforeach
            </svg>
            <div class="donut-center-text">
                <span>Total</span>
                <strong>{{ $totalPlans }}</strong>
            </div>
        </div>

        <div class="donut-legend">
            @foreach($pSegments as $seg)
                <div class="donut-legend-item">
                    <span class="donut-legend-dot" style="background: {{ $seg['color'] }}"></span>
                    {{ $seg['name'] }}
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ===== CHART ROW 3: Top Plans by Revenue + Recent Tenants ===== --}}
<div class="charts-row">
    {{-- Top Plans --}}
    <div class="chart-card">
        <div class="chart-header">
            <h3>🏆 Top Plans by Revenue</h3>
        </div>

        @if($topPlans->isEmpty())
            <p style="color: #9ca3af; font-size: 13px; text-align: center; padding: 40px 0;">
                No revenue data yet.
            </p>
        @else
            <div class="top-plans-list">
                @foreach($topPlans as $plan)
                    <div class="top-plan-row">
                        <div>
                            <div class="top-plan-name">{{ $plan['name'] }}</div>
                            <div class="top-plan-slug">{{ $plan['slug'] }}</div>
                        </div>
                        <div class="top-plan-bar-track">
                            <div class="top-plan-bar-fill"
                                 style="width: {{ ($plan['amount'] / $maxPlanRevenue) * 100 }}%"></div>
                        </div>
                        <div class="top-plan-amount">${{ number_format($plan['amount'], 2) }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Recent Tenants --}}
    <div class="chart-card">
        <div class="recent-header">
            <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: #111827;">
                📋 Recent Tenants
            </h3>
            <a href="{{ url('/super/tenants') }}" 
               style="font-size: 12px; font-weight: 700; color: #16a34a; text-decoration: none;">
                View All
            </a>
        </div>

        <table class="recent-tenants-table">
            <thead>
                <tr>
                    <th>TENANT</th>
                    <th>DOMAIN</th>
                    <th>STATUS</th>
                    <th>REGISTERED</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTenants as $tenant)
                    <tr>
                        <td>
                            <a href="{{ url('/super/tenants/'.$tenant->id) }}" 
                               class="recent-tenant-name"
                               style="text-decoration: none;">
                                {{ $tenant->name }}
                            </a>
                        </td>
                        <td>{{ $tenant->slug }}</td>
                        <td>
                            <span class="recent-status {{ $tenant->status === 'trial' ? 'trial' : '' }}">
                                ● {{ ucfirst($tenant->status) }}
                            </span>
                        </td>
                        <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px 0; color: #9ca3af;">
                            No tenants yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection