<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, SoftDeletes, UserScoped;

    protected $fillable = [
        'user_id',
        'name',
        'mobile',
        'address',
        'total_amount',
        'due',
        'note',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'due'          => 'decimal:2',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getTotalOrdersAttribute(): int
    {
        return $this->sales_count ?? $this->sales()->count();
    }

    public function getHasDueAttribute(): bool
    {
        return $this->due > 0;
    }
}
