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
       Schema::create('samiti_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->default('আমাদের সমিতি');
            $table->string('reg_no')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('president')->nullable();
            $table->string('secretary')->nullable();
            $table->string('treasurer')->nullable();
            $table->decimal('weekly_rate', 10, 2)->default(500);
            $table->decimal('loan_rate', 5, 2)->default(10);
            $table->integer('max_loan_multiplier')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samiti_profiles');
    }
};
