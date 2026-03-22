<?php
// ════════════════════════════════════════════════════════════
//  app/Models/ScoreTournament.php
//  ✅ table   : scorehub_tournaments
//  ✅ fillable : migration এর সাথে পুরো sync
// ════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ScoreTournament extends Model
{
    protected $table = 'scorehub_tournaments';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'entry_fee',
        'start_date',
        'end_date',
        'venue',
        'status',
        'overs_per_match',
        'prize_details',
    ];

    protected $casts = [
        'entry_fee'       => 'decimal:2',
        'start_date'      => 'date:Y-m-d',
        'end_date'        => 'date:Y-m-d',
        'overs_per_match' => 'integer',
    ];

    // ── Scopes ───────────────────────────────────────────────
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ── Relations ────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ✅ tournament_id দিয়ে — scorehub_tournament_teams তে
    public function teams(): HasMany
    {
        return $this->hasMany(ScoreTournamentTeam::class, 'tournament_id')
                    ->orderBy('order_index');
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(ScoreTournamentFixture::class, 'tournament_id')
                    ->orderBy('match_date')
                    ->orderBy('created_at');
    }

    public function pointsTable(): HasMany
    {
        return $this->hasMany(ScoreTournamentTeam::class, 'tournament_id')
                    ->orderByDesc('points')
                    ->orderByDesc('matches_won');
    }

    // ── Accessors ────────────────────────────────────────────
    public function getTeamsCountAttribute(): int
    {
        return $this->teams()->count();
    }

    public function getFixturesCountAttribute(): int
    {
        return $this->fixtures()->count();
    }
}
