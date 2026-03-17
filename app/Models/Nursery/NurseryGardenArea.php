<?php

namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NurseryGardenArea extends Model
{
    use HasFactory;

    protected $table = 'nursery_garden_areas';

    protected $fillable = [
        'user_id', 'name', 'emoji', 'plant_count', 'description', 'color',
    ];

    protected $casts = [
        'plant_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
