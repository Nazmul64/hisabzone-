<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transport extends Model
{
    use HasFactory, UserScoped;

    protected $fillable = [
        'user_id',
        'sale_id',
        'date',
        'vehicle_no',
        'driver_name',
        'driver_mobile',
        'fare',
        'destination',
        'brick_quantity',
        'status',
        'note',
    ];

    protected $casts = [
        'date'           => 'date',
        'fare'           => 'decimal:2',
        'brick_quantity' => 'integer',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'অপেক্ষমাণ',
            'ongoing'   => 'চলমান',
            'delivered' => 'ডেলিভারি সম্পন্ন',
            default     => $this->status,
        };
    }
}
