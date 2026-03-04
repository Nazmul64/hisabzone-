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
        Schema::create('stock_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_id');
            $table->string('invoice_number');
            $table->enum('payment_type', ['sale', 'purchase']);
            $table->string('payment_method')->default('cash');
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_payments');
    }
};
