<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ScorePlayer extends Model
{
    protected $table = 'scorehub_players';

    protected $fillable = [
        'team_id', 'user_id', 'name',
        'order_index', 'is_out', 'run_entries',
    ];

    protected $casts = [
        'run_entries' => 'array',
        'is_out'      => 'boolean',
        'order_index' => 'integer',
    ];

    // ব্যক্তিগত স্কোর (শুধু তথ্য — দলীয় টোটালে যায় না)
    public function getScoreAttribute(): int
    {
        return array_sum($this->run_entries ?? []);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(ScoreTeam::class, 'team_id');
    }
}
