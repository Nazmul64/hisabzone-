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
        Schema::create('scorehub_overs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')
                  ->constrained('scorehub_teams')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->integer('over_number');           // ওভার নম্বর (1, 2, ... 20)
            $table->boolean('is_done')->default(false); // শেষ হয়েছে কিনা

            $table->timestamps();
            $table->index('user_id');
            $table->index(['team_id', 'over_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scorehub_overs');
    }
};
