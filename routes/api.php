<?php

use App\Http\Controllers\Api\v1\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth Routes (Public)
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    // Protected Business Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Products
        Route::apiResource('products', \App\Http\Controllers\Api\v1\ProductController::class);
        Route::apiResource('categories', \App\Http\Controllers\Api\v1\CategoryController::class);
        Route::apiResource('sizes', \App\Http\Controllers\Api\v1\SizeController::class);
        Route::apiResource('colors', \App\Http\Controllers\Api\v1\ColorController::class);

        // Customers
        Route::apiResource('customers', \App\Http\Controllers\Api\v1\CustomerController::class);

        // Orders
        Route::get('orders', [\App\Http\Controllers\Api\v1\OrderController::class, 'index']);
        Route::post('orders', [\App\Http\Controllers\Api\v1\OrderController::class, 'store']);
        Route::get('orders/{id}', [\App\Http\Controllers\Api\v1\OrderController::class, 'show']);
        Route::post('orders/{id}/status', [\App\Http\Controllers\Api\v1\OrderController::class, 'updateStatus']);
        Route::get('orders/{id}/invoice', [\App\Http\Controllers\Api\v1\InvoiceController::class, 'show']);
        Route::get('orders/{id}/payments', [\App\Http\Controllers\Api\v1\PaymentController::class, 'index']);
        Route::post('orders/{id}/payments', [\App\Http\Controllers\Api\v1\PaymentController::class, 'store']);

        // Reports & Analytics
        Route::get('analytics/summary', [\App\Http\Controllers\Api\v1\ReportController::class, 'dashboard']);
        Route::get('reports/sales', [\App\Http\Controllers\Api\v1\ReportController::class, 'sales']);
        Route::get('reports/products', [\App\Http\Controllers\Api\v1\ReportController::class, 'topProducts']);
        Route::get('reports/customers', [\App\Http\Controllers\Api\v1\ReportController::class, 'topCustomers']);
        
        // Protected Profit Report
        Route::middleware('check_permission:profit')->get('reports/profit', [\App\Http\Controllers\Api\v1\ReportController::class, 'profit']);

        // Sourcing
        Route::apiResource('suppliers', \App\Http\Controllers\Api\v1\SupplierController::class);
        Route::apiResource('purchases', \App\Http\Controllers\Api\v1\PurchaseController::class);

        // Operations
        Route::apiResource('expenses', \App\Http\Controllers\Api\v1\ExpenseController::class);

        // Settings & Profile
        Route::get('settings', [\App\Http\Controllers\Api\v1\SettingsController::class, 'show']);
        Route::put('settings', [\App\Http\Controllers\Api\v1\SettingsController::class, 'update']);

        Route::get('profile', [\App\Http\Controllers\Api\v1\ProfileController::class, 'show']);
        Route::put('profile', [\App\Http\Controllers\Api\v1\ProfileController::class, 'update']);
        Route::put('profile/password', [\App\Http\Controllers\Api\v1\ProfileController::class, 'updatePassword']);

        // Delivery
        Route::get('orders/{id}/delivery', [\App\Http\Controllers\Api\v1\DeliveryController::class, 'show']);
        Route::post('orders/{id}/delivery', [\App\Http\Controllers\Api\v1\DeliveryController::class, 'store']);
    });


    // Test route for verification
    Route::get('/ping', function () {
        return response()->json(['message' => 'pong', 'version' => 'v1']);
    });
});
