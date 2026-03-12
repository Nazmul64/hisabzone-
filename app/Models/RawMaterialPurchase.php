<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawMaterialPurchase extends Model
{
    use HasFactory, UserScoped;

    protected $fillable = [
        'user_id',
        'raw_material_id',
        'supplier_id',
        'supplier_name',
        'date',
        'quantity',
        'unit_price',
        'total_cost',
        'note',
    ];

    protected $casts = [
        'date'       => 'date',
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
