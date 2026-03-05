<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ══════════════════════════════════════════════
//  Migration 1: scorehub_matches
//  ✅ user_id দিয়ে data isolation নিশ্চিত
//     একজন user শুধু নিজের matches দেখবে
// ══════════════════════════════════════════════

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorehub_matches', function (Blueprint $table) {
            $table->id();

            // ✅ যে user ম্যাচ তৈরি করেছে তার ID
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // user মুছলে তার সব ম্যাচও মুছবে

            $table->string('title')->nullable();       // ম্যাচের শিরোনাম (ঐচ্ছিক)
            $table->string('team_a_name');             // প্রথম দলের নাম
            $table->string('team_b_name');             // দ্বিতীয় দলের নাম
            $table->date('match_date')->nullable();    // ম্যাচের তারিখ
            $table->enum('status', ['ongoing', 'completed'])->default('ongoing');

            $table->timestamps();

            // ✅ Index: user_id দিয়ে দ্রুত query
            $table->index('user_id');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorehub_matches');
    }
};
