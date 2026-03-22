<?php
// ════════════════════════════════════════════════════════════
//  database/migrations/xxxx_create_score_ball_entries_table.php
//  ✅ batting_team_id → scorehub_tournament_teams.id
// ════════════════════════════════════════════════════════════

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_ball_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fixture_id')
                  ->constrained('scorehub_tournament_fixtures')
                  ->onDelete('cascade');

            // ✅ scorehub_tournament_teams.id
            $table->foreignId('batting_team_id')
                  ->constrained('scorehub_tournament_teams')
                  ->onDelete('cascade');

            $table->unsignedTinyInteger('over_number');
            $table->unsignedTinyInteger('ball_number');

            $table->enum('ball_type', [
                'dot', 'one', 'two', 'three', 'four', 'six',
                'no_ball', 'bye', 'wide', 'wicket',
            ])->default('dot');

            $table->string('ball_type_bn')->nullable();
            $table->unsignedTinyInteger('runs')->default(0);
            $table->boolean('is_extra')->default(false);
            $table->boolean('is_cut')->default(false);
            $table->string('bowler_name')->nullable();
            $table->string('batsman_name')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('fixture_id');
            $table->index('batting_team_id');
            $table->index(['fixture_id', 'batting_team_id']);
            $table->index(['fixture_id', 'over_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_ball_entries');
    }
};
