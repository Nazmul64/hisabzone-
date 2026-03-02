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
       Schema::create('samiti_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('samiti_member_id')->constrained()->onDelete('cascade');
            $table->foreignId('samiti_meeting_id')->constrained()->onDelete('cascade');
            $table->boolean('is_present')->default(false);
            $table->timestamps();

            $table->unique(['samiti_member_id', 'samiti_meeting_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samiti_attendances');
    }
};
