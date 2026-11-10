<?php

namespace App\Http\Middleware;

use App\Models\Language;
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

        if (! in_array($request->session()->get('locale') ?: $request->cookie('locale'), Language::SUPPORTED_LOCALES, true)
            && in_array($settings->language, Language::SUPPORTED_LOCALES, true)) {
            app()->setLocale($settings->language);
        }

        return $next($request);
    }
}
