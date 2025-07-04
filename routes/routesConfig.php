<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Route;

return fn () => Route::namespace('App\\Http\\Controllers')
    ->middleware([
        'api',
        'encrypt',
        'service',
    ])
    ->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Routes For Erp
        |--------------------------------------------------------------------------
        */
        Route::namespace('Erp')
            ->name('erp.')
            ->prefix('erp')
            ->group(function () {
                /*
                    |--------------------------------------------------------------------------
                    | Routes Public
                    |--------------------------------------------------------------------------
                    */
                Route::namespace('Public')
                    ->name('public.')
                    ->prefix('public')
                    ->middleware([])
                    ->group(function () {
                        Route::prefix('auth')
                            ->name('auth.')
                            ->group(base_path('routes/erp/public/auth.php'));
                    });
            });
    });
