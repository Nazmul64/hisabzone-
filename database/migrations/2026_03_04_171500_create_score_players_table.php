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
        Schema::create('scorehub_players', function (Blueprint $table) {
           $table->id();

            $table->foreignId('team_id')
                  ->constrained('scorehub_teams')
                  ->onDelete('cascade');

            // ✅ user_id: অন্য user এর player access করা যাবে না
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('name');
            $table->integer('order_index')->default(0); // সিরিয়াল নম্বর
            $table->boolean('is_out')->default(false);  // আউট হয়েছে কিনা

            // রান এন্ট্রি — JSON array
            // e.g. [4, 6, 0, 2, 1] — ০ মানে ডাক (duck out)
            // score = array sum (শুধু তথ্য, দলীয় টোটালে যায় না)
            $table->json('run_entries')->default('[]');

            $table->timestamps();

            $table->index('user_id');
            $table->index(['team_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scorehub_players');
    }
};
