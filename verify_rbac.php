<?php

use App\Models\Permission;
use App\Models\Role;
use App\Services\AuthService;
use Illuminate\Support\Facades\Gate;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting RBAC Verification...\n";

try {
    // 1. Register new Tenant (Owner)
    $authService = new AuthService();
    $ownerData = [
        'name' => 'Owner User',
        'email' => 'owner_' . time() . '@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'company_name' => 'Test Company ' . time(),
    ];
    
    echo "Registering Tenant...\n";
    $result = $authService->register($ownerData);
    $owner = $result['user'];
    $tenant = $result['tenant'];
    
    echo "Tenant Created: {$tenant->name}\n";
    echo "User Created: {$owner->email}\n";

    // 2. Check Owner Role
    if ($owner->hasRole('Owner')) {
        echo "✅ Owner has 'Owner' role.\n";
    } else {
        echo "❌ Owner MISSING 'Owner' role.\n";
    }

    // 3. Check Owner Permission (Gate Bypass)
    // Note: Gate::allows() uses the currently authenticated user, or we can use $owner->can()
    // We need to actAs($owner) for Gate facade normally, or rely on our trait method hasPermissionTo()?
    // Our trait method checks hasRole('Owner').
    if ($owner->hasPermissionTo('order.delete')) {
        echo "✅ Owner has 'order.delete' permission (via Role check).\n";
    } else {
        echo "❌ Owner MISSING 'order.delete' permission.\n";
    }

    // 4. Create Staff User
    echo "Creating Staff User...\n";
    $staff = \App\Models\User::create([
        'name' => 'Staff User',
        'email' => 'staff_' . time() . '@test.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'tenant_id' => $tenant->id,
    ]);

    // 5. Check Staff Permission (Should be false)
    if (!$staff->hasPermissionTo('order.delete')) {
        echo "✅ Staff correctly blocked from 'order.delete'.\n";
    } else {
        echo "❌ Staff INCORRECTLY has 'order.delete'.\n";
    }

    // 6. Assign Permission directly
    echo "Assigning 'order.delete' permission to Staff...\n";
    $perm = Permission::where('slug', 'order.delete')->first();
    $staff->permissions()->attach($perm);
    $staff->refresh();

    // 7. Check Staff Permission (Should be true)
    if ($staff->hasPermissionTo('order.delete')) {
        echo "✅ Staff now has 'order.delete' permission.\n";
    } else {
        echo "❌ Staff MISSING 'order.delete' permission after assignment.\n";
    }
    
    echo "RBAC Verification Complete.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
