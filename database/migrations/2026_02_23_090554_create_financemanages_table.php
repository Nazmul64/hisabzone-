<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financemanages', function (Blueprint $table) {
            $table->id();

            // ── Owner ────────────────────────────────────
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // ── Finance fields ───────────────────────────
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('type', ['income', 'expense'])->default('expense');

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->nullOnDelete();

            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financemanages');
    }
};
