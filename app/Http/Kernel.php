<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        // \Modules\Store\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
        // \App\Http\Middleware\AddAuthHeader::class,
    ];

   protected $middleware = [
    \App\Http\Middleware\CheckForMaintenanceMode::class,
    \App\Http\Middleware\CheckInstalled::class,
    \App\Http\Middleware\CheckLicenseExpiry::class,
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    \App\Http\Middleware\TrimStrings::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    \App\Http\Middleware\SetSessionConfig::class,

    // SaaS tenant resolver
    \App\Http\Middleware\ResolveSaaSTenant::class,
];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
    \App\Http\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,

    // Start session FIRST
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,

    // SaaS tenant resolver MUST run after StartSession
    \App\Http\Middleware\ResolveSaaSTenant::class,

    \App\Http\Middleware\SetLocale::class,

    \App\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \Laravel\Passport\Http\Middleware\CreateFreshApiToken::class,

    \App\Http\Middleware\CheckLicenseExpiry::class,
],

        'store' => [
            'store.auth',
        ],

        'api' => [
            // 'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetPdfLocale::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'store.auth' => \App\Http\Middleware\StoreAuthenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'Is_Active' => \App\Http\Middleware\Is_Active::class,
        'store.data' => \App\Http\Middleware\StoreDataMiddleware::class,
        'setlocale' => \App\Http\Middleware\SetLocale::class,
        'XSS' => \App\Http\Middleware\XSS::class,
        'request.safety' => \App\Http\Middleware\RequestSafety::class,
        'store.enabled' => \App\Http\Middleware\EnsureStoreEnabled::class,
        'token.timeout' => \App\Http\Middleware\EnforceApiTokenTimeout::class,
        'pdf.locale' => \App\Http\Middleware\SetPdfLocale::class,
        'portal.auth' => \App\Http\Middleware\EnsurePortalAuth::class,

        // SaaS tenant middleware
        'saas.tenant' => \App\Http\Middleware\ResolveSaaSTenant::class,
    ];
}