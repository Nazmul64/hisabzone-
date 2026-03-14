<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pharmacy_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('pharmacy_suppliers')->nullOnDelete();
            $table->foreignId('medicine_id')->constrained('pharmacy_medicines')->cascadeOnDelete();
            $table->string('invoice_no', 100)->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->date('date');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pharmacy_purchases'); }
};
