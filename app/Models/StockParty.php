<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockParty extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'phone', 'email', 'address',
        'image_url', 'is_supplier', 'balance', 'notes',
    ];

    protected $casts = [
        'is_supplier' => 'boolean',
        'balance'     => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function user()             { return $this->belongsTo(User::class); }
    public function saleInvoices()     { return $this->hasMany(SaleInvoice::class, 'customer_id'); }
    public function purchaseInvoices() { return $this->hasMany(PurchaseInvoice::class, 'supplier_id'); }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeForUser($query, $userId) { return $query->where('user_id', $userId); }
    public function scopeCustomers($query)        { return $query->where('is_supplier', false); }
    public function scopeSuppliers($query)        { return $query->where('is_supplier', true); }

    // ── Computed Attributes ───────────────────────────────────────
    public function getTotalDueAttribute(): float
    {
        if ($this->is_supplier) {
            return $this->purchaseInvoices()->sum('due_amount');
        }
        return $this->saleInvoices()->sum('due_amount');
    }

    public function getTotalPurchaseAttribute(): float { return $this->purchaseInvoices()->sum('grand_total'); }
    public function getTotalSaleAttribute(): float     { return $this->saleInvoices()->sum('grand_total'); }
}
