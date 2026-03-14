<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacyReturn extends Model
{
    protected $fillable = [
        'user_id','return_type','party_name',
        'medicine_name','quantity','amount','reason','date',
    ];

    protected $casts = ['date' => 'date', 'amount' => 'float'];

    public function user() { return $this->belongsTo(User::class); }
}
