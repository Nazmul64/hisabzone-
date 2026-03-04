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
      Schema::create('stock_invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->unique();
            $table->string('business_name')->default('My Business');
            $table->string('business_tagline')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('admin_name')->default('Admin');
            $table->string('admin_title')->default('Administrator');
            $table->string('payment_method')->default('Cash');
            $table->text('terms')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('currency_symbol')->default('BDT');
            $table->string('invoice_prefix_sale')->default('S');
            $table->string('invoice_prefix_purchase')->default('P');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_invoice_settings');
    }
};
