<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, UserScoped;

    protected $fillable = [
        'user_id',
        'name',
        'mobile',
        'address',
        'material_type',   // Flutter app sends 'material_type'
        'emoji',
        'total_supply',
        'total_orders',
        'note',
    ];

    protected $casts = [
        'total_supply' => 'decimal:2',
        'total_orders' => 'integer',
    ];

    public function rawMaterialPurchases(): HasMany
    {
        return $this->hasMany(RawMaterialPurchase::class);
    }
}
