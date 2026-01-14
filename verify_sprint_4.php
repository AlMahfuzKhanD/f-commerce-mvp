<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\Order;
use App\Models\User;
use App\Models\Role;
use App\Models\Customer;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Sprint 4 Verification (Reports & Analytics)...\n";

try {
    // 1. Setup Context (Owner)
    $owner = User::where('email', 'like', 'owner_%')->firstOrFail();
    Auth::login($owner);
    echo "Acting as Owner: {$owner->email}\n";

    // 2. Data Prep: Create Orders for Yesterday and Today
    $customer = Customer::firstOrFail();
    $product = Product::firstOrFail();
    
    $orderService = new OrderService();
    
    // Create order for Today
    $todayOrder = $orderService->createOrder([
        'customer_id' => $customer->id,
        'order_source' => 'manual',
        'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 100]] // Total 200, Cost (assuming 10*2=20 or similar from seeded data)
    ]);
    // Manually set profit for testing if not auto-calc (OrderService usually does this)
    $todayOrder->profit_amount = 50; 
    $todayOrder->save();
    
    // Create order for Yesterday
    $yesterdayOrder = $orderService->createOrder([
        'customer_id' => $customer->id,
        'order_source' => 'manual',
        'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100]]
    ]);
    $yesterdayOrder->created_at = Carbon::yesterday();
    $yesterdayOrder->profit_amount = 20; 
    $yesterdayOrder->save();

    echo "✅ Data Prepped: Today 200 (Profit 50), Yesterday 100 (Profit 20).\n";

    // 3. Test Dashboard API
    echo "\n--- Testing Analytics Dashboard ---\n";
    $controller = app(\App\Http\Controllers\Api\v1\ReportController::class);
    $dashboardRequest = \Illuminate\Http\Request::create("/api/v1/analytics/summary", 'GET');
    $dashboardData = $controller->dashboard($dashboardRequest)->getData(true)['data'];
    
    echo "Dashboard Today Sales: {$dashboardData['today_sales']}\n";
    if ($dashboardData['today_sales'] >= 200) {
         echo "✅ Dashboard Today Sales Correct.\n";
    } else {
         echo "❌ Dashboard Today Sales Incorrect.\n";
    }

    // 4. Test Profit Report (Owner)
    echo "\n--- Testing Profit Report ---\n";
    $profitRequest = \Illuminate\Http\Request::create("/api/v1/reports/profit", 'GET', [
        'start_date' => Carbon::yesterday()->toDateString(),
        'end_date' => Carbon::today()->toDateString()
    ]);
    $profitData = $controller->profit($profitRequest)->getData(true)['data'];
    
    $totalProfit = array_sum(array_column($profitData, 'profit'));
    echo "Total Profit Report: {$totalProfit}\n";
    
    if ($totalProfit >= 70) {
        echo "✅ Profit Report Calculation Correct.\n";
    } else {
        echo "❌ Profit Report Calculation Incorrect.\n";
    }

    // 5. Test Access Control
    echo "\n--- Testing Access Control (Staff) ---\n";
    // Create/Find Staff User
    $staff = User::where('email', 'not like', 'owner_%')->first();
    if (!$staff) {
        $staff = User::factory()->create(['email' => 'staff@test.com', 'password' => bcrypt('password')]);
        // Assign Staff Role
    }
    // Mock Middleware behavior: In a real request, middleware intercepts. 
    // Here we can check if the user has permission.
    
    echo "Acting as Staff: {$staff->email}\n";
    Auth::login($staff);
    if ($staff->can('profit')) {
         echo "❌ Staff INCORRECTLY has 'profit' permission.\n";
    } else {
         echo "✅ Staff correctly DENIED 'profit' permission.\n";
    }

    echo "\nSprint 4 Verification Complete.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
