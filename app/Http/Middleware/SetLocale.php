<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $default = config('app.locale');
        $default = in_array($default, Language::SUPPORTED_LOCALES, true) ? $default : 'en';
        $locale = Session::get('locale')
            ?: $request->cookie('locale')
            ?: $default;

        if (! in_array($locale, Language::SUPPORTED_LOCALES, true)) {
            $locale = $default;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
