<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Assume working on the first tenant for now (or loop through all)
        $tenant = Tenant::first();
        if (!$tenant) return;

        // 1. Manager Role (Almost Admin)
        $manager = Role::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Manager']
        );
        // Give all permissions except maybe nuking data (but for MVP give all)
        // Or exclude 'settings.delete' if it existed.
        $allPermissions = Permission::all();
        $manager->permissions()->sync($allPermissions);

        // 2. Staff Role (Limited)
        $staff = Role::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Staff']
        );
        
        $staffPermissions = Permission::whereIn('slug', [
            'dashboard.view',
            'order.view', 'order.create', 'order.update_status',
            'product.view',
            'customer.view', 'customer.create',
            'supplier.view', // Maybe?
        ])->get();
        
        $staff->permissions()->sync($staffPermissions);
    }
}
