<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacyExpense extends Model
{
    protected $fillable = ['user_id','type','emoji','amount','date','color','note'];
    protected $casts    = ['date' => 'date', 'amount' => 'float'];

    public function user() { return $this->belongsTo(User::class); }
}
