<?php

use App\Models\User;
use App\Models\Role;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::first();
if (!$user) {
    echo "No user found.\n";
    exit(1);
}

$role = Role::firstOrCreate(
    ['name' => 'Owner', 'tenant_id' => $user->tenant_id]
);

if (!$user->roles->contains($role->id)) {
    $user->roles()->attach($role);
    echo "Assigned Owner role to user {$user->email}.\n";
} else {
    echo "User already has Owner role.\n";
}
