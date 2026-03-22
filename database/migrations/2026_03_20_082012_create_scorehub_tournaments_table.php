<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorehub_tournaments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('entry_fee', 10, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('venue')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'completed'])->default('upcoming');
            $table->integer('overs_per_match')->default(20);
            $table->text('prize_details')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorehub_tournaments');
    }
};
