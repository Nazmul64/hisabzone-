<?php
// ════════════════════════════════════════════════════════════
//  database/migrations/xxxx_create_scorehub_tournament_teams_table.php
//  ✅ table name: scorehub_tournament_teams
//  ✅ tournament_id → scorehub_tournaments.id
// ════════════════════════════════════════════════════════════

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorehub_tournament_teams', function (Blueprint $table) {
            $table->id();

            // ✅ scorehub_tournaments.id — এই foreign key সঠিক
            $table->foreignId('tournament_id')
                  ->constrained('scorehub_tournaments')
                  ->onDelete('cascade');

            $table->string('name');
            $table->string('captain')->nullable();
            $table->string('contact')->nullable();
            $table->decimal('entry_fee_paid', 10, 2)->default(0);
            $table->boolean('fee_paid')->default(false);

            // পয়েন্ট টেবিল
            $table->integer('matches_played')->default(0);
            $table->integer('matches_won')->default(0);
            $table->integer('matches_lost')->default(0);
            $table->integer('matches_draw')->default(0);
            $table->integer('points')->default(0);
            $table->integer('order_index')->default(0);

            $table->timestamps();

            $table->index('tournament_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorehub_tournament_teams');
    }
};
