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
       Schema::create('samiti_dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('samiti_member_id')->constrained()->onDelete('cascade');
            $table->string('dividend_id')->unique(); // D001...
            $table->integer('year');
            $table->decimal('total_savings', 12, 2);
            $table->decimal('dividend_percent', 8, 4)->default(0);
            $table->decimal('dividend_amount', 12, 2)->default(0);
            $table->decimal('profit_pool', 12, 2)->default(0);
            $table->boolean('is_distributed')->default(false);
            $table->date('distributed_date')->nullable();
            $table->timestamps();

            $table->unique(['samiti_member_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samiti_dividends');
    }
};
