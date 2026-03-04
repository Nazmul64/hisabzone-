<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'code', 'category', 'unit',
        'purchase_price', 'sale_price', 'quantity',
        'low_stock_alert', 'image_url', 'description', 'is_active',
    ];

    protected $casts = [
        'purchase_price'  => 'float',
        'sale_price'      => 'float',
        'quantity'        => 'float',
        'low_stock_alert' => 'float',
        'is_active'       => 'boolean',
    ];

    public function user()             { return $this->belongsTo(User::class); }
    public function saleItems()        { return $this->hasMany(SaleItem::class, 'product_id'); }
    public function purchaseItems()    { return $this->hasMany(PurchaseItem::class, 'product_id'); }
    public function adjustments()      { return $this->hasMany(StockAdjustment::class, 'product_id'); }

    public function scopeForUser($q, $uid) { return $q->where('user_id', $uid); }
    public function scopeActive($q)        { return $q->where('is_active', true); }
    public function scopeLowStock($q)
    {
        return $q->whereNotNull('low_stock_alert')
            ->whereRaw('quantity <= low_stock_alert')
            ->where('quantity', '>', 0);
    }
    public function scopeOutOfStock($q) { return $q->where('quantity', '<=', 0); }

    public function getIsLowStockAttribute(): bool
    {
        return $this->low_stock_alert !== null && $this->quantity <= $this->low_stock_alert;
    }

    public function getStockValueAttribute(): float { return $this->quantity * $this->purchase_price; }
    public function getSaleValueAttribute(): float  { return $this->quantity * $this->sale_price; }
}
