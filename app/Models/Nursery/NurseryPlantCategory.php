<?php
namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class NurseryPlantCategory extends Model
{
    use HasFactory;

    protected $table = 'nursery_plant_categories';

    protected $fillable = ['user_id', 'name', 'emoji', 'count', 'color'];

    protected $casts = ['count' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plants(): HasMany
    {
        return $this->hasMany(NurseryPlant::class, 'nursery_plant_category_id');
    }
}
