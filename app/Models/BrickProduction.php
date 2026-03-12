<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrickProduction extends Model
{
    use HasFactory, UserScoped;

    protected $fillable = [
        'user_id',
        'date',
        'worker_id',
        'worker_name',
        'raw_bricks',
        'burned_bricks',
        'broken_bricks',
        'brick_type',
        'note',
    ];

    protected $casts = [
        'date'          => 'date',
        'raw_bricks'    => 'integer',
        'burned_bricks' => 'integer',
        'broken_bricks' => 'integer',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function getBrickTypeLabelAttribute(): string
    {
        return match ($this->brick_type) {
            'standard' => 'স্ট্যান্ডার্ড',
            'premium'  => 'প্রিমিয়াম',
            'export'   => 'এক্সপোর্ট',
            default    => $this->brick_type,
        };
    }

    public function getNetBricksAttribute(): int
    {
        return $this->burned_bricks - $this->broken_bricks;
    }
}
