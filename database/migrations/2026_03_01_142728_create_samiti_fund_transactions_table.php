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
        Schema::create('samiti_fund_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique(); // T001...
            $table->string('type'); // সঞ্চয়, কিস্তি, ঋণ, খরচ, সুদ
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->boolean('is_credit')->default(true);
            $table->string('reference')->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samiti_fund_transactions');
    }
};
