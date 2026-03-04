<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'invoice_id', 'invoice_number',
        'payment_type', 'payment_method', 'amount', 'notes', 'date',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'float',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function scopeForUser($query, $userId) { return $query->where('user_id', $userId); }
    public function scopeToday($query)            { return $query->whereDate('date', today()); }
    public function scopeThisMonth($query)        { return $query->whereYear('date', now()->year)->whereMonth('date', now()->month); }
}
