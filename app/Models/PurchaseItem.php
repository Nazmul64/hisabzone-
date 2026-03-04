<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_invoice_id', 'product_id', 'product_name',
        'quantity', 'unit_price', 'total',
    ];

    protected $casts = [
        'quantity'   => 'float',
        'unit_price' => 'float',
        'total'      => 'float',
    ];

    public function invoice() { return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id'); }
    public function product() { return $this->belongsTo(StockProduct::class, 'product_id'); }
}
