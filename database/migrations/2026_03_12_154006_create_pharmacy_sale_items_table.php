<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pharmacy_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('pharmacy_sales')->cascadeOnDelete();
            $table->foreignId('medicine_id')->nullable()->constrained('pharmacy_medicines')->nullOnDelete();
            $table->string('medicine_name');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pharmacy_sale_items'); }
};
