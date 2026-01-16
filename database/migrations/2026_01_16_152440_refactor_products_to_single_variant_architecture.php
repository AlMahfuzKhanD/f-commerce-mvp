<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add new columns to product_variants (Check first)
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'price')) {
                 $table->decimal('price', 10, 2)->after('extra_price')->nullable(); 
            }
            if (!Schema::hasColumn('product_variants', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->after('price')->nullable();
            }
        });

        // 2. Migrate Data
        $products = \Illuminate\Support\Facades\DB::table('products')->get();

        foreach ($products as $product) {
            $variants = \Illuminate\Support\Facades\DB::table('product_variants')
                ->where('product_id', $product->id)
                ->get();

            if ($variants->isEmpty()) {
                // Check if we already migrated this product?
                // Logic: A product without variants needs a variant. 
                // Using updateOrInsert might be safer but insert is fine if we don't have duplicates.
                // Assuming ID auto-increment or similar.
                
                \Illuminate\Support\Facades\DB::table('product_variants')->insert([
                    'product_id' => $product->id,
                    'size_id' => null,
                    'color_id' => null,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'stock_quantity' => $product->stock_quantity,
                    'price' => $product->base_price, 
                    'cost_price' => $product->cost_price ?? 0,
                    'extra_price' => 0, 
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ]);
            } else {
                foreach ($variants as $variant) {
                    // Only update if price is NULL (meaning not yet migrated)
                    if ($variant->price === null) {
                         $price = $product->base_price + $variant->extra_price;
                        \Illuminate\Support\Facades\DB::table('product_variants')
                            ->where('id', $variant->id)
                            ->update([
                                'price' => $price,
                                'cost_price' => $product->cost_price ?? 0,
                            ]);
                    }
                }
            }
        }

        // 3. Cleanup Products Table
        Schema::table('products', function (Blueprint $table) {
            // Ensure there is an index on tenant_id for the FK, so we can drop the unique index
            // We use a raw DB statement or just try adding it. 
            // If it already exists (duplicates), it's fine or we catch it.
            try {
                $table->index('tenant_id'); 
            } catch (\Exception $e) {}

            // Now drop the unique index
            try { 
                $table->dropUnique(['tenant_id', 'sku']); 
            } catch (\Exception $e) { /* ignore */ }
            
            // Checks for columns before dropping to avoid errors if already dropped
            if (Schema::hasColumn('products', 'sku')) $table->dropColumn('sku');
            if (Schema::hasColumn('products', 'barcode')) $table->dropColumn('barcode');
            if (Schema::hasColumn('products', 'stock_quantity')) $table->dropColumn('stock_quantity');
            if (Schema::hasColumn('products', 'cost_price')) $table->dropColumn('cost_price');
            if (Schema::hasColumn('products', 'base_price')) $table->dropColumn('base_price');
        });

        // 4. Cleanup Variants Table
        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'extra_price')) {
                 $table->dropColumn('extra_price'); 
            }
            // Make price required - might fail if nulls exist, but our migration step ensured inputs.
            $table->decimal('price', 10, 2)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this is complex and lossy (if multiple variants had diff prices, which one becomes base?)
        // Attempting partial revert
        Schema::table('products', function (Blueprint $table) {
             $table->string('sku')->nullable();
             $table->string('barcode')->nullable()->unique();
             $table->integer('stock_quantity')->default(0);
             $table->decimal('base_price', 10, 2)->default(0);
             $table->decimal('cost_price', 10, 2)->nullable();
        });

        // Restore data? (Naive approach: take first variant)
        $products = \Illuminate\Support\Facades\DB::table('products')->get();
        foreach ($products as $product) {
            $firstVariant = \Illuminate\Support\Facades\DB::table('product_variants')
                ->where('product_id', $product->id)
                ->first();
            
            if ($firstVariant) {
                \Illuminate\Support\Facades\DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'sku' => $firstVariant->sku,
                        'barcode' => $firstVariant->barcode,
                        'stock_quantity' => $firstVariant->stock_quantity,
                        'base_price' => $firstVariant->price,
                        'cost_price' => $firstVariant->cost_price,
                    ]);
            }
        }

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['price', 'cost_price']);
            $table->decimal('extra_price', 10, 2)->default(0);
        });
    }
};
