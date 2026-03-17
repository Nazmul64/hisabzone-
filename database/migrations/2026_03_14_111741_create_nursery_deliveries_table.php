<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
         Schema::create('nursery_deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('nursery_order_id')->nullable()->constrained('nursery_orders')->nullOnDelete();
                $table->foreignId('nursery_customer_id')->nullable()->constrained('nursery_customers')->nullOnDelete();
                $table->string('delivery_number')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('address', 500)->nullable();
                $table->date('date');
                $table->enum('status', ['pending', 'in_transit', 'completed', 'cancelled'])->default('pending');
                $table->string('emoji', 10)->default('🚛');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
    }
    public function down(): void { Schema::dropIfExists('nursery_deliveries'); }
};
