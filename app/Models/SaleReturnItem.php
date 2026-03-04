<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = ['sale_return_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'total'];
    protected $casts    = ['quantity' => 'float', 'unit_price' => 'float', 'total' => 'float'];

    public function saleReturn() { return $this->belongsTo(SaleReturn::class); }
    public function product()    { return $this->belongsTo(StockProduct::class, 'product_id'); }
}
