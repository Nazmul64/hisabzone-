<?php

// app/Models/ScoreTeam.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ScoreTeam extends Model
{
    protected $table = 'scorehub_teams';

    protected $fillable = [
        'match_id',
        'user_id',
        'name',
        'side',
        'max_run',
        'grid_cut_cells',
        'overs_count',
    ];

    protected $casts = [
        'grid_cut_cells' => 'array',   // JSON → PHP array (auto)
        'max_run'        => 'integer',
        'overs_count'    => 'integer',
    ];

    // ══════════════════════════════════════════
    //  Computed Attributes
    // ══════════════════════════════════════════

    // ✅ gridScore = কাটা ঘরের COUNT
    //    ঘরের নম্বর যোগ হয় না — শুধু count
    public function getGridScoreAttribute(): int
    {
        return count($this->grid_cut_cells ?? []);
    }

    // অতিরিক্ত রান (কাটা বাদে)
    public function getExtrasTotalAttribute(): int
    {
        return $this->extras()
                    ->where('is_cut', false)
                    ->sum('val');
    }

    // গ্র্যান্ড টোটাল = gridScore + extrasTotal
    public function getGrandTotalAttribute(): int
    {
        return $this->grid_score + $this->extras_total;
    }

    // ✅ Scope: শুধু এই user এর data
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ── Relationships ──────────────────────────
    public function match(): BelongsTo
    {
        return $this->belongsTo(ScoreMatch::class, 'match_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(ScorePlayer::class, 'team_id')
                    ->orderBy('order_index');
    }

    public function extras(): HasMany
    {
        return $this->hasMany(ScoreExtra::class, 'team_id')
                    ->orderBy('order_index');
    }

    public function overs(): HasMany
    {
        return $this->hasMany(ScoreOver::class, 'team_id')
                    ->orderBy('over_number');
    }

    public function bowlers(): HasMany
    {
        return $this->hasMany(ScoreBowler::class, 'team_id')
                    ->orderBy('order_index');
    }
}
