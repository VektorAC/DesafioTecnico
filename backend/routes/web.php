<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HardcodedAuthController;
use App\Http\Controllers\ShopController;

Route::post('/auth/login-hardcoded', [HardcodedAuthController::class, 'login']);
Route::post('/auth/logout', [HardcodedAuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    Route::get('/shops/connect',  [ShopController::class, 'shopifyConnect']);
    Route::get('/shops/callback', [ShopController::class, 'shopifyCallback']);
    Route::post('/shops/woo',     [ShopController::class, 'wooConnect']);
});
