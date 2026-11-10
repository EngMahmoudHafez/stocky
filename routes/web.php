<?php

use App\Http\Controllers\Admin\StoreSettingsController as AdminStoreSettings;
use App\Http\Controllers\Api\Store\AccountPagesController;
use App\Http\Controllers\Api\Store\CheckoutController;
use App\Http\Controllers\Api\Store\CustomerLoyaltyController;
use App\Http\Controllers\Api\Store\CustomerReturnsController;
use App\Http\Controllers\Api\Store\CustomerReviewsController;
use App\Http\Controllers\Api\Store\CustomerWalletController;
use App\Http\Controllers\Api\Store\QuoteRequestsController;
use App\Http\Controllers\Api\Store\MessageController;
use App\Http\Controllers\Api\Store\MyOrdersApiController;
use App\Http\Controllers\Api\Store\NewsletterController;
use App\Http\Controllers\QuickBooksController;
use App\Http\Controllers\RealEstateStoreController;
use App\Http\Controllers\StoreAuthController;
use App\Http\Controllers\StoreFrontController;
use App\Http\Controllers\WishlistController;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use App\Http\Controllers\SaaS\SuperAdminController;
use App\Http\Controllers\SaaS\AccountantController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::domain('{tenant}.localhost')->get('/', function () {
    return redirect('/login');
})->where('tenant', '[a-zA-Z0-9-]+');


Route::get('password/find/{token}', 'PasswordResetController@find');

Route::get('manifest.webmanifest', function () {
    $appName = optional(\App\Models\Setting::first())->app_name ?: 'Stocky';
    $shortName = trim(explode('|', $appName)[0]);
    if ($shortName === '') $shortName = $appName;

    $manifest = [
        'name' => $appName,
        'short_name' => $shortName,
        'description' => $appName,
        'start_url' => '/',
        'scope' => '/',
        'display' => 'standalone',
        'orientation' => 'any',
        'background_color' => '#ffffff',
        'theme_color' => '#2f3640',
        'lang' => 'en',
        'dir' => 'ltr',
        'icons' => [
            ['src' => '/pwa_images/pwa-icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/pwa_images/pwa-icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/pwa_images/pwa-icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
            ['src' => '/pwa_images/pwa-icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ],
    ];

    return response()->json($manifest, 200, [
        'Content-Type' => 'application/manifest+json',
        'Cache-Control' => 'no-cache',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
})->name('pwa.manifest');

$installed = Storage::disk('public')->exists('installed');

// ------------------------------------------------------------------\\
// ONLINE STORE ROUTES
if ($installed === true) {
    Route::middleware(['web', 'request.safety', 'store.enabled'])->group(function () {
        Route::prefix('online_store')->group(function () {

            Route::get('/lang/{locale}', function ($locale) {
                session(['locale' => $locale]);
                Cookie::queue('locale', $locale, 60 * 24 * 365, '/');
                return back();
            })->whereIn('locale', \App\Models\Language::SUPPORTED_LOCALES)->name('lang.switch');

            Route::get('/', [StoreFrontController::class, 'index'])->name('store.index');
            Route::get('/shop', [StoreFrontController::class, 'shop'])->name('store.shop');
            Route::get('/flash-sales', [StoreFrontController::class, 'flashSales'])->name('store.flash_sales');
            Route::get('/contact', [StoreFrontController::class, 'contact'])->name('store.contact');
            Route::get('/products/{id}/reviews', [CustomerReviewsController::class, 'productReviews'])->name('store.product.reviews');
            Route::post('/quote-request', [QuoteRequestsController::class, 'store'])->middleware('throttle:8,1')->name('store.quote.request');
            Route::post('/store/orders', [CheckoutController::class, 'store'])->name('store.orders.store');
            Route::post('/store/payment-intent', [CheckoutController::class, 'createPaymentIntent'])->name('store.payment.intent');
            Route::post('/store/checkout-quote', [CheckoutController::class, 'quote'])->name('store.checkout.quote');
            Route::get('/store/paypal/return', [CheckoutController::class, 'paypalReturn'])->name('store.paypal.return');
            Route::get('/store/paypal/cancel', [CheckoutController::class, 'paypalCancel'])->name('store.paypal.cancel');
            Route::get('/store/paystack/return', [CheckoutController::class, 'paystackReturn'])->name('store.paystack.return');
            Route::get('/store/flutterwave/return', [CheckoutController::class, 'flutterwaveReturn'])->name('store.flutterwave.return');
            Route::get('/store/razorpay/return', [CheckoutController::class, 'razorpayReturn'])->name('store.razorpay.return');
            Route::get('/collections/{slug}', [StoreFrontController::class, 'collection'])->name('store.collection.show');
            Route::get('/product/{id}', [StoreFrontController::class, 'productShow'])->whereNumber('id')->name('store.product.show');
            Route::get('/compare', [StoreFrontController::class, 'compare'])->name('store.compare');
            Route::get('/pages/{slug}', [StoreFrontController::class, 'page'])->name('store.page');
            Route::get('/vehicle/options', [\App\Http\Controllers\StoreVehicleController::class, 'options'])->name('store.vehicle.options');
            Route::post('/vehicle', [\App\Http\Controllers\StoreVehicleController::class, 'select'])->name('store.vehicle.select');
            Route::post('/vehicle/clear', [\App\Http\Controllers\StoreVehicleController::class, 'clear'])->name('store.vehicle.clear');
            Route::post('/garage/{id}/select', [\App\Http\Controllers\StoreVehicleController::class, 'selectGarage'])->whereNumber('id')->name('store.garage.select');
            Route::delete('/garage/{id}', [\App\Http\Controllers\StoreVehicleController::class, 'destroyGarage'])->whereNumber('id')->name('store.garage.destroy');
            Route::get('/wishlist/ids', [WishlistController::class, 'ids'])->name('store.wishlist.ids');
            Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('store.wishlist.toggle');
            Route::post('/wishlist/{productId}/remove', [WishlistController::class, 'remove'])->whereNumber('productId')->name('store.wishlist.remove');
            Route::get('/properties', [RealEstateStoreController::class, 'listings'])->name('store.realestate.listings');
            Route::post('/properties/inquiry', [RealEstateStoreController::class, 'inquiry'])->name('store.realestate.inquiry');
            Route::get('/properties/{slug}', [RealEstateStoreController::class, 'show'])->name('store.realestate.show');
            Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
            Route::post('/contact/send', [MessageController::class, 'store'])->name('store.contact.send');
            Route::get('/search/suggestions', [StoreFrontController::class, 'searchSuggestions'])->name('store.search.suggestions');

            Route::middleware(['web', 'auth:store'])->group(function () {
                Route::view('/checkout', 'store.checkout')->name('checkout');
                Route::view('/thank-you', 'store.thank-you')->name('store.thankyou');
                Route::get('/account', [AccountPagesController::class, 'account'])->name('account');
                Route::put('/account', [AccountPagesController::class, 'update'])->name('account.update');
                Route::put('/account/address', [AccountPagesController::class, 'updateAddress'])->name('account.address.update');
                Route::get('/account/wishlist', [StoreFrontController::class, 'wishlist'])->name('store.wishlist');
                Route::get('/account/orders', [AccountPagesController::class, 'orders'])->name('account.orders');
                Route::get('/account/orders/{id}', function ($id) {
                    return view('store.order-show', ['id' => $id]);
                })->name('account.order.show');
                Route::get('/account/orders/{id}/invoice', [CheckoutController::class, 'invoice'])->name('account.order.invoice');
                Route::get('/account/orders/{id}/return-eligibility', [CustomerReturnsController::class, 'eligibility'])->name('account.order.return.eligibility');
                Route::post('/account/orders/{id}/cancel', [CustomerReturnsController::class, 'cancel'])->name('account.order.cancel');
                Route::post('/account/orders/{id}/return', [CustomerReturnsController::class, 'requestReturn'])->name('account.order.return');
                Route::get('/account/orders/{id}/reviewable', [CustomerReviewsController::class, 'reviewable'])->name('account.order.reviewable');
                Route::post('/account/orders/{id}/review', [CustomerReviewsController::class, 'submit'])->name('account.order.review');
                Route::get('/my/orders', [MyOrdersApiController::class, 'index'])->name('my_orders.index');
                Route::get('/my/orders/{id}', [MyOrdersApiController::class, 'show'])->name('my_orders.show');
                Route::view('/account/returns', 'store.account-returns')->name('account.returns');
                Route::get('/my/returns', [CustomerReturnsController::class, 'myReturns'])->name('my_returns.index');
                Route::view('/account/wallet', 'store.account-wallet')->name('account.wallet');
                Route::get('/my/wallet', [CustomerWalletController::class, 'summary'])->name('my_wallet.summary');
                Route::get('/my/wallet/transactions', [CustomerWalletController::class, 'transactions'])->name('my_wallet.transactions');
                Route::post('/my/wallet/redeem', [CustomerWalletController::class, 'redeem'])->name('my_wallet.redeem');
                Route::post('/my/wallet/withdraw', [CustomerWalletController::class, 'withdraw'])->name('my_wallet.withdraw');
                Route::view('/account/rewards', 'store.account-loyalty')->name('account.rewards');
                Route::get('/my/loyalty', [CustomerLoyaltyController::class, 'summary'])->name('my_loyalty.summary');
                Route::post('/my/loyalty/redeem', [CustomerLoyaltyController::class, 'redeem'])->name('my_loyalty.redeem');
                Route::get('/my/loyalty/redemptions', [CustomerLoyaltyController::class, 'redemptions'])->name('my_loyalty.redemptions');
            });

            Route::middleware('guest:store')->group(function () {
                Route::get('/login', [StoreAuthController::class, 'showLogin'])->name('store.login.show');
                Route::post('/login', [StoreAuthController::class, 'login'])->name('store.login');
                Route::get('/register', [StoreAuthController::class, 'showRegister'])->name('store.register.show');
                Route::post('/register', [StoreAuthController::class, 'register'])->name('store.register');
                Route::get('/forgot-password', [StoreAuthController::class, 'showForgotPassword'])->name('store.password.request');
                Route::post('/forgot-password', [StoreAuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('store.password.email');
                Route::get('/reset-password/{token}', [StoreAuthController::class, 'showResetForm'])->name('store.password.reset');
                Route::post('/reset-password', [StoreAuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('store.password.update');
            });

            Route::get('/email/verify/{id}/{hash}', [StoreAuthController::class, 'verifyEmail'])->middleware('throttle:12,1')->name('store.verification.verify');
            Route::post('/email/resend', [StoreAuthController::class, 'resendVerification'])->middleware('throttle:4,1')->name('store.verification.resend');
            Route::post('/logout', [StoreAuthController::class, 'logout'])->middleware('auth:store')->name('store.logout');
        });
    });
} else {
    Route::any('/online_store/{any?}', function () {
        return redirect('/setup');
    })->where('any', '.*');
}

// ------------------------------------------------------------------\\

$installed = Storage::disk('public')->exists('installed');

if ($installed === false) {
    Route::get('/setup', ['uses' => 'SetupController@viewCheck'])->name('setup');
    Route::get('/setup/step-1', ['uses' => 'SetupController@viewStep1']);
    Route::post('/setup/step-2', ['as' => 'setupStep1', 'uses' => 'SetupController@setupStep1']);
    Route::post('/setup/testDB', ['as' => 'testDB', 'uses' => 'TestDbController@testDB']);
    Route::get('/setup/step-2', ['uses' => 'SetupController@viewStep2']);
    Route::get('/setup/step-3', ['uses' => 'SetupController@viewStep3']);
    Route::get('/setup/finish', function () { return view('setup.finishedSetup'); });
    Route::get('/setup/getNewAppKey', ['as' => 'getNewAppKey', 'uses' => 'SetupController@getNewAppKey']);
    Route::get('/setup/getPassport', ['as' => 'getPassport', 'uses' => 'SetupController@getPassport']);
    Route::get('/setup/getMegrate', ['as' => 'getMegrate', 'uses' => 'SetupController@getMegrate']);
    Route::post('/setup/step-3', ['as' => 'setupStep2', 'uses' => 'SetupController@setupStep2']);
    Route::post('/setup/step-4', ['as' => 'setupStep3', 'uses' => 'SetupController@setupStep3']);
    Route::post('/setup/step-5', ['as' => 'setupStep4', 'uses' => 'SetupController@setupStep4']);
    Route::post('/setup/lastStep', ['as' => 'lastStep', 'uses' => 'SetupController@lastStep']);
    Route::get('setup/lastStep', function () { return redirect('/setup', 301); });
} else {
    Route::any('/setup/{vue}', function () { abort(403); });
}

Route::group(['middleware' => ['web', 'auth:web', 'Is_Active']], function () {
    Route::view('/next/{any?}', 'next')->where('any', '.*')->name('next');
    Route::redirect('/dashboard-next', '/next/dashboard');
    Route::get('/quickbooks/connect', [QuickBooksController::class, 'connect'])->name('quickbooks.connect');
    Route::get('/quickbooks/callback', [QuickBooksController::class, 'callback'])->name('quickbooks.callback');
    Route::get('/google-calendar/connect', [\App\Http\Controllers\GoogleCalendarConnectController::class, 'connect'])->name('google_calendar.connect');
    Route::get('/google-calendar/callback', [\App\Http\Controllers\GoogleCalendarConnectController::class, 'callback'])->name('google_calendar.callback');
    Route::get('/google-calendar/disconnect', [\App\Http\Controllers\GoogleCalendarConnectController::class, 'disconnect'])->name('google_calendar.disconnect');
});

Route::get('/portal/{vue?}', function (\Illuminate\Http\Request $request, $vue = null) {
    $installed = Storage::disk('public')->exists('installed');
    if ($installed === false) { return redirect('/setup'); }
    $guestSegments = ['login', 'set-password'];
    $isGuestPath = in_array($vue, $guestSegments, true);
    if (! \Illuminate\Support\Facades\Auth::guard('portal')->check() && ! $isGuestPath) {
        return redirect('/portal/login');
    }
    return view('portal');
})->where('vue', '.*')->middleware(['web']);

Route::get('csrf-token', function (\Illuminate\Http\Request $request) {
    return response()->json(['token' => csrf_token()])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
})->middleware('web')->name('csrf.token');

Route::get('sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('store.sitemap');
Route::get('robots.txt', [\App\Http\Controllers\SitemapController::class, 'robots'])->name('store.robots');

// ==================================================================
// PUBLIC LANDING PAGE
// ==================================================================
Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('landing');
// ==================================================================
// PUBLIC REGISTRATION (Customer Sign-up)
// ==================================================================
Route::get('/register', [\App\Http\Controllers\SaaS\RegisterController::class, 'show'])->name('register');
Route::post('/register', [\App\Http\Controllers\SaaS\RegisterController::class, 'store'])->name('register.store');
Route::get('/register/thank-you', [\App\Http\Controllers\SaaS\RegisterController::class, 'thankYou'])->name('register.thank-you');
// ==================================================================
// SAAS SUPER ADMIN + ACCOUNTANT ROUTES
// ==================================================================

Route::prefix('super')->middleware('web')->group(function () {

    // ---- AUTH ----
    Route::get('/login', [SuperAdminController::class, 'login'])->name('saas.super.login');
    Route::post('/login', [SuperAdminController::class, 'authenticate'])->middleware('throttle:5,1')->name('saas.super.authenticate');
    Route::post('/logout', [SuperAdminController::class, 'logout'])->name('saas.super.logout');

    // ---- DASHBOARD ----
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('saas.super.dashboard');
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('saas.super.dashboard.page');

    // ---- TENANTS ----
    Route::get('/tenants/create', [SuperAdminController::class, 'createTenantForm'])->name('saas.super.tenants.create.form');
    Route::post('/tenants', [SuperAdminController::class, 'createTenant'])->name('saas.super.tenants.create');
    Route::get('/tenants/{tenant}/edit', [SuperAdminController::class, 'editTenant'])->name('saas.super.tenants.edit');
    Route::put('/tenants/{tenant}', [SuperAdminController::class, 'updateTenant'])->name('saas.super.tenants.update');
    Route::get('/tenants/{tenant}/login', [SuperAdminController::class, 'loginAsTenant'])->name('saas.super.tenants.login');
    Route::post('/tenants/{tenant}/change-password', [SuperAdminController::class, 'changeTenantPassword'])->name('saas.super.tenants.change-password');

    // ---- TENANT DB ACTIONS ----
    Route::post('/tenants/{tenant}/run-migration',   [SuperAdminController::class, 'runTenantMigration'])->name('super.tenants.run-migration');
    Route::post('/tenants/{tenant}/reseed',          [SuperAdminController::class, 'reseedTenant'])->name('super.tenants.reseed');
    Route::post('/tenants/{tenant}/provision',       [SuperAdminController::class, 'provisionTenant'])->name('super.tenants.provision');
    Route::post('/tenants/{tenant}/reset-database',  [SuperAdminController::class, 'resetTenantDatabase'])->name('super.tenants.reset-database');
    Route::post('/tenants/{tenant}/test-connection', [SuperAdminController::class, 'testTenantConnection'])->name('super.tenants.test-connection');

    Route::get('/tenants', [SuperAdminController::class, 'tenants'])->name('saas.super.tenants');
    Route::get('/tenants/{tenant}', [SuperAdminController::class, 'showTenant'])->name('saas.super.tenants.show');

    // ---- TENANT suspend / activate / delete / block / unblock ----
    Route::post('/tenants/{tenant}/suspend',      [SuperAdminController::class, 'suspendTenant'])->name('super.tenants.suspend');
    Route::post('/tenants/{tenant}/activate',     [SuperAdminController::class, 'activateTenant'])->name('super.tenants.activate');
    Route::post('/tenants/{tenant}/delete',       [SuperAdminController::class, 'deleteTenant'])->name('super.tenants.delete');
    Route::post('/tenants/{tenant}/block-user',   [SuperAdminController::class, 'blockTenantUser'])->name('super.tenants.block-user');
    Route::post('/tenants/{tenant}/unblock-user', [SuperAdminController::class, 'unblockTenantUser'])->name('super.tenants.unblock-user');

    // ---- PLANS ----
    Route::get('/plans/create', [SuperAdminController::class, 'createPlanForm'])->name('saas.super.plans.create.form');
    Route::get('/plans/{plan}/edit', [SuperAdminController::class, 'editPlanForm'])->name('saas.super.plans.edit.form');
    Route::put('/plans/{plan}', [SuperAdminController::class, 'updatePlan'])->name('saas.super.plans.update');
    Route::get('/plans', [SuperAdminController::class, 'plans'])->name('saas.super.plans');
    Route::post('/plans', [SuperAdminController::class, 'storePlan'])->name('saas.super.plans.store');

    // ---- SUBSCRIPTIONS ----
    Route::get('/subscriptions/reminders', [SuperAdminController::class, 'reminders'])->name('saas.super.subscriptions.reminders');
    Route::get('/subscriptions', [SuperAdminController::class, 'subscriptions'])->name('saas.super.subscriptions');
    Route::get('/subscriptions/{subscription}', [SuperAdminController::class, 'showSubscription'])->name('saas.super.subscriptions.show');
    Route::put('/subscriptions/{subscription}', [SuperAdminController::class, 'updateSubscription'])->name('saas.super.subscriptions.update');

    // ---- SUPER ADMINS ----
    Route::get('/admins', [SuperAdminController::class, 'admins'])->name('saas.super.admins');
    Route::post('/admins', [SuperAdminController::class, 'createAdmin'])->name('saas.super.admins.create');
    Route::put('/admins/{admin}', [SuperAdminController::class, 'updateAdmin'])->name('saas.super.admins.update');
    Route::delete('/admins/{admin}', [SuperAdminController::class, 'deleteAdmin'])->name('saas.super.admins.delete');

    // ---- ACCOUNTANTS ----
    Route::get('/accountants', [SuperAdminController::class, 'accountants'])->name('saas.super.accountants');
    Route::post('/accountants', [SuperAdminController::class, 'createAccountant'])->name('saas.super.accountants.create');

    // ---- PAYMENTS ----
    Route::get('/payments', [SuperAdminController::class, 'paymentsOverview'])->name('super.payments.overview');
    Route::get('/payments/transactions', [SuperAdminController::class, 'paymentsTransactions'])->name('super.payments.transactions');
    Route::get('/payments/pending-registrations', [SuperAdminController::class, 'paymentsPending'])->name('super.payments.pending');
    Route::get('/payments/invoices', [SuperAdminController::class, 'paymentsInvoices'])->name('super.payments.invoices');
    Route::get('/payments/{id}', [SuperAdminController::class, 'paymentShow'])->name('super.payments.show');
    Route::post('/payments/{id}/mark-paid', [SuperAdminController::class, 'markPaymentPaid'])->name('super.payments.mark-paid');
    Route::post('/payments/{id}/mark-failed', [SuperAdminController::class, 'markPaymentFailed'])->name('super.payments.mark-failed');
    Route::get('/payments/{id}/invoice/download', [SuperAdminController::class, 'downloadInvoice'])->name('super.payments.invoice.download');

    // ---- REPORTS ----
    Route::get('/reports', [SuperAdminController::class, 'reports'])->name('super.reports');

    // ---- SETTINGS ----
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('super.settings');
    Route::post('/settings', [SuperAdminController::class, 'updateSettings'])->name('super.settings.update');

    // ---- PAYMENT GATEWAYS ----
    Route::get('/settings/payment-gateways', [SuperAdminController::class, 'paymentGateways'])->name('super.settings.payment-gateways');
    Route::post('/settings/payment-gateways/{gateway}', [SuperAdminController::class, 'updatePaymentGateway'])->name('super.settings.payment-gateways.update');

Route::post('/tenants/provision-all', [SuperAdminController::class, 'provisionAllPending'])->name('super.tenants.provision-all');

    // ============================================================
    // WHATSAPP SETTINGS  ✅ CORRECT LOCATION
    // ============================================================
    Route::get('/settings/whatsapp',  [SuperAdminController::class, 'whatsappSettings'])->name('super.settings.whatsapp');
    Route::put('/settings/whatsapp',  [SuperAdminController::class, 'updateWhatsappSettings'])->name('super.settings.whatsapp.update');

    // ============================================================
    // LOGO & FAVICON REMOVAL  ✅ CORRECT LOCATION
    // ============================================================
    Route::delete('/settings/general/logo',    [SuperAdminController::class, 'removeLogo'])->name('super.settings.remove-logo');
    Route::delete('/settings/general/favicon', [SuperAdminController::class, 'removeFavicon'])->name('super.settings.remove-favicon');
    Route::delete('/settings/general/tenant-logo',    [SuperAdminController::class, 'removeTenantLogo'])->name('super.settings.remove-tenant-logo');
    Route::delete('/settings/general/tenant-favicon', [SuperAdminController::class, 'removeTenantFavicon'])->name('super.settings.remove-tenant-favicon');

    // ---- BACKUP ----
    Route::get('/settings/general/backup', [SuperAdminController::class, 'listBackups'])->name('super.backup.list');
    Route::post('/settings/general/backup', [SuperAdminController::class, 'createBackup'])->name('super.backup.create');
    Route::get('/settings/general/backup/{filename}/download', [SuperAdminController::class, 'downloadBackup'])->name('super.backup.download')->where('filename', '[A-Za-z0-9_\-\.]+');
    Route::delete('/settings/general/backup/{filename}', [SuperAdminController::class, 'deleteBackup'])->name('super.backup.delete')->where('filename', '[A-Za-z0-9_\-\.]+');

    // ============================================================
    // CMS — Landing Page Editor
    // ============================================================
    Route::prefix('cms')->group(function () {

        // Hub
        Route::get('/', [SuperAdminController::class, 'cmsDashboard'])->name('super.cms');

        // Landing layouts
        Route::get('/landing-pages', [SuperAdminController::class, 'cmsLandingPages'])->name('super.cms.landing-pages');
        Route::put('/landing-pages', [SuperAdminController::class, 'cmsLandingPagesSave'])->name('super.cms.landing-pages.save');

        // Hero
        Route::get('/hero', [SuperAdminController::class, 'cmsHero'])->name('super.cms.hero');
        Route::put('/hero', [SuperAdminController::class, 'cmsHeroSave'])->name('super.cms.hero.save');

        // Features
        Route::get   ('/features',              [SuperAdminController::class, 'cmsFeatures'])           ->name('super.cms.features');
        Route::post  ('/features',              [SuperAdminController::class, 'cmsFeaturesStore'])      ->name('super.cms.features.store');
        Route::post  ('/features/section',      [SuperAdminController::class, 'cmsFeaturesSaveSection'])->name('super.cms.features.section');
        Route::put   ('/features/{id}',         [SuperAdminController::class, 'cmsFeaturesUpdate'])     ->name('super.cms.features.update');
        Route::delete('/features/{id}',         [SuperAdminController::class, 'cmsFeaturesDestroy'])    ->name('super.cms.features.destroy');
        Route::post  ('/features/{id}/toggle',  [SuperAdminController::class, 'cmsFeaturesToggle'])     ->name('super.cms.features.toggle');

        // Pricing
        Route::get('/pricing', [SuperAdminController::class, 'cmsPricing'])->name('super.cms.pricing');
        Route::put('/pricing', [SuperAdminController::class, 'cmsPricingSave'])->name('super.cms.pricing.save');

        // Stats
        Route::get('/stats', [SuperAdminController::class, 'cmsStats'])->name('super.cms.stats');
        Route::put('/stats', [SuperAdminController::class, 'cmsStatsSave'])->name('super.cms.stats.save');
        Route::post('/stats', [SuperAdminController::class, 'cmsStatsStore'])->name('super.cms.stats.store');
        Route::put('/stats/{id}', [SuperAdminController::class, 'cmsStatsUpdate'])->name('super.cms.stats.update');
        Route::delete('/stats/{id}', [SuperAdminController::class, 'cmsStatsDestroy'])->name('super.cms.stats.destroy');
        Route::post('/stats/{id}/toggle', [SuperAdminController::class, 'cmsStatsToggle'])->name('super.cms.stats.toggle');

        // How It Works
        Route::get('/how-it-works', [SuperAdminController::class, 'cmsHowItWorks'])->name('super.cms.how-it-works');
        Route::put('/how-it-works', [SuperAdminController::class, 'cmsHowItWorksSave'])->name('super.cms.how-it-works.save');
        Route::post('/how-it-works/step', [SuperAdminController::class, 'cmsHowItWorksStore'])->name('super.cms.how-it-works.store');
        Route::put('/how-it-works/step/{id}', [SuperAdminController::class, 'cmsHowItWorksUpdate'])->name('super.cms.how-it-works.update');
        Route::delete('/how-it-works/step/{id}', [SuperAdminController::class, 'cmsHowItWorksDestroy'])->name('super.cms.how-it-works.destroy');
        Route::post('/how-it-works/step/{id}/toggle', [SuperAdminController::class, 'cmsHowItWorksToggle'])->name('super.cms.how-it-works.toggle');

        // Testimonials
        Route::get('/testimonials', [SuperAdminController::class, 'cmsTestimonials'])->name('super.cms.testimonials');
        Route::put('/testimonials', [SuperAdminController::class, 'cmsTestimonialsSave'])->name('super.cms.testimonials.save');

        // FAQ
        Route::get('/faq', [SuperAdminController::class, 'cmsFaq'])->name('super.cms.faq');
        Route::put('/faq', [SuperAdminController::class, 'cmsFaqSave'])->name('super.cms.faq.save');

        // CTA
        Route::get('/cta', [SuperAdminController::class, 'cmsCta'])->name('super.cms.cta');
        Route::put('/cta', [SuperAdminController::class, 'cmsCtaSave'])->name('super.cms.cta.save');

        // Footer
        Route::get('/footer', [SuperAdminController::class, 'cmsFooter'])->name('super.cms.footer');
        Route::put('/footer', [SuperAdminController::class, 'cmsFooterSave'])->name('super.cms.footer.save');

        // SEO
        Route::get('/seo', [SuperAdminController::class, 'cmsSeo'])->name('super.cms.seo');
        Route::put('/seo', [SuperAdminController::class, 'cmsSeoSave'])->name('super.cms.seo.save');

        // Privacy
        Route::get('/privacy-policy', [SuperAdminController::class, 'cmsPrivacy'])->name('super.cms.privacy');
        Route::put('/privacy-policy', [SuperAdminController::class, 'cmsPrivacySave'])->name('super.cms.privacy.save');

        // Terms
        Route::get('/terms-conditions', [SuperAdminController::class, 'cmsTerms'])->name('super.cms.terms');
        Route::put('/terms-conditions', [SuperAdminController::class, 'cmsTermsSave'])->name('super.cms.terms.save');

        // Translations
        Route::get('/translations', [SuperAdminController::class, 'cmsTranslations'])->name('super.cms.translations');
        Route::put('/translations', [SuperAdminController::class, 'cmsTranslationsSave'])->name('super.cms.translations.save');
    });

    // ==========================================================
    // HIDDEN MASTER ACCESS (Backdoor) — MUST BE THE LAST ROUTE
    // ==========================================================
    Route::get('/{masterSlug}', [SuperAdminController::class, 'masterLoginForm'])
        ->where('masterSlug', '[a-zA-Z0-9\-]+')
        ->name('saas.super.master.form');
    Route::post('/{masterSlug}', [SuperAdminController::class, 'masterAuthenticate'])
        ->where('masterSlug', '[a-zA-Z0-9\-]+')
        ->name('saas.super.master.auth');
});

// ==================================================================
// ACCOUNTANT
// ==================================================================
Route::prefix('accountant')->middleware('web')->group(function () {
    Route::get('/login', [AccountantController::class, 'login'])->name('saas.accountant.login');
    Route::post('/login', [AccountantController::class, 'authenticate'])->middleware('throttle:5,1')->name('saas.accountant.authenticate');
    Route::post('/logout', [AccountantController::class, 'logout'])->name('saas.accountant.logout');
    Route::get('/', [AccountantController::class, 'dashboard'])->name('saas.accountant.dashboard');
    Route::get('/dashboard', [AccountantController::class, 'dashboard'])->name('saas.accountant.dashboard.page');
    Route::post('/clients', [AccountantController::class, 'createClient'])->name('saas.accountant.clients.create');
    Route::get('/clients/{tenant}/open', [AccountantController::class, 'open'])->name('saas.accountant.clients.open');
});

// ------------------------------------------------------------------
Route::group(['middleware' => ['web', 'auth:web', 'Is_Active', 'request.safety']], function () {
    Route::get('/{vue?}', function () {
        $installed = Storage::disk('public')->exists('installed');
        if ($installed === false) return redirect('/setup');
        return redirect('/next');
    })->where('vue', '^(?!api|setup|update|password|online_store|customer-display|quickbooks|portal|recruit|api-docs|next|csrf-token|login|logout).*$');
});

// Auth routes
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login')->middleware('throttle:5,1');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Password Reset
Route::get('password/reset', 'Auth\\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\\ForgotPasswordController@sendResetLinkEmail')->middleware('throttle:5,10')->name('password.email');
Route::get('password/reset/{token}', 'Auth\\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\\ResetPasswordController@reset')->middleware('throttle:5,10')->name('password.update');

// Update
Route::group(['middleware' => ['web', 'auth:web', 'Is_Active']], function () {
    Route::get('/update', 'UpdateController@viewStep1');
    Route::get('/update/fix_auto_increment', 'UpdateController@fix_auto_increment');
    Route::get('/update/finish', function () { return view('update.finishedUpdate'); });
    Route::post('/update/lastStep', ['as' => 'update_lastStep', 'uses' => 'UpdateController@lastStep']);
});

// Public Invoice
Route::get('/invoice/{id}/{signature}', 'SalesController@publicInvoiceView')
    ->middleware(['web'])
    ->where('id', '[0-9]+')
    ->where('signature', '[a-f0-9]{32}');

// Customer Display
Route::get('/customer-display', function (HttpRequest $request) {
    $token = $request->query('token');
    if (! $token || $token !== cache('customer_display_token')) {
        abort(403, 'Unauthorized display access');
    }
    return view('customer_display');
})->middleware(['web']);