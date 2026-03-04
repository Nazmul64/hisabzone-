<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ← ADD THIS

class PurchaseReturn extends Model
{
    use SoftDeletes; // ← ADD THIS

    protected $fillable = [
        'user_id', 'purchase_invoice_id', 'original_invoice_number',
        'return_invoice_number', 'refund_amount', 'reason', 'date',
    ];

    protected $casts = [
        'date'          => 'date',
        'refund_amount' => 'float',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function invoice()    { return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id'); }
    public function items()      { return $this->hasMany(PurchaseReturnItem::class); }

    public function scopeForUser($query, $userId) { return $query->where('user_id', $userId); }
    public function scopeToday($query)            { return $query->whereDate('date', today()); }
    public function scopeThisMonth($query) {
        return $query->whereYear('date', now()->year)->whereMonth('date', now()->month);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->return_invoice_number)) {
                $count = static::where('user_id', $model->user_id)
                               ->withTrashed() // ✅ now works because SoftDeletes is used
                               ->count();
                $model->return_invoice_number = 'PR-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
