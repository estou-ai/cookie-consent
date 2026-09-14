<?php

use Estouai\CookieConsent\Http\Controllers\ConsentLogController;
use Illuminate\Support\Facades\Route;

// A beacon endpoint, not a user session action — exempt from CSRF like
// Statamic core's own `nocache`/`csrf` action routes (see vendor/statamic/
// cms/routes/web.php), since a static-cached page has no CSRF cookie to send
// back. Throttled instead, against log-flooding abuse.
Route::post('log', [ConsentLogController::class, 'store'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([
        'App\Http\Middleware\VerifyCsrfToken',
        'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken',
        'Illuminate\Foundation\Http\Middleware\PreventRequestForgery',
    ])
    ->name('cookie-consent.log');
