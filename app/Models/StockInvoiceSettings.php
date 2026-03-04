<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockInvoiceSettings extends Model
{
    protected $table    = 'stock_invoice_settings';

    protected $fillable = [
        'user_id', 'business_name', 'business_tagline', 'phone', 'email',
        'address', 'admin_name', 'admin_title', 'payment_method', 'terms',
        'logo_url', 'currency_symbol', 'invoice_prefix_sale', 'invoice_prefix_purchase',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
