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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');         // Admin যে ভাষায় দিক
            $table->string('slug')->unique(); // ✅ এটাই translation key (food, salary, etc.)
            $table->boolean('is_expense')->default(true);
             $table->string('icon')->default('category')->nullable(); // Material icon name (Flutter-এ IconData-তে convert হয়)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
