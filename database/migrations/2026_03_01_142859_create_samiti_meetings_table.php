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
       Schema::create('samiti_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('meeting_id')->unique(); // M001...
            $table->string('title');
            $table->date('date');
            $table->string('time')->default('10:00 AM');
            $table->string('venue')->default('সমিতি ঘর');
            $table->text('agenda')->nullable();
            $table->text('notes')->nullable();
            $table->integer('attendees')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samiti_meetings');
    }
};
