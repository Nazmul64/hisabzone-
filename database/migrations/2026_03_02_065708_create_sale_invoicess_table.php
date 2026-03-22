<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. sale_invoices ──────────────────────────────────────
       Schema::create('sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->date('date');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('stock_parties')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('other_cost',    12, 2)->default(0);
            $table->decimal('discount',      12, 2)->default(0);
            $table->decimal('vat_percent',    8, 2)->default(0);
            $table->decimal('vat_amount',    12, 2)->default(0);
            $table->decimal('subtotal',      12, 2)->default(0);
            $table->decimal('grand_total',   12, 2)->default(0);
            $table->decimal('paid_amount',   12, 2)->default(0);
            $table->decimal('due_amount',    12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'status']);
        });

        // ── 2. sale_items ─────────────────────────────────────────

    }

    public function down(): void
    {
        Schema::dropIfExists('sale_invoices');
    }
};
