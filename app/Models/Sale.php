<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    use HasFactory, SoftDeletes, UserScoped;

    protected $fillable = [
        'user_id',
        'date',
        'customer_id',
        'customer_name',
        'quantity',
        'price_per_thousand',
        'total',
        'paid_amount',
        'due_amount',
        'status',
        'brick_type',
        'invoice_no',
        'note',
    ];

    protected $casts = [
        'date'               => 'date',
        'price_per_thousand' => 'decimal:2',
        'total'              => 'decimal:2',
        'paid_amount'        => 'decimal:2',
        'due_amount'         => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Sale $sale) {
            $sale->due_amount = $sale->total - $sale->paid_amount;
            if ($sale->due_amount <= 0) {
                $sale->due_amount = 0;
                $sale->status = 'paid';
            } elseif ($sale->paid_amount > 0) {
                $sale->status = 'partial';
            } else {
                $sale->status = 'due';
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transport(): HasOne
    {
        return $this->hasOne(Transport::class);
    }
}
