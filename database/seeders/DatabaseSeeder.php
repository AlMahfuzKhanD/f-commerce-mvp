<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a tenant first
        $tenant = \App\Models\Tenant::create([
            'name' => 'Test Tenant',
            'slug' => 'test',
        ]);

        User::factory()->create([
            'name' => 'Owner User',
            'email' => 'test@example.com',
            'tenant_id' => $tenant->id,
        ]);

        // Also run PermissionSeeder
        $this->call(PermissionSeeder::class);
    }
}
