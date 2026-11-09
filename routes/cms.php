<?php

use App\Http\Controllers\SaaS\CmsController;
use Illuminate\Support\Facades\Route;

/*
| CMS inner-page CRUD routes — loaded from RouteServiceProvider.
| All sections use the same generic controller.
*/

Route::prefix('super/cms')->middleware('web')->group(function () {
    // Generic CRUD for every repeater section
    $sections = [
        'features', 'stats', 'steps', 'testimonials', 'faq',
        'footer-columns', 'footer-social', 'how-it-works-steps',
    ];

    foreach ($sections as $section) {
        Route::get   ("/$section",                 [CmsController::class, 'list'])         ->defaults('section', $section)->name("super.cms.$section.list");
        Route::post  ("/$section",                 [CmsController::class, 'store'])        ->defaults('section', $section)->name("super.cms.$section.store");
        Route::put   ("/$section/{id}",            [CmsController::class, 'update'])       ->defaults('section', $section)->name("super.cms.$section.update");
        Route::delete("/$section/{id}",            [CmsController::class, 'destroy'])      ->defaults('section', $section)->name("super.cms.$section.destroy");
        Route::post  ("/$section/{id}/toggle",     [CmsController::class, 'toggle'])       ->defaults('section', $section)->name("super.cms.$section.toggle");
    }

    // Footer columns + social (separate sections)
    Route::get   ('/footer/columns',           [CmsController::class, 'list'])   ->defaults('section', 'footer_columns')->name('super.cms.footer_columns.list');
    Route::post  ('/footer/columns',           [CmsController::class, 'store'])  ->defaults('section', 'footer_columns')->name('super.cms.footer_columns.store');
    Route::put   ('/footer/columns/{id}',      [CmsController::class, 'update']) ->defaults('section', 'footer_columns')->name('super.cms.footer_columns.update');
    Route::delete('/footer/columns/{id}',      [CmsController::class, 'destroy'])->defaults('section', 'footer_columns')->name('super.cms.footer_columns.destroy');
    Route::post  ('/footer/columns/{id}/toggle',[CmsController::class, 'toggle'])->defaults('section', 'footer_columns')->name('super.cms.footer_columns.toggle');

    Route::get   ('/footer/social',            [CmsController::class, 'list'])   ->defaults('section', 'footer_social')->name('super.cms.footer_social.list');
    Route::post  ('/footer/social',            [CmsController::class, 'store'])  ->defaults('section', 'footer_social')->name('super.cms.footer_social.store');
    Route::put   ('/footer/social/{id}',       [CmsController::class, 'update']) ->defaults('section', 'footer_social')->name('super.cms.footer_social.update');
    Route::delete('/footer/social/{id}',       [CmsController::class, 'destroy'])->defaults('section', 'footer_social')->name('super.cms.footer_social.destroy');
    Route::post  ('/footer/social/{id}/toggle',[CmsController::class, 'toggle']) ->defaults('section', 'footer_social')->name('super.cms.footer_social.toggle');
});
