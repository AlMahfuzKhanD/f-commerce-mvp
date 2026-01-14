<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Sprint 2 Verification...\n";

try {
    // 1. Setup Context (Owner)
    $owner = User::where('email', 'like', 'owner_%')->firstOrFail();
    Auth::login($owner);
    echo "Acting as User: {$owner->email} (Tenant: {$owner->tenant_id})\n";

    // Cleanup from previous runs
    Customer::where('phone', '01999999999')->forceDelete(); 
    Product::where('sku', 'like', 'SPRINT2-ITEM-%')->forceDelete();
    echo "🧹 Cleanup Done.\n";

    // 2. Product Management (Stock Logic)
    echo "\n--- Testing Product Stock ---\n";
    $sku = 'SPRINT2-ITEM-' . time();
    $product = Product::create([
        'tenant_id' => $owner->tenant_id,
        'name' => 'Stock Test Item',
        'sku' => $sku,
        'base_price' => 100.00,
        'stock_quantity' => 10, // Initial Stock
        'is_active' => true,
    ]);
    echo "✅ Product Created: {$product->name} (Stock: {$product->stock_quantity})\n";

    // 3. Customer Integrity (Unique Phone)
    echo "\n--- Testing Customer Uniqueness ---\n";
    $phone = '01999999999';
    $c1 = Customer::create([
        'tenant_id' => $owner->tenant_id, 
        'name' => 'Unique User 1', 
        'phone' => $phone
    ]);
    echo "✅ Customer 1 Created.\n";

    try {
        $c2 = Customer::create([
            'tenant_id' => $owner->tenant_id, 
            'name' => 'Unique User 2', 
            'phone' => $phone // Duplicate
        ]);
        echo "❌ Duplicate Customer Created (Expected Failure)!\n";
    } catch (\Exception $e) {
        echo "✅ Duplicate Customer Blocked: " . $e->getMessage() . "\n";
    }

    // 4. Order Logic (Stock Deduction)
    echo "\n--- Testing Stock Deduction ---\n";
    $orderService = new OrderService();
    $orderData = [
        'customer_id' => $c1->id,
        'order_source' => 'manual',
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 100.00,
            ]
        ]
    ];
    $order = $orderService->createOrder($orderData);
    $product->refresh();
    
    if ($product->stock_quantity === 8) {
        echo "✅ Stock Deducted Correctly. New Stock: {$product->stock_quantity}\n";
    } else {
        echo "❌ Stock Deduction Failed. Stock: {$product->stock_quantity}\n";
    }

    // 5. Customer Delete Protection
    echo "\n--- Testing Customer Delete Protection ---\n";
    $controller = new \App\Http\Controllers\Api\v1\CustomerController();
    $response = $controller->destroy((string)$c1->id);
    
    // Check validation response (409)
    if ($response->getStatusCode() === 409) {
        echo "✅ Customer Deletion Blocked (Has Order).\n";
    } else {
        echo "❌ Customer Deletion INCORRECTLY Allowed (Status: {$response->getStatusCode()}).\n";
    }
    
    // 6. Test Insufficient Stock
    echo "\n--- Testing Insufficient Stock ---\n";
    try {
        $orderService->createOrder([
            'customer_id' => $c1->id,
            'order_source' => 'manual',
            'items' => [['product_id' => $product->id, 'quantity' => 100, 'unit_price' => 100]]
        ]);
        echo "❌ Insufficient Stock Order Allowed!\n";
    } catch (\Exception $e) {
        echo "✅ Insufficient Stock Blocked: " . $e->getMessage() . "\n";
    }

    echo "\nSprint 2 Verification Complete.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
