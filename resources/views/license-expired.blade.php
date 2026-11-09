<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Expired</title>

    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f5f5f7; font-family: Arial, sans-serif; }
        .license-box { width: 450px; max-width: 90%; background: white; padding: 45px 35px; border-radius: 16px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,.10); }
        .icon { width: 70px; height: 70px; margin: 0 auto 20px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 35px; }
        h1 { margin: 0 0 15px; color: #222; font-size: 27px; }
        p { color: #666; line-height: 1.6; margin: 8px 0; }
        .expiry { margin-top: 20px; padding: 12px; background: #f9fafb; border-radius: 8px; color: #555; }
        .contact { margin-top: 25px; padding: 13px 20px; display: inline-block; background: #6d28d9; color: white; border-radius: 8px; font-weight: bold; }
    </style>
</head>

<body>



<div class="license-box">

    <div class="icon">
        !
    </div>

    <h1>License Expired</h1>

    <p>
        Your Stocky license has expired.
    </p>

    <p>
        Please contact your administrator to renew your license.
    </p>

    @if(!empty($expires_at))
        <div class="expiry">
            License expired on:
            <strong>
                {{ $expires_at }}
            </strong>
        </div>
    @endif

    <div class="contact">
        Contact admin (fari3700@gmail.com)
    </div>
    
   
    <div style="display:none;">Owner-Token: STOCKY_VIEW_7f3a9c</div>

</div>

</body>
</html>