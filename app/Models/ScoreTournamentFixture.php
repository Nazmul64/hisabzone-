<?php
// ════════════════════════════════════════════════════════════
//  app/Models/ScoreTournamentFixture.php
//  ✅ table   : scorehub_tournament_fixtures
//  ✅ fillable : migration + Flutter API এর সাথে sync
//
//  Flutter updateFixture() পাঠায়:
//    team_a_score, team_b_score, team_a_wickets, team_b_wickets
//    team_a_overs, team_b_overs, winner_id
//    mom_player_name, mom_runs, mom_fours, mom_sixes, mom_team_id
//    result_summary, status, match_date, match_time, venue, round
// ════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ScoreTournamentFixture extends Model
{
    protected $table = 'scorehub_tournament_fixtures';

    protected $fillable = [
        'tournament_id',
        'team_a_id',
        'team_b_id',

        // শিডিউল
        'match_date',
        'match_time',
        'venue',
        'round',

        // স্কোর
        'team_a_score',
        'team_a_wickets',
        'team_a_overs',
        'team_b_score',
        'team_b_wickets',
        'team_b_overs',

        // ফলাফল
        'winner_team_id',
        'result_summary',
        'status',

        // ম্যান অব দ্য ম্যাচ — migration column নাম অনুযায়ী
        'man_of_match_name',      // ✅ 'man_of_match' নয়
        'man_of_match_runs',
        'man_of_match_fours',
        'man_of_match_sixes',
        'man_of_match_team_id',
    ];

    protected $casts = [
        'match_date'           => 'date:Y-m-d',
        'team_a_score'         => 'integer',
        'team_b_score'         => 'integer',
        'team_a_wickets'       => 'integer',
        'team_b_wickets'       => 'integer',
        'man_of_match_runs'    => 'integer',
        'man_of_match_fours'   => 'integer',
        'man_of_match_sixes'   => 'integer',
    ];

    // ── Scope ────────────────────────────────────────────────
    public function scopeForTournament(Builder $query, int $tournamentId): Builder
    {
        return $query->where('tournament_id', $tournamentId);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    // ── Relations ────────────────────────────────────────────
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(ScoreTournament::class, 'tournament_id');
    }

    public function teamA(): BelongsTo
    {
        return $this->belongsTo(ScoreTournamentTeam::class, 'team_a_id');
    }

    public function teamB(): BelongsTo
    {
        return $this->belongsTo(ScoreTournamentTeam::class, 'team_b_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(ScoreTournamentTeam::class, 'winner_team_id');
    }

    public function momTeam(): BelongsTo
    {
        return $this->belongsTo(ScoreTournamentTeam::class, 'man_of_match_team_id');
    }

    // বল এন্ট্রি
    public function ballEntries(): HasMany
    {
        return $this->hasMany(ScoreBallEntry::class, 'fixture_id');
    }

    // ── Helpers ──────────────────────────────────────────────
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsScheduledAttribute(): bool
    {
        return $this->status === 'scheduled';
    }

    public function getTeamANameAttribute(): string
    {
        return $this->teamA?->name ?? '';
    }

    public function getTeamBNameAttribute(): string
    {
        return $this->teamB?->name ?? '';
    }

    public function getWinnerNameAttribute(): ?string
    {
        return $this->winner?->name;
    }
}
