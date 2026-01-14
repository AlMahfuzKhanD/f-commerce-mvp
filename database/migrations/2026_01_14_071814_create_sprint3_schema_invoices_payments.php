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
        // 1. Invoices Table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number'); // E.g., INV-2024-001
            $table->date('date');
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('generated'); // generated, paid, void
            $table->timestamps();
            
            // Unique invoice number per tenant
            $table->unique(['tenant_id', 'invoice_number']);
        });

        // 2. Invoice Items Table (Snapshot)
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        // 3. Payments Table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // cash, bkash, nagad, bank
            $table->timestamp('paid_at')->useCurrent();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // 4. Update Orders Table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('status'); // unpaid, partially_paid, paid
                $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
                $table->decimal('due_amount', 10, 2)->default(0)->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_amount', 'due_amount']);
        });

        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
