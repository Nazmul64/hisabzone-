<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'invoice_number', 'date', 'supplier_id', 'supplier_name',
        'shipping_cost', 'other_cost', 'discount', 'vat_percent', 'vat_amount',
        'subtotal', 'grand_total', 'paid_amount', 'due_amount', 'notes', 'status',
    ];

    protected $casts = [
        'date'          => 'date',
        'shipping_cost' => 'float',
        'other_cost'    => 'float',
        'discount'      => 'float',
        'vat_percent'   => 'float',
        'vat_amount'    => 'float',
        'subtotal'      => 'float',
        'grand_total'   => 'float',
        'paid_amount'   => 'float',
        'due_amount'    => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function user()     { return $this->belongsTo(User::class); }
    public function supplier() { return $this->belongsTo(StockParty::class, 'supplier_id'); }
    public function items()    { return $this->hasMany(PurchaseItem::class); }
    public function payments() { return $this->hasMany(StockPayment::class, 'invoice_id', 'id'); }
    public function returns()  { return $this->hasMany(PurchaseReturn::class); }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeForUser($query, $userId)       { return $query->where('user_id', $userId); }
    public function scopeToday($query)                  { return $query->whereDate('date', today()); }
    public function scopeThisMonth($query)              { return $query->whereYear('date', now()->year)->whereMonth('date', now()->month); }
    public function scopeThisYear($query)               { return $query->whereYear('date', now()->year); }
    public function scopeDateRange($query, $from, $to)  { return $query->whereBetween('date', [$from, $to]); }

    // ── Auto Invoice Number ───────────────────────────────────────
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->invoice_number)) {
                $count = static::where('user_id', $model->user_id)->withTrashed()->count();
                $model->invoice_number = 'P-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
