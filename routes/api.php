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

            // Products (Read-Only)
            Route::get('products', [\App\Http\Controllers\Api\v1\ProductController::class, 'index']);
            Route::get('products/{id}', [\App\Http\Controllers\Api\v1\ProductController::class, 'show']);

            // Customers
            Route::get('customers', [\App\Http\Controllers\Api\v1\CustomerController::class, 'index']);
            Route::post('customers', [\App\Http\Controllers\Api\v1\CustomerController::class, 'store']);
            Route::get('customers/{id}', [\App\Http\Controllers\Api\v1\CustomerController::class, 'show']);

            // Orders
            Route::get('orders', [\App\Http\Controllers\Api\v1\OrderController::class, 'index']);
            Route::post('orders', [\App\Http\Controllers\Api\v1\OrderController::class, 'store']);
            Route::get('orders/{id}', [\App\Http\Controllers\Api\v1\OrderController::class, 'show']);
            // Route::put('orders/{id}', [\App\Http\Controllers\Api\v1\OrderController::class, 'update']);
            // Route::patch('orders/{id}/status', [\App\Http\Controllers\Api\v1\OrderController::class, 'updateStatus']);
        });
    });

    // Test route for verification
    Route::get('/ping', function () {
        return response()->json(['message' => 'pong', 'version' => 'v1']);
    });
});
