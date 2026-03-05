<?php
// app/Models/TailorCustomer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TailorCustomer extends Model
{
    use HasFactory;

    protected $table = 'tailor_customers';

    protected $fillable = [
        'user_id', 'name', 'phone', 'address', 'email', 'notes',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(TailorOrder::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany(TailorPayment::class, 'customer_id');
    }

    // Helpers
    public function totalSpent(): float
    {
        return $this->orders()->sum('price');
    }

    public function totalDue(): float
    {
        return $this->orders()->selectRaw('SUM(price - paid_amount) as due')->value('due') ?? 0;
    }
}
