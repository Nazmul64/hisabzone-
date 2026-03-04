<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'sale_invoice_id', 'original_invoice_number',
        'return_invoice_number', 'refund_amount', 'reason', 'date',
    ];

    protected $casts = [
        'date'          => 'date',
        'refund_amount' => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function user()    { return $this->belongsTo(User::class); }
    public function invoice() { return $this->belongsTo(SaleInvoice::class, 'sale_invoice_id'); }
    public function items()   { return $this->hasMany(SaleReturnItem::class); }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeForUser($query, $userId) { return $query->where('user_id', $userId); }
    public function scopeToday($query)            { return $query->whereDate('date', today()); }
    public function scopeThisMonth($query)        { return $query->whereYear('date', now()->year)->whereMonth('date', now()->month); }

    // ── Auto Return Invoice Number ────────────────────────────────
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->return_invoice_number)) {
                $count = static::where('user_id', $model->user_id)->withTrashed()->count();
                $model->return_invoice_number = 'SR-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
