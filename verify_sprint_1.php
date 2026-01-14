<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Sprint 1 Verification...\n";

try {
    // 1. Setup Context (Owner)
    $owner = User::where('email', 'like', 'owner_%')->first();
    if (!$owner) {
        die("❌ No Owner user found. Run earlier verification first.\n");
    }
    Auth::login($owner);
    // Bind tenant scope manually if middleware not running? 
    // TenantMiddleware sets logic usually. But User trait scopes queries automatically.
    // Ensure we are in tenant context effectively.
    echo "Acting as User: {$owner->email} (Tenant: {$owner->tenant_id})\n";

    // 2. Create Product
    echo "Creating Product...\n";
    $product = Product::create([
        'tenant_id' => $owner->tenant_id,
        'name' => 'Test T-Shirt',
        'sku' => 'TSHIRT-001-' . time(),
        'base_price' => 500.00,
        'cost_price' => 300.00,
        'is_active' => true,
    ]);
    echo "✅ Product Created: {$product->name} (ID: {$product->id})\n";

    // 3. Create Customer
    echo "Creating Customer...\n";
    $customer = Customer::create([
        'tenant_id' => $owner->tenant_id,
        'name' => 'John Doe',
        'phone' => '01700000000',
        'address' => 'Dhaka, Bangladesh',
    ]);
    echo "✅ Customer Created: {$customer->name} (ID: {$customer->id})\n";

    // 4. Create Order via Service
    echo "Creating Order...\n";
    $orderService = new OrderService();
    $orderData = [
        'customer_id' => $customer->id,
        'order_source' => 'facebook',
        'notes' => 'Test Order via Script',
        'delivery_charge' => 60,
        'discount' => 10,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 450.00, // Discounted unit price
            ]
        ]
    ];

    $order = $orderService->createOrder($orderData);
    
    // 5. Verify Order
    echo "✅ Order Created: {$order->order_number} (Status: {$order->status})\n";
    
    if ($order->total_amount == (2 * 450 + 60 - 10)) {
        echo "✅ Financials Correct: {$order->total_amount}\n";
    } else {
        echo "❌ Financials Incorrect: {$order->total_amount} (Expected: " . (2 * 450 + 60 - 10) . ")\n";
    }

    if ($order->items->first()->product_name === 'Test T-Shirt') {
        echo "✅ Product Name Snapshot Correct.\n";
    } else {
        echo "❌ Product Name Snapshot Missing.\n";
    }

    // 6. Verify Event
    if ($order->events()->where('event_type', 'status_change')->exists()) {
        echo "✅ Status Change Event Logged.\n";
    } else {
        echo "❌ Status Change Event MISSING.\n";
    }

    echo "Sprint 1 Verification Complete.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
