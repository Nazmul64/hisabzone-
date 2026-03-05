<?php
// app/Models/TailorOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TailorOrder extends Model
{
    use HasFactory;

    protected $table = 'tailor_orders';

    protected $fillable = [
        'user_id', 'customer_id', 'order_number', 'cloth_type',
        'quantity', 'price', 'paid_amount', 'delivery_date',
        'order_date', 'status', 'assigned_employee',
        'measurements', 'notes',
    ];

    protected $casts = [
        'measurements'  => 'array',
        'delivery_date' => 'date',
        'order_date'    => 'date',
        'price'         => 'float',
        'paid_amount'   => 'float',
    ];

    // Appended computed attributes
    protected $appends = ['due_amount'];

    public function getDueAmountAttribute(): float
    {
        return max(0, $this->price - $this->paid_amount);
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(TailorCustomer::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany(TailorPayment::class, 'order_id');
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', '!=', 'delivered');
    }

    public function scopeDeliveredToday($query)
    {
        return $query->where('status', 'delivered')
                     ->whereDate('updated_at', today());
    }

    public function scopeDeliveryToday($query)
    {
        return $query->whereDate('delivery_date', today());
    }

    // Generate unique order number
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . date('Ymd') . '-';
        $last = self::where('order_number', 'like', $prefix . '%')
                    ->orderByDesc('id')->first();

        $seq = $last ? ((int) substr($last->order_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
