<?php
// ════════════════════════════════════════════════════════════
//  database/migrations/xxxx_create_scorehub_tournament_fixtures_table.php
//  ✅ team_a_id, team_b_id → scorehub_tournament_teams.id
// ════════════════════════════════════════════════════════════

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorehub_tournament_fixtures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tournament_id')
                  ->constrained('scorehub_tournaments')
                  ->onDelete('cascade');

            // ✅ scorehub_tournament_teams.id — সঠিক
            $table->foreignId('team_a_id')
                  ->constrained('scorehub_tournament_teams')
                  ->onDelete('cascade');

            $table->foreignId('team_b_id')
                  ->constrained('scorehub_tournament_teams')
                  ->onDelete('cascade');

            // শিডিউল
            $table->date('match_date')->nullable();
            $table->time('match_time')->nullable();
            $table->string('venue')->nullable();
            $table->string('round')->nullable();

            // স্কোর
            $table->integer('team_a_score')->nullable();
            $table->integer('team_a_wickets')->nullable();
            $table->string('team_a_overs')->nullable();
            $table->integer('team_b_score')->nullable();
            $table->integer('team_b_wickets')->nullable();
            $table->string('team_b_overs')->nullable();

            // ফলাফল
            $table->foreignId('winner_team_id')
                  ->nullable()
                  ->constrained('scorehub_tournament_teams')
                  ->onDelete('set null');

            $table->text('result_summary')->nullable();

            // ম্যান অব দ্য ম্যাচ
            $table->string('man_of_match_name')->nullable();
            $table->integer('man_of_match_runs')->nullable();
            $table->integer('man_of_match_fours')->nullable();
            $table->integer('man_of_match_sixes')->nullable();

            $table->foreignId('man_of_match_team_id')
                  ->nullable()
                  ->constrained('scorehub_tournament_teams')
                  ->onDelete('set null');

            $table->enum('status', ['scheduled', 'live', 'completed'])->default('scheduled');

            $table->timestamps();

            $table->index('tournament_id');
            $table->index('team_a_id');
            $table->index('team_b_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorehub_tournament_fixtures');
    }
};
