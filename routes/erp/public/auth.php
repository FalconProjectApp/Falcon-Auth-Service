<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->namespace('V1')
    ->name('v1.')
    ->controller('AuthController')
    ->group(function () {
        Route::post('/', 'store')->name('store');
        Route::post('send-token', 'sendToken')->name('send-token');
        Route::post('check-token', 'checkToken')->name('check-token');
    });

Route::prefix('v2')
    ->namespace('V2')
    ->name('v2.')
    ->controller('AuthController')
    ->group(function () {
        Route::post('/', 'store')->name('store');
    });