<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::create('nursery_purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('nursery_supplier_id')->nullable()->constrained('nursery_suppliers')->nullOnDelete();
                $table->foreignId('nursery_plant_id')->nullable()->constrained('nursery_plants')->nullOnDelete();
                $table->string('supplier_name')->nullable();
                $table->string('plant_name')->nullable();
                $table->integer('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_amount', 12, 2);
                $table->date('date');
                $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
    }
    public function down(): void { Schema::dropIfExists('nursery_purchases'); }
};
