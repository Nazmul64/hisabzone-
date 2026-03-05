<?php

// app/Models/ScoreMatch.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ScoreMatch extends Model
{
    protected $table = 'scorehub_matches';

    protected $fillable = [
        'user_id',
        'title',
        'team_a_name',
        'team_b_name',
        'match_date',
        'status',
    ];

    protected $casts = [
        'match_date' => 'date',
    ];

    // ══════════════════════════════════════════
    //  ✅ Scope: শুধু এই user এর ডাটা
    //  প্রতিটা query তে এটা ব্যবহার করা হবে
    // ══════════════════════════════════════════
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ── Relationships ──────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(ScoreTeam::class, 'match_id');
    }

    public function teamA()
    {
        return $this->hasOne(ScoreTeam::class, 'match_id')
                    ->where('side', 'a');
    }

    public function teamB()
    {
        return $this->hasOne(ScoreTeam::class, 'match_id')
                    ->where('side', 'b');
    }
}
