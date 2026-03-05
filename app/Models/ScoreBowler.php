<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ScoreBowler extends Model
{
    protected $table = 'scorehub_bowlers';

    protected $fillable = [
        'team_id', 'user_id', 'name', 'overs', 'order_index',
    ];

    protected $casts = [
        'order_index' => 'integer',
    ];

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(ScoreTeam::class, 'team_id');
    }
}
