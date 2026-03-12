<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory, UserScoped;

    // Laravel auto-resolves to 'inventories' — no $table override needed

    protected $fillable = [
        'user_id',
        'brick_type',
        'label',
        'emoji',
        'total',
        'sold',
        'available',
    ];

    protected $casts = [
        'total'     => 'integer',
        'sold'      => 'integer',
        'available' => 'integer',
    ];

    public function getStockPercentAttribute(): float
    {
        if ($this->total === 0) return 0.0;
        return round(($this->available / $this->total) * 100, 1);
    }

    public function getBrickTypeLabelAttribute(): string
    {
        return match ($this->brick_type) {
            'standard' => 'স্ট্যান্ডার্ড',
            'premium'  => 'প্রিমিয়াম',
            'export'   => 'এক্সপোর্ট',
            'broken'   => 'ভাঙা ইট',
            default    => $this->brick_type,
        };
    }

    public function getEmojiForTypeAttribute(): string
    {
        return match ($this->brick_type) {
            'premium'  => '⭐',
            'export'   => '📤',
            'broken'   => '💔',
            default    => '🧱',
        };
    }
}
