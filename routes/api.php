<?php

use App\Http\Controllers\Api\v1\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);

            // Products
            Route::apiResource('products', \App\Http\Controllers\Api\v1\ProductController::class);

            // Customers
            Route::apiResource('customers', \App\Http\Controllers\Api\v1\CustomerController::class);

            // Orders
            Route::get('orders', [\App\Http\Controllers\Api\v1\OrderController::class, 'index']);
            Route::post('orders', [\App\Http\Controllers\Api\v1\OrderController::class, 'store']);
            Route::get('orders/{id}', [\App\Http\Controllers\Api\v1\OrderController::class, 'show']);
            Route::post('orders/{id}/status', [\App\Http\Controllers\Api\v1\OrderController::class, 'updateStatus']); // Sprint 3
            Route::get('orders/{id}/invoice', [\App\Http\Controllers\Api\v1\InvoiceController::class, 'show']); // Sprint 3
            Route::get('orders/{id}/payments', [\App\Http\Controllers\Api\v1\PaymentController::class, 'index']); // Sprint 3
            Route::post('orders/{id}/payments', [\App\Http\Controllers\Api\v1\PaymentController::class, 'store']); // Sprint 3
            // Route::put('orders/{id}', [\App\Http\Controllers\Api\v1\OrderController::class, 'update']);
            // Route::patch('orders/{id}/status', [\App\Http\Controllers\Api\v1\OrderController::class, 'updateStatus']);
        });
    });

    // Test route for verification
    Route::get('/ping', function () {
        return response()->json(['message' => 'pong', 'version' => 'v1']);
    });
});
