<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HoldController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/products/{product}', [ProductController::class, 'show']);

    Route::post('/holds', [HoldController::class, 'store']);

    Route::post('/orders', [OrderController::class, 'store']);

    Route::post('/payments/webhook', [PaymentController::class, 'handle']);
});
