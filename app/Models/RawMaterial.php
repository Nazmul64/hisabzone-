<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    use HasFactory, UserScoped;

    protected $fillable = [
        'user_id',
        'name',
        'unit',
        'emoji',
        'stock',
        'used',
        'unit_price',
        'low_stock_alert',
    ];

    protected $casts = [
        'stock'           => 'decimal:2',
        'used'            => 'decimal:2',
        'unit_price'      => 'decimal:2',
        'low_stock_alert' => 'decimal:2',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(RawMaterialPurchase::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->low_stock_alert;
    }

    public function getTotalPurchasedAttribute(): float
    {
        return (float) $this->purchases()->sum('quantity');
    }
}
