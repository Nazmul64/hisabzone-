<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'product_id', 'product_name',
        'type', 'quantity', 'unit_cost', 'reason', 'date',
    ];

    protected $casts = [
        'date'      => 'date',
        'quantity'  => 'float',
        'unit_cost' => 'float',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(StockProduct::class, 'product_id'); }

    public function scopeForUser($query, $userId) { return $query->where('user_id', $userId); }
}
