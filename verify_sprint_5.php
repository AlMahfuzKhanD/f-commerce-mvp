<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Sprint 5 Verification (Launch Readiness)...\n";

try {
    // 1. Setup Context (Owner)
    $owner = User::where('email', 'like', 'owner_%')->firstOrFail();
    Auth::login($owner);
    echo "Acting as Owner: {$owner->email}\n";

    // 2. Test Tenant Settings Update
    echo "\n--- Testing Settings Update ---\n";
    $settingsController = app(\App\Http\Controllers\Api\v1\SettingsController::class);
    $settingsRequest = \Illuminate\Http\Request::create("/api/v1/settings", 'PUT', [
        'address' => '123 Test Street, Dhaka',
        'phone' => '+8801700000000',
        'logo' => 'https://example.com/logo.png',
        'currency' => 'BDT'
    ]);
    $settingsRequest->setUserResolver(fn () => $owner);
    $settingsController->update($settingsRequest);
    
    $owner->refresh();
    if ($owner->tenant->address === '123 Test Street, Dhaka' && $owner->tenant->phone === '+8801700000000') {
         echo "✅ Tenant Settings Updated (Address/Phone).\n";
    } else {
         echo "❌ Tenant Settings Update Failed.\n";
    }

    // 3. Test Delivery Assignment
    echo "\n--- Testing Delivery Assignment ---\n";
    $order = Order::where('tenant_id', $owner->tenant_id)->latest()->firstOrFail();
    echo "Testing with Order: {$order->order_number}\n";

    $deliveryController = app(\App\Http\Controllers\Api\v1\DeliveryController::class);
    $deliveryRequest = \Illuminate\Http\Request::create("/api/v1/orders/{$order->id}/delivery", 'POST', [
        'courier_name' => 'Pathao',
        'tracking_number' => 'PTH-123456',
        'delivery_status' => 'shipped'
    ]);
    $deliveryController->store($deliveryRequest, (string)$order->id);
    
    $order->refresh();
    // Check if delivery record exists
    if ($order->delivery && $order->delivery->courier_name === 'Pathao') {
         echo "✅ Courier Assigned: {$order->delivery->courier_name} (Track: {$order->delivery->tracking_number}).\n";
    } else {
         echo "❌ Courier Assignment Failed.\n";
    }

    // 4. Test Profile Password Change
    echo "\n--- Testing Password Change ---\n";
    $profileController = app(\App\Http\Controllers\Api\v1\ProfileController::class);
    $newPassword = 'newpassword123';
    
    $passRequest = \Illuminate\Http\Request::create("/api/v1/profile/password", 'PUT', [
        'current_password' => 'password', // Assuming factory default 'password'
        'password' => $newPassword,
        'password_confirmation' => $newPassword
    ]);
    // Mock user for request guard
    $passRequest->setUserResolver(fn () => $owner);
    
    try {
        $profileController->updatePassword($passRequest);
        $owner->refresh();
        if (Hash::check($newPassword, $owner->password)) {
            echo "✅ Password Changed Successfully.\n";
            // Revert password for future tests
            $owner->update(['password' => Hash::make('password')]);
            echo "🔄 Password Reverted to default.\n";
        } else {
            echo "❌ Password Change Logic Failed.\n";
        }
    } catch (\Exception $e) {
        echo "❌ Password Change Failed: " . $e->getMessage() . "\n";
    }

    echo "\nSprint 5 Verification Complete.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
