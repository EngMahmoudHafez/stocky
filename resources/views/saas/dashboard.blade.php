@extends('saas.layout')

@section('title', 'Dashboard')
@section('topbar_title', 'Dashboard')

@section('content')

@php
    $tenantCount = (int) ($tenants ?? 0);
    $activeCount = (int) ($active ?? 0);
    $planCount = (int) ($plans ?? 0);
    $subscriptionCount = (int) ($subscriptions ?? 0);
    
    // Get real data for graphs if available, fallback to defaults
    $tenantDates = $chartData['tenant_dates'] ?? ['2026-08-01', '2026-08-15', '2026-09-01', '2026-09-08'];
    $tenantTotals = $chartData['tenant_totals'] ?? [0, 1, 1, 2];

    $activeSubs = $chartData['active_subs'] ?? 2;
    $trialSubs = $chartData['trial_subs'] ?? 0;

    $planNames = $chartData['plan_names'] ?? ['Starter', 'Enterprise'];
    $planCounts = $chartData['plan_counts'] ?? [1, 1];
@endphp

<div class="page-heading">
    <div>
        <h1>Good afternoon, Super Admin</h1>
        <p>Welcome back! Here's an overview of your SaaS platform.</p>
    </div>
    <div style="color:#6b7280;font-size:13px;padding-top:8px;">
        {{ now()->format('D, d M Y') }}
    </div>
</div>

<!-- =========================================================
     STATISTICS
========================================================== -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon stat-icon-purple">◫</div>
            <span class="stat-label">Total Tenants</span>
        </div>
        <div class="stat-number">{{ number_format($tenantCount) }}</div>
        <a href="{{ url('/super/tenants') }}" class="stat-link">View all tenants <span>→</span></a>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon stat-icon-green">✓</div>
            <span class="stat-label">Active / Trial</span>
        </div>
        <div class="stat-card">
    <div class="stat-top">
        <div class="stat-icon" style="background:#fef2f2; color:#dc2626;">⏳</div>
        <div class="stat-label">Pending Registrations</div>
    </div>
    <div class="stat-number">{{ $pending ?? 0 }}</div>
    <div style="margin-top:8px;">
        <a href="{{ url('/super/tenants') }}" style="font-size:12px; color:#dc2626; font-weight:700;">Review pending →</a>
    </div>
</div>
        <div class="stat-number">{{ number_format($activeCount) }}</div>
        <a href="{{ url('/super/subscriptions') }}" class="stat-link">View subscriptions <span>→</span></a>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon stat-icon-blue">◇</div>
            <span class="stat-label">Active Plans</span>
        </div>
        <div class="stat-number">{{ number_format($planCount) }}</div>
        <a href="{{ url('/super/plans') }}" class="stat-link">Manage plans <span>→</span></a>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon stat-icon-orange">$</div>
            <span class="stat-label">Total Subscriptions</span>
        </div>
        <div class="stat-number">{{ number_format($subscriptionCount) }}</div>
        <a href="{{ url('/super/subscriptions') }}" class="stat-link">View subscriptions <span>→</span></a>
    </div>
</div>


<!-- =========================================================
     CHARTS ROW 1
========================================================== -->
<div class="chart-grid">
    
    <!-- LINE CHART -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <h2>Tenants created (last 6 months)</h2>
                <p>Last 6 months</p>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="tenantLineChart"></canvas>
        </div>
    </div>

    <!-- DONUT CHART -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <h2>Subscription status</h2>
            </div>
        </div>
        <div class="chart-body donut-center">
            <canvas id="subscriptionDonutChart"></canvas>
        </div>
    </div>

</div>


<!-- =========================================================
     CHARTS ROW 2
========================================================== -->
<div class="chart-grid">
    
    <!-- BAR CHART -->
    <div class="chart-card" style="grid-column: span 2;">
        <div class="chart-header">
            <div>
                <h2>Subscriptions by plan (top 5)</h2>
                <p>Total</p>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="planBarChart"></canvas>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <h2>Quick actions</h2>
            </div>
        </div>
        <div class="quick-actions">
            <a href="{{ url('/super/tenants') }}" class="quick-action">
                <div class="quick-icon purple">◫</div>
                <div class="quick-content">
                    <strong>Manage tenants</strong>
                    <span>Create and manage tenants</span>
                </div>
            </a>
            <a href="{{ url('/super/plans') }}" class="quick-action">
                <div class="quick-icon blue">＋</div>
                <div class="quick-content">
                    <strong>Create new plan</strong>
                    <span>Create a new subscription plan</span>
                </div>
            </a>
            <a href="{{ url('/super/subscriptions') }}" class="quick-action">
                <div class="quick-icon green">$</div>
                <div class="quick-content">
                    <strong>Subscriptions</strong>
                    <span>Manage active subscriptions</span>
                </div>
            </a>
           
        </div>
    </div>

</div>


<style>
    .chart-grid {
        display: grid;
        grid-template-columns: 1.4fr .6fr;
        gap: 22px;
        margin-bottom: 22px;
    }

    .chart-card {
        background: #fff;
        border: 1px solid #e7e9ef;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(17,24,39,.025);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #eef0f4;
        padding-bottom: 12px;
    }

    .chart-header h2 {
        margin: 0;
        color: #111827;
        font-size: 16px;
        font-weight: 750;
    }

    .chart-header p {
        margin: 5px 0 0;
        color: #8a93a3;
        font-size: 12px;
    }

    .chart-body {
        position: relative;
        height: 250px;
    }

    .donut-center {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 250px;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        padding-top: 10px;
    }

    .quick-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 20px 10px;
        border: 1px solid #f0f1f4;
        border-radius: 12px;
        transition: all .15s ease;
    }

    .quick-action:hover {
        border-color: #e0e7ff;
        background: #fafaff;
    }

    .quick-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        margin-bottom: 10px;
        font-size: 18px;
    }

    .quick-icon.purple { background: #eef2ff; color: #6366f1; }
    .quick-icon.blue { background: #eff6ff; color: #2563eb; }
    .quick-icon.green { background: #ecfdf5; color: #16a34a; }
    .quick-icon.orange { background: #fff7ed; color: #ea580c; }

    .quick-content strong {
        display: block;
        color: #1f2937;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 3px;
    }

    .quick-content span {
        display: block;
        color: #9ca3af;
        font-size: 11px;
    }

    @media (max-width: 1100px) {
        .chart-grid { grid-template-columns: 1fr; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Tenant Line Chart
        const ctxLine = document.getElementById('tenantLineChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: @json($tenantDates),
                datasets: [{
                    label: 'Tenants',
                    data: @json($tenantTotals),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false }, ticks: { color: '#9ca3af' } },
                    x: { grid: { display: false }, ticks: { color: '#9ca3af' } }
                }
            }
        });

        // 2. Subscription Donut Chart
        const ctxDonut = document.getElementById('subscriptionDonutChart').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Trial'],
                datasets: [{
                    data: [@json($activeSubs), @json($trialSubs)],
                    backgroundColor: ['#10b981', '#3b82f6'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, pointStyle: 'circle', padding: 20, color: '#6b7280' }
                    }
                }
            }
        });

        // 3. Plan Horizontal Bar Chart
        const ctxBar = document.getElementById('planBarChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: @json($planNames),
                datasets: [{
                    label: 'Subscriptions',
                    data: @json($planCounts),
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                    barThickness: 20
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, max: 1, grid: { display: false }, ticks: { color: '#9ca3af' } },
                    y: { grid: { display: false }, ticks: { color: '#111827', font: { weight: '600' } } }
                }
            }
        });

    });
</script>
@endsection