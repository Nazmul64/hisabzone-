<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    protected $fillable = ['purchase_return_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'total'];
    protected $casts    = ['quantity' => 'float', 'unit_price' => 'float', 'total' => 'float'];

    public function purchaseReturn() { return $this->belongsTo(PurchaseReturn::class); }
    public function product()        { return $this->belongsTo(StockProduct::class, 'product_id'); }
}
