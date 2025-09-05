<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevTokenAuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopController;

Route::post('/dev/login',  [DevTokenAuthController::class, 'login']);
Route::post('/dev/logout', [DevTokenAuthController::class, 'logout']);
Route::get('/auth/me', fn() => request()->user())->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/orders',   [OrderController::class, 'index']);

    Route::get('/export/products.{format}', [ProductController::class, 'export'])
        ->whereIn('format', ['csv','xlsx']);
    Route::get('/export/orders.{format}',   [OrderController::class, 'export'])
        ->whereIn('format', ['csv','xlsx']);

    Route::get('/shops', [ShopController::class, 'index']);
});
