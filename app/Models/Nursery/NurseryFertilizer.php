<?php
namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NurseryFertilizer extends Model
{
    use HasFactory;

    protected $table = 'nursery_fertilizers';

    protected $fillable = [
        'user_id', 'name', 'emoji', 'total_quantity', 'used_quantity',
        'unit', 'total_qty_value', 'used_qty_value', 'color', 'notes',
    ];

    protected $casts = [
        'total_qty_value' => 'float',
        'used_qty_value'  => 'float',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
