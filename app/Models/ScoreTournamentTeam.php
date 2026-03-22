<?php
// ════════════════════════════════════════════════════════════
//  app/Models/ScoreTournamentTeam.php
//  ✅ table   : scorehub_tournament_teams   ← এটাই আসল ঠিক
//  ✅ fillable : migration এর সাথে পুরো sync
//  ✅ matches_draw সহ
// ════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ScoreTournamentTeam extends Model
{
    // ✅ সঠিক টেবিল নাম — migration এ create করা হয়েছে
    protected $table = 'scorehub_tournament_teams';

    protected $fillable = [
        'tournament_id',     // ✅
        'name',
        'captain',
        'contact',
        'entry_fee_paid',
        'fee_paid',
        'matches_played',
        'matches_won',
        'matches_lost',
        'matches_draw',      // ✅
        'points',
        'order_index',
    ];

    protected $casts = [
        'tournament_id'   => 'integer',
        'entry_fee_paid'  => 'decimal:2',
        'fee_paid'        => 'boolean',
        'matches_played'  => 'integer',
        'matches_won'     => 'integer',
        'matches_lost'    => 'integer',
        'matches_draw'    => 'integer',
        'points'          => 'integer',
        'order_index'     => 'integer',
    ];

    // ── Scope ────────────────────────────────────────────────
    public function scopeForTournament(Builder $query, int $tournamentId): Builder
    {
        return $query->where('tournament_id', $tournamentId);
    }

    // ── Relations ────────────────────────────────────────────
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(ScoreTournament::class, 'tournament_id');
    }

    public function fixturesAsTeamA(): HasMany
    {
        return $this->hasMany(ScoreTournamentFixture::class, 'team_a_id');
    }

    public function fixturesAsTeamB(): HasMany
    {
        return $this->hasMany(ScoreTournamentFixture::class, 'team_b_id');
    }

    public function ballEntries(): HasMany
    {
        return $this->hasMany(ScoreBallEntry::class, 'batting_team_id');
    }
}
