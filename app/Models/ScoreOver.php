<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ScoreOver extends Model
{
    protected $table = 'scorehub_overs';

    protected $fillable = [
        'team_id', 'user_id', 'over_number', 'is_done',
    ];

    protected $casts = [
        'over_number' => 'integer',
        'is_done'     => 'boolean',
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
