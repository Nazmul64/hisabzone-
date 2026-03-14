<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacyMedicine extends Model
{
    protected $fillable = [
        'user_id','name','generic_name','brand','category','batch_no',
        'purchase_price','selling_price','stock','min_stock',
        'expiry_date','unit','description','is_active',
    ];

    protected $casts = ['expiry_date' => 'date', 'is_active' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
}
