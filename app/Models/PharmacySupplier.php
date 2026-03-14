<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacySupplier extends Model
{
    protected $fillable = ['user_id','name','company','phone','email','address','total_purchase','is_active'];
    protected $casts    = ['is_active' => 'boolean', 'total_purchase' => 'float'];

    public function user()      { return $this->belongsTo(User::class); }
    public function purchases() { return $this->hasMany(PharmacyPurchase::class, 'supplier_id'); }
}
