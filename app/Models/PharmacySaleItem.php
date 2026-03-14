<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacySaleItem extends Model
{
    protected $fillable = ['sale_id','medicine_id','medicine_name','quantity','unit_price','total_price'];
    protected $casts    = ['total_price' => 'float'];

    public function sale()     { return $this->belongsTo(PharmacySale::class); }
    public function medicine() { return $this->belongsTo(PharmacyMedicine::class, 'medicine_id'); }
}
