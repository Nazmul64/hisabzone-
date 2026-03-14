<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacyCustomer extends Model
{
    protected $fillable = ['user_id','name','phone','address','total_purchase','last_visit','is_active'];
    protected $casts    = ['is_active' => 'boolean', 'last_visit' => 'date', 'total_purchase' => 'float'];

    public function user()  { return $this->belongsTo(User::class); }
    public function sales() { return $this->hasMany(PharmacySale::class, 'customer_id'); }
}
