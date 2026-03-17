<?php
namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class NurseryPlant extends Model
{
    use HasFactory;

    protected $table = 'nursery_plants';

    protected $fillable = [
        'user_id', 'nursery_plant_category_id', 'name', 'scientific_name',
        'category', 'price', 'quantity', 'min_stock', 'age', 'size',
        'emoji', 'description',
    ];

    protected $casts = [
        'price'     => 'float',
        'quantity'  => 'integer',
        'min_stock' => 'integer',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function plantCategory(): BelongsTo { return $this->belongsTo(NurseryPlantCategory::class, 'nursery_plant_category_id'); }
    public function sales(): HasMany { return $this->hasMany(NurserySale::class, 'nursery_plant_id'); }
    public function purchases(): HasMany { return $this->hasMany(NurseryPurchase::class, 'nursery_plant_id'); }
    public function careRecords(): HasMany { return $this->hasMany(NurseryPlantCareRecord::class, 'nursery_plant_id'); }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity < $this->min_stock;
    }
}
