<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tailor_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('tailor_customers')->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->string('cloth_type'); // shirt, pant, panjabi, etc.
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->date('delivery_date');
            $table->date('order_date');
            // status: pending, cutting, sewing, ironing, ready, delivered
            $table->enum('status', ['pending', 'cutting', 'sewing', 'ironing', 'ready', 'delivered'])
                  ->default('pending');
            $table->string('assigned_employee')->nullable();
            $table->json('measurements')->nullable(); // {"chest":"40", "waist":"36", ...}
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tailor_orders');
    }
};
