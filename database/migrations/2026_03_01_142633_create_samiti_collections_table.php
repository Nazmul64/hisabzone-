<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('samiti_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('samiti_member_id')->constrained()->onDelete('cascade');
            $table->integer('week_number'); // 1-4
            $table->integer('month');
            $table->integer('year');
            $table->decimal('amount', 12, 2);
            $table->boolean('is_collected')->default(false);
            $table->date('collected_date')->nullable();
            $table->timestamps();

            // Custom short name to avoid MySQL 64-char identifier limit
            $table->unique(['samiti_member_id', 'week_number', 'month', 'year'], 'sc_member_week_month_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samiti_collections');
    }
};
