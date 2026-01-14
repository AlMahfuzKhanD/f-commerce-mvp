<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Sprint 3 Verification (Invoices & Payments)...\n";

try {
    // 1. Setup Context
    $owner = User::where('email', 'like', 'owner_%')->firstOrFail();
    Auth::login($owner);
    echo "Acting as User: {$owner->email}\n";

    // 2. Create Order
    $customer = Customer::firstOrFail();
    $product = Product::where('stock_quantity', '>', 0)->firstOrFail();
    
    $orderService = new OrderService();
    $order = $orderService->createOrder([
        'customer_id' => $customer->id,
        'order_source' => 'manual',
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1000]
        ]
    ]);
    echo "✅ Order Created: {$order->order_number} (Total: {$order->total_amount})\n";

    // 3. Confirm Order -> Trigger Invoice
    echo "\n--- Testing Invoice Generation ---\n";
    $controller = app(\App\Http\Controllers\Api\v1\OrderController::class);
    $request = \Illuminate\Http\Request::create("/api/v1/orders/{$order->id}/status", 'POST', ['status' => 'confirmed']);
    $request->setUserResolver(fn () => $owner);
    
    $controller->updateStatus($request, (string)$order->id, app(InvoiceService::class));
    
    $order->refresh();
    if ($order->status === 'confirmed' && $order->invoice) {
        echo "✅ Order Confirmed.\n";
        echo "✅ Invoice Generated: {$order->invoice->invoice_number}\n";
        echo "✅ Invoice Items Count: {$order->invoice->items()->count()}\n";
    } else {
        echo "❌ Invoice Generation Failed!\n";
    }

    // 4. Test Partial Payment
    echo "\n--- Testing Partial Payment ---\n";
    $paymentController = app(\App\Http\Controllers\Api\v1\PaymentController::class);
    $payRequest1 = \Illuminate\Http\Request::create("/api/v1/orders/{$order->id}/payments", 'POST', [
        'amount' => 400,
        'payment_method' => 'cash',
        'note' => 'Partial payment'
    ]);
    $paymentController->store($payRequest1, (string)$order->id);
    
    $order->refresh();
    echo "Paid: {$order->paid_amount}, Due: {$order->due_amount}, Status: {$order->payment_status}\n";
    
    if ($order->payment_status === 'partially_paid' && $order->due_amount == 600) {
        echo "✅ Partial Payment Logic Correct.\n";
    } else {
        echo "❌ Partial Payment Logic Failed.\n";
    }

    // 5. Test Full Payment
    echo "\n--- Testing Full Payment ---\n";
    $payRequest2 = \Illuminate\Http\Request::create("/api/v1/orders/{$order->id}/payments", 'POST', [
        'amount' => 600,
        'payment_method' => 'bkash'
    ]);
    $paymentController->store($payRequest2, (string)$order->id);
    
    $order->refresh();
    echo "Paid: {$order->paid_amount}, Due: {$order->due_amount}, Status: {$order->payment_status}\n";
    
    if ($order->payment_status === 'paid' && $order->due_amount == 0) {
        echo "✅ Full Payment Logic Correct.\n";
    } else {
        echo "❌ Full Payment Logic Failed.\n";
    }

    echo "\nSprint 3 Verification Complete.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
