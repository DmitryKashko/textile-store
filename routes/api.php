<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::middleware('throttle:auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('categories', [CategoryController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{slug}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders', [OrderController::class, 'store'])->middleware('throttle:orders');
});
