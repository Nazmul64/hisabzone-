<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TailorInventory extends Model
{
   protected $table    = 'tailor_inventory';
    protected $fillable = ['user_id', 'cloth_name', 'quantity', 'purchase_price', 'supplier', 'low_stock_threshold'];
    protected $casts    = ['purchase_price' => 'float'];
    protected $appends  = ['is_low_stock'];

    public function user() { return $this->belongsTo(User::class); }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity < $this->low_stock_threshold;
    }
}
