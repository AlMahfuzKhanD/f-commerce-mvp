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
        Schema::dropIfExists('product_variants');
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('size')->nullable(); // e.g. M, L, XL, 42, 44
            $table->string('color')->nullable(); // e.g. Red, Blue
            $table->string('sku')->unique()->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->decimal('extra_price', 10, 2)->default(0); // Price added to base product price
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
