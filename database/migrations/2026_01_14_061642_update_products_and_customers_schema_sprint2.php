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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(0)->after('cost_price');
            }
            if (!Schema::hasColumn('products', 'deleted_at')) {
                $table->softDeletes();
            }
            // Unique SKU per tenant (if not null)
            $exists = collect(DB::select("SHOW INDEXES FROM products WHERE Key_name = 'products_tenant_id_sku_unique'"))->count() > 0;
            if (!$exists) {
                 $table->unique(['tenant_id', 'sku'], 'products_tenant_id_sku_unique');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Cleanup duplicates before adding unique index
        $duplicates = DB::table('customers')
            ->select('tenant_id', 'phone', DB::raw('COUNT(*) as count'))
            ->groupBy('tenant_id', 'phone')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $keepId = DB::table('customers')
                ->where('tenant_id', $duplicate->tenant_id)
                ->where('phone', $duplicate->phone)
                ->orderBy('id')
                ->value('id');

            DB::table('customers')
                ->where('tenant_id', $duplicate->tenant_id)
                ->where('phone', $duplicate->phone)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->unique(['tenant_id', 'phone'], 'customers_tenant_id_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'deleted_at']);
            $table->dropUnique('products_tenant_id_sku_unique');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
            $table->dropUnique('customers_tenant_id_phone_unique');
        });
    }
};
