<?php

namespace App\Http\Middleware;

use App\Models\StoreSetting;
use Closure;
use Illuminate\Http\Request;

class EnsureStoreEnabled
{
    /**
     * Abort with 404 if the online store is disabled in settings.
     */
    public function handle(Request $request, Closure $next)
    {
        $settings = StoreSetting::first();

        if (! $settings || ! $settings->enabled) {
            abort(404);
        }

        if ($request->routeIs('store.realestate.*') && $settings->theme !== 'real_estate') {
            abort(404);
        }

        if (! $request->session()->has('locale') && ! $request->cookie('locale')
            && in_array($settings->language, ['en', 'fr', 'es', 'ar'], true)) {
            app()->setLocale($settings->language);
        }

        return $next($request);
    }
}
