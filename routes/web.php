<?php

use Illuminate\Support\Facades\Route;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Http\Controllers\BrowserSessionsController;

Route::middleware(config('browser-sessions-lite.middleware', ['web', 'auth']))
    ->prefix(config('browser-sessions-lite.prefix', 'user'))
    ->name('browser-sessions.')
    ->group(function () {
        Route::get('/browser-sessions', [BrowserSessionsController::class, 'index'])
            ->name('index');

        Route::delete('/browser-sessions/others', [BrowserSessionsController::class, 'destroy'])
            ->name('destroy');
    });
