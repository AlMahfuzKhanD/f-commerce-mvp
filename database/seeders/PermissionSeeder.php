<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Orders
            ['slug' => 'order.view', 'group' => 'orders', 'description' => 'View orders'],
            ['slug' => 'order.create', 'group' => 'orders', 'description' => 'Create new orders'],
            ['slug' => 'order.update_status', 'group' => 'orders', 'description' => 'Update order status'],
            ['slug' => 'order.delete', 'group' => 'orders', 'description' => 'Delete orders'],

            // Customers
            ['slug' => 'customer.view', 'group' => 'customers', 'description' => 'View customers'],
            ['slug' => 'customer.create', 'group' => 'customers', 'description' => 'Create new customers'],
            ['slug' => 'customer.update', 'group' => 'customers', 'description' => 'Update customer details'],

            // Products
            ['slug' => 'product.view', 'group' => 'products', 'description' => 'View products'],
            ['slug' => 'product.create', 'group' => 'products', 'description' => 'Create new products'],
            ['slug' => 'product.update', 'group' => 'products', 'description' => 'Update products'],
            ['slug' => 'product.delete', 'group' => 'products', 'description' => 'Delete products'],

            // Staff
            ['slug' => 'staff.view', 'group' => 'staff', 'description' => 'View staff members'],
            ['slug' => 'staff.create', 'group' => 'staff', 'description' => 'Invite new staff'],
            ['slug' => 'staff.update', 'group' => 'staff', 'description' => 'Update staff roles'],
            ['slug' => 'staff.delete', 'group' => 'staff', 'description' => 'Remove staff'],

            // Settings
            ['slug' => 'settings.view', 'group' => 'settings', 'description' => 'View tenant settings'],
            ['slug' => 'settings.update', 'group' => 'settings', 'description' => 'Update tenant settings'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['slug' => $perm['slug']],
                ['group' => $perm['group'], 'description' => $perm['description']]
            );
        }
    }
}
