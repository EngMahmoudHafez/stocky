@extends('saas.layout')
@section('title', 'Pending Offline Payments')
@section('topbar_title', 'Pending Offline Payments')

@section('content')
<style>
    .pend-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
    .pend-header h1 { margin: 0; font-size: 26px; letter-spacing: -.5px; color: #111827; }
    .pend-header p { margin: 8px 0 0; color: #6b7280; font-size: 14px; }
    .btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 9px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 700; text-decoration: none; }
    .card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; overflow: hidden; }
    .card-header { padding: 18px 22px; border-bottom: 1px solid #eef0f4; display: flex; justify-content: space-between; align-items: center; }
    .card-header strong { color: #111827; font-size: 14px; }
    .card-header .count { color: #9ca3af; font-size: 12px; }
    .empty-state { padding: 80px 30px; text-align: center; }
    .empty-icon { width: 60px; height: 60px; margin: 0 auto 20px; background: #ecfdf5; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #16a34a; font-size: 26px; font-weight: 800; }
    .empty-title { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 6px; }
    .empty-sub { font-size: 13px; color: #9ca3af; }
</style>

<div class="pend-header">
    <div>
        <h1>Pending Offline Payments</h1>
        <p>Review and approve bank transfer payments from new signups.</p>
    </div>
    <a href="{{ url('/super/payments') }}" class="btn-back">← Back to Payments</a>
</div>

<div class="card">
    <div class="card-header">
        <strong>🏦 Awaiting Verification</strong>
        <span class="count">{{ $pending->count() }} registration{{ $pending->count() === 1 ? '' : 's' }}</span>
    </div>
    @if($pending->count())
        {{-- Populate as real data becomes available --}}
    @else
        <div class="empty-state">
            <div class="empty-icon">✓</div>
            <div class="empty-title">No pending payments</div>
            <div class="empty-sub">All offline payment proofs have been reviewed.</div>
        </div>
    @endif
</div>
@endsection