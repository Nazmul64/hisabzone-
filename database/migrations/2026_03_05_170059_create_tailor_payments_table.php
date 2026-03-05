<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tailor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // nullable — salary payment এ real order নেই
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')
                  ->references('id')
                  ->on('tailor_orders')
                  ->onDelete('set null');

            // nullable — salary payment এ customer নেই
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('tailor_customers')
                  ->onDelete('set null');

            // nullable — order payment এ employee নেই
            // ✅ ->after() সরানো হয়েছে — Schema::create() তে কাজ করে না
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('tailor_employees')
                  ->onDelete('set null');

            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'bkash', 'nagad', 'bank', 'rocket'])->default('cash');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->string('type')->default('order'); // 'order' বা 'salary'

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tailor_payments');
    }
};
