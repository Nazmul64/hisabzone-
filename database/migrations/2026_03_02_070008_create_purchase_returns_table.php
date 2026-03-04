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
        Schema::create('purchase_returns', function (Blueprint $table) {
           $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_invoice_id')->constrained()->onDelete('cascade');
            $table->string('original_invoice_number');
            $table->string('return_invoice_number')->unique();
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->text('reason')->nullable();
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
