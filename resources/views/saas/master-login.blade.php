@extends('saas.layout')

@section('title', 'Sign In')
@section('topbar_title', 'Sign In')

@section('content')
<div style="max-width: 420px; margin: 60px auto;">
    <div class="card" style="padding: 32px;">
        <h1 style="margin: 0 0 8px; font-size: 22px; font-weight: 800; color: #111827;">Sign In</h1>
        <p style="margin: 0 0 24px; color: #6b7280; font-size: 13px;">Enter your credentials to continue.</p>

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom: 16px;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ url()->current() }}">
            @csrf

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required autofocus
                       style="width: 100%; padding: 11px 14px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px;">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required
                       style="width: 100%; padding: 11px 14px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px;">
            </div>

            <button type="submit" class="btn-save" style="width: 100%; padding: 12px; margin-top: 8px;">
                Sign In
            </button>
        </form>
    </div>
</div>
@endsection