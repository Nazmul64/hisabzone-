<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacySale extends Model
{
    protected $fillable = [
        'user_id','customer_id','invoice_no','subtotal','discount',
        'total_amount','paid_amount','due_amount','payment_method','date','note',
    ];

    protected $casts = ['date' => 'date', 'total_amount' => 'float', 'paid_amount' => 'float', 'due_amount' => 'float'];

    public function user()     { return $this->belongsTo(User::class); }
    public function customer() { return $this->belongsTo(PharmacyCustomer::class, 'customer_id'); }
    public function items()    { return $this->hasMany(PharmacySaleItem::class, 'sale_id'); }
}
