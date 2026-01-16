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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['size', 'color']);
            $table->foreignId('size_id')->nullable()->after('product_id')->constrained('sizes')->nullOnDelete();
            $table->foreignId('color_id')->nullable()->after('size_id')->constrained('colors')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeign(['size_id']);
            $table->dropForeign(['color_id']);
            $table->dropColumn(['size_id', 'color_id']);
            $table->string('size')->nullable();
            $table->string('color')->nullable();
        });
    }
};
