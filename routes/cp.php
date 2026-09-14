<?php

use Estouai\CookieConsent\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:manage cookie consent settings')->group(function () {
    Route::get('cookie-consent', [SettingsController::class, 'index'])->name('cookie-consent.index');
    Route::post('cookie-consent', [SettingsController::class, 'update'])->name('cookie-consent.update');
});
