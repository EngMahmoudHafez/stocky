<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank you — {{ $settings['app_name'] ?? 'Poraone' }}</title>
    @if(file_exists(public_path('images/super/settings/favicon.png')))
        <link rel="icon" href="{{ asset('images/super/settings/favicon.png') }}" type="image/png">
    @endif
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f9fafb; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #fff; padding: 50px 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); text-align: center; max-width: 480px; }
        .icon { font-size: 60px; margin-bottom: 20px; }
        h1 { color: #111827; margin-bottom: 12px; font-size: 26px; }
        p { color: #6b7280; line-height: 1.7; margin-bottom: 24px; font-size: 14px; }
        a { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 14px; }
        .footer { margin-top: 30px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🎉</div>
        <h1>Thank you for signing up!</h1>
        <p>Your workspace request has been received. Our team will review it and provision your account shortly. You will receive an email once your workspace is ready.</p>
        <a href="/">Back to homepage</a>
        <div class="footer">© {{ date('Y') }} {{ $settings['app_name'] ?? 'Poraone' }}. All rights reserved.</div>
    </div>
</body>
</html>