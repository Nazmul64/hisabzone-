<?php
// ════════════════════════════════════════════════════════════
//  app/Models/ScoreBallEntry.php
//  ✅ table   : score_ball_entries
//  ✅ fillable : migration এর সাথে পুরো sync
//  ✅ ball_type_bn accessor — বাংলা লেবেল
// ════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ScoreBallEntry extends Model
{
    protected $table = 'score_ball_entries';

    protected $fillable = [
        'fixture_id',
        'batting_team_id',
        'over_number',
        'ball_number',
        'ball_type',
        'ball_type_bn',
        'runs',
        'is_extra',
        'is_cut',
        'bowler_name',
        'batsman_name',
        'note',
    ];

    protected $casts = [
        'fixture_id'      => 'integer',
        'batting_team_id' => 'integer',
        'over_number'     => 'integer',
        'ball_number'     => 'integer',
        'runs'            => 'integer',
        'is_extra'        => 'boolean',
        'is_cut'          => 'boolean',
    ];

    // Extra types — NB, বাই, ওয়াইড কাটা যাবে
    public static array $extraTypes = ['no_ball', 'bye', 'wide'];

    // রান ম্যাপিং
    public static array $runsMap = [
        'dot'     => 0,
        'one'     => 1,
        'two'     => 2,
        'three'   => 3,
        'four'    => 4,
        'six'     => 6,
        'no_ball' => 1,
        'bye'     => 1,
        'wide'    => 1,
        'wicket'  => 0,
    ];

    // ── Scopes ───────────────────────────────────────────────
    public function scopeForFixture(Builder $q, int $fixtureId): Builder
    {
        return $q->where('fixture_id', $fixtureId);
    }

    public function scopeForTeam(Builder $q, int $teamId): Builder
    {
        return $q->where('batting_team_id', $teamId);
    }

    public function scopeForOver(Builder $q, int $over): Builder
    {
        return $q->where('over_number', $over);
    }

    public function scopeExtras(Builder $q): Builder
    {
        return $q->whereIn('ball_type', self::$extraTypes)->where('is_cut', false);
    }

    public function scopeNotCut(Builder $q): Builder
    {
        return $q->where('is_cut', false);
    }

    public function scopeWickets(Builder $q): Builder
    {
        return $q->where('ball_type', 'wicket');
    }

    public function scopeByBowler(Builder $q, string $bowler): Builder
    {
        return $q->where('bowler_name', $bowler);
    }

    // ── Relations ────────────────────────────────────────────
    public function fixture(): BelongsTo
    {
        return $this->belongsTo(ScoreTournamentFixture::class, 'fixture_id');
    }

    public function battingTeam(): BelongsTo
    {
        return $this->belongsTo(ScoreTournamentTeam::class, 'batting_team_id');
    }

    // ── Accessor: বাংলা লেবেল ────────────────────────────────
    public function getBallTypeBnAttribute(): string
    {
        return match ($this->attributes['ball_type'] ?? '') {
            'dot'     => 'ডট',
            'one'     => '১',
            'two'     => '২',
            'three'   => '৩',
            'four'    => 'চার',
            'six'     => 'ছক্কা',
            'no_ball' => 'নো বল',
            'bye'     => 'বাই',
            'wide'    => 'ওয়াইড',
            'wicket'  => 'উইকেট',
            default   => $this->attributes['ball_type'] ?? '',
        };
    }

    // extra কিনা
    public function getIsExtraTypeAttribute(): bool
    {
        return in_array($this->ball_type, self::$extraTypes);
    }

    public static function runsFromType(string $type): int
    {
        return self::$runsMap[$type] ?? 0;
    }
}
