<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TailorDealer extends Model
{
     protected $table    = 'tailor_dealers';
    protected $fillable = ['user_id', 'name', 'phone', 'address', 'products', 'total_purchase'];
    protected $casts    = ['total_purchase' => 'float'];

    public function user() { return $this->belongsTo(User::class); }
}
