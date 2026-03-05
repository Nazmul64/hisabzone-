<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ScoreExtra extends Model
{
    protected $table = 'scorehub_extras';

    protected $fillable = [
        'team_id', 'user_id', 'val', 'is_cut', 'order_index',
    ];

    protected $casts = [
        'val'         => 'integer',
        'is_cut'      => 'boolean',
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
