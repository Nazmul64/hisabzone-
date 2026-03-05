<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ══════════════════════════════════════════════
//  Migration 2: scorehub_teams
//  ✅ user_id সব table এ আছে — double security
//     match_id + user_id দিয়ে verify করা হবে
// ══════════════════════════════════════════════

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorehub_teams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('match_id')
                  ->constrained('scorehub_matches')
                  ->onDelete('cascade');

            // ✅ Double security: user_id সরাসরি এখানেও
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('name');
            $table->enum('side', ['a', 'b']);           // দল ক (a) বা দল খ (b)
            $table->integer('max_run')->default(100);   // গ্রিডের মোট ঘর সংখ্যা

            // কাটা ঘরের নম্বর — JSON array
            // e.g. [1, 5, 12, 33] = ৪টা ঘর কাটা = ৪ রান
            // gridScore = count(grid_cut_cells)
            $table->json('grid_cut_cells')->default('[]');

            $table->integer('overs_count')->default(20); // মোট ওভার সংখ্যা

            $table->timestamps();

            $table->index('user_id');
            $table->index(['match_id', 'side']);
            $table->index(['user_id', 'match_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorehub_teams');
    }
};
