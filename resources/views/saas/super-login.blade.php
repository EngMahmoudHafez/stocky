<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin Login â {{ $settings['app_name'] ?? 'Poraone' }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
            min-height: 100vh;
            display: flex;
        }

        /* ==========================================================
           LEFT SIDE â Green Branding Panel
           ========================================================== */
        .login-left {
            flex: 0 0 55%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 80px;
            color: #fff;
        }

        /* Decorative circles */
        .login-left::before,
        .login-left::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
        }

        .login-left::before {
            width: 500px;
            height: 500px;
            top: -200px;
            left: -200px;
        }

        .login-left::after {
            width: 400px;
            height: 400px;
            bottom: -150px;
            right: -100px;
            background: rgba(255, 255, 255, 0.05);
        }

        .login-left-inner {
            max-width: 520px;
            position: relative;
            z-index: 2;
        }

        .shield-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 32px;
        }

        .login-left h1 {
            font-size: 40px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1px;
            margin-bottom: 20px;
        }

        .login-left p {
            font-size: 15px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 40px;
        }

        .login-left-features {
            display: flex;
            gap: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .feature-item .feature-value {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .feature-item .feature-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.75);
        }

        /* ==========================================================
           RIGHT SIDE â Login Form
           ========================================================== */
        .login-right {
            flex: 1;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
        }

        .login-form-wrap {
            width: 100%;
            max-width: 400px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        /* NEW: Style for the image logo */
        .brand-logo-img {
            max-height: 40px;
            border-radius: 8px;
        }

        .brand-text {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.4px;
        }

        .login-form-wrap h2 {
            font-size: 28px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.7px;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        /* Demo hint */
        .demo-hint {
            background: #ecfdf5;
            border: 2px dashed #10b981;
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            margin-bottom: 24px;
            transition: all .15s ease;
        }

        .demo-hint:hover {
            background: #d1fae5;
        }

        .demo-badge {
            background: #10b981;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 6px;
            letter-spacing: 0.5px;
        }

        .demo-text {
            flex: 1;
            font-size: 12px;
            color: #065f46;
            line-height: 1.4;
        }

        .demo-text strong {
            display: block;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .demo-arrow {
            color: #10b981;
            font-size: 18px;
            font-weight: 700;
        }

        /* Form fields */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: all .15s ease;
            font-family: inherit;
        }

        .form-input:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 16px;
            padding: 0;
        }

        /* Remember / Forgot */
        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .remember-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #6b7280;
        }

        .remember-wrap input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #10b981;
            cursor: pointer;
        }

        .forgot-link {
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link:hover {
            color: #059669;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);
            transition: all .15s ease;
            font-family: inherit;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(16, 185, 129, 0.35);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 24px 0;
            color: #9ca3af;
            font-size: 12px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        /* Back button */
        .btn-back {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all .15s ease;
            font-family: inherit;
        }

        .btn-back:hover {
            background: #f9fafb;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-top: 40px;
        }

        /* Errors */
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-left { display: none; }
            .login-right { flex: 1; }
        }
    </style>
</head>
<body>

    <!-- LEFT: Green branding -->
    <div class="login-left">
        <div class="login-left-inner">
            <div class="shield-icon">ð¡ï¸</div>
            <h1>Manage your entire platform from one place</h1>
            <p>Access tenant management, billing, subscriptions, and analytics through the super admin dashboard.</p>
            
            <div class="login-left-features">
                <div class="feature-item">
                    <span class="feature-value">100%</span>
                    <span class="feature-label">Secure</span>
                </div>
                <div class="feature-item">
                    <span class="feature-value">24/7</span>
                    <span class="feature-label">Access</span>
                </div>
                <div class="feature-item">
                    <span class="feature-value">Real-time</span>
                    <span class="feature-label">Analytics</span>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Login form -->
    <div class="login-right">
        <div class="login-form-wrap">
            
            <!-- Brand: NOW DYNAMIC -->
            <div class="brand">
                @if(file_exists(public_path('images/super/settings/logo.png')))
                    <img src="{{ asset('images/super/settings/logo.png') }}" alt="Logo" class="brand-logo-img">
                @else
                    <div class="brand-icon">{{ substr($settings['app_name'] ?? 'P', 0, 1) }}</div>
                @endif
                <div class="brand-text">{{ $settings['app_name'] ?? 'Poraone' }}</div>
            </div>

            <h2>Welcome back</h2>
            <p class="login-subtitle">Sign in to the super admin dashboard to manage your platform.</p>

            <!-- Errors -->
            @if($errors->any())
                <div class="error-box">{{ $errors->first() }}</div>
            @endif

            <!-- Form -->
            <form method="POST" action="/super/login">
                @csrf

                <div class="form-group">
                    <label class="form-label">Email address</label>
                    <div class="input-wrap">
                        <span class="input-icon">â</span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-input" 
                            placeholder="Enter Your Email" 
                            value="{{ old('email') }}"
                            required 
                            autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">ð</span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-input" 
                            placeholder="Enter your password" 
                            required>
                        <button type="button" class="password-toggle" onclick="togglePassword()" title="Show/hide password">ð</button>
                    </div>
                </div>

                <div class="form-row">
                    <label class="remember-wrap">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-submit">
                    Sign In â
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">OR</div>

            <!-- Back to homepage -->
            <a href="/" class="btn-back">
                â Back to homepage
            </a>

            <!-- Footer: NOW DYNAMIC -->
            <div class="login-footer">
                Â© {{ date('Y') }} {{ $settings['company_name'] ?? 'Poraone' }}. All rights reserved.
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        function autofillDemo() {
            document.getElementById('email').value = 'admin@example.com';
            document.getElementById('password').value = '123456';
        }
    </script>

</body>
</html>