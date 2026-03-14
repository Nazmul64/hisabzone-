<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacyPurchase extends Model
{
    protected $fillable = [
        'user_id','supplier_id','medicine_id','invoice_no',
        'quantity','unit_price','total_amount','date','status','note',
    ];

    protected $casts = ['date' => 'date', 'total_amount' => 'float'];

    public function user()     { return $this->belongsTo(User::class); }
    public function supplier() { return $this->belongsTo(PharmacySupplier::class, 'supplier_id'); }
    public function medicine() { return $this->belongsTo(PharmacyMedicine::class, 'medicine_id'); }
}
