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
        Schema::create('scorehub_bowlers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')
                  ->constrained('scorehub_teams')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('name');
            $table->string('overs')->nullable();      // e.g. "৪" — শুধু তথ্য
            $table->integer('order_index')->default(0);

            $table->timestamps();
            $table->index('user_id');
            $table->index('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scorehub_bowlers');
    }
};
