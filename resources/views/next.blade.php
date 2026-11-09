@php
    $manifestPath = public_path('js/.vite/manifest.json');
    $entry = null;
    $cssFiles = [];

    $appVersion = is_file(base_path('version.txt'))
        ? trim(file_get_contents(base_path('version.txt')))
        : '';

    if (is_file($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
        foreach ($manifest as $item) {
            if (!empty($item['isEntry'])) { $entry = $item; break; }
        }
        $cssFiles = $entry['css'] ?? [];
        if (!$cssFiles) {
            foreach ($manifest as $key => $item) {
                if (!empty($item['file']) && str_ends_with($item['file'], '.css')) {
                    $cssFiles[] = $item['file'];
                }
            }
        }
    }

    // Backward compatibility for already-built Next bundles. New bundles read
    // plan features from /api/get_user_auth, but the current deployed bundle
    // still reads this bootstrap value. Resolve it server-side, including custom
    // domains, so it can never default a tenant to all features accidentally.
    $tenantFeatures = [];
    $isSaasTenant = false;
    try {
        $tenant = \App\Helpers\TenantDetect::current();
        $isSaasTenant = (bool) ($tenant && $tenant->plan);
        if ($isSaasTenant) {
            $tenantFeatures = is_array($tenant->plan->features)
                ? $tenant->plan->features
                : (json_decode($tenant->plan->features ?? '[]', true) ?: []);
            // Old bundles interpret an empty feature array as "all access".
            // Use a harmless sentinel until the new entitlement code is built.
            if (empty($tenantFeatures)) $tenantFeatures = ['__NO_FEATURES__'];
            // Compatibility aliases for the currently deployed bundle. The new
            // bundle uses the canonical keys directly.
            $legacyFeatureAliases = [
                'meetings' => 'meeting',
                'service_maintenance' => 'service',
            ];
            foreach ($legacyFeatureAliases as $canonical => $legacy) {
                if (in_array($canonical, $tenantFeatures, true)) $tenantFeatures[] = $legacy;
            }
            $tenantFeatures = array_values(array_unique($tenantFeatures));
        }
    } catch (\Throwable $e) {
        // Fail closed for tenant bootstrap; central installs still use [] = all.
        $tenantFeatures = $isSaasTenant ? ['__NO_FEATURES__'] : [];
    }
@endphp
<!DOCTYPE html>
<html lang="{{ $app_settings->default_language ?? 'en' }}">
  <head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-version" content="{{ $appVersion }}">
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <link rel="icon" href="{{ asset('images/' . ($app_settings->favicon ?? 'favicon.ico')) }}">
    <link rel="stylesheet" href="/css/receipt-fonts.css">
    <title>{{ $app_settings->app_name ?? 'Stocky' }}</title>
    <script>window.__PAGE_TITLE_SUFFIX__ = @json($app_settings->page_title_suffix ?? '');</script>



    <script>
        // Compatibility bridge for pre-rebuild bundles. It is deliberately
        // server-generated and cleared before writing the current entitlement.
        window.localStorage.removeItem('tenant_features');
        window.localStorage.setItem('tenant_features', JSON.stringify(@json($tenantFeatures)));
        window.__TENANT_FEATURES__ = @json($tenantFeatures);
    </script>


    @foreach ($cssFiles as $css)
      <link rel="stylesheet" href="/js/{{ $css }}">
    @endforeach
  </head>
  <body>
    <noscript><strong>This page requires JavaScript.</strong></noscript>
    <div id="vue3-app">
      <style>
        .boot-loader {
          position: fixed; inset: 0; display: flex; flex-direction: column;
          align-items: center; justify-content: center; gap: 16px;
          background: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
          color: rgba(0, 0, 0, 0.45); font-size: 14px;
        }
        .boot-loader__spinner {
          width: 40px; height: 40px;
          border: 3px solid rgba(109, 40, 217, 0.15);
          border-top-color: #6d28d9; border-radius: 50%;
          animation: boot-spin 0.8s linear infinite;
        }
        @keyframes boot-spin { to { transform: rotate(360deg); } }
      </style>
      <div class="boot-loader">
        <div class="boot-loader__spinner"></div>
        <div>{{ $app_settings->app_name ?? 'Stocky' }}</div>
      </div>
    </div>

    <script src="/assets_setup/js/qrcode.js"></script>

    @if ($entry)
      <script type="module" src="/js/{{ $entry['file'] }}"></script>
    @else
      <p style="font-family: sans-serif; padding: 24px">
        Stocky Next assets are not built yet. Run <code>npm run vue3:build</code>.
      </p>
    @endif
  </body>
</html>
