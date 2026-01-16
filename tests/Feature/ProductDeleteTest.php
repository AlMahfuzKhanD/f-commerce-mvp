<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_hard_delete_removes_record_and_variants()
    {
        $user = User::factory()->create(['tenant_id' => 1]);
        $this->actingAs($user);

        // 1. Create Product with Variants
        $product = Product::create([
            'tenant_id' => 1,
            'name' => 'Delete Me',
            'base_price' => 100,
            'description' => 'To be deleted',
            'is_active' => true,
            'stock_quantity' => 0
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock_quantity' => 10,
            'extra_price' => 0
        ]);

        // Verify exist
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id]);

        // 2. Perform Delete
        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        // 3. Verify Response
        $response->assertStatus(200);

        // 4. Verify Database
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }
}
