<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory, UserScoped;

    protected $fillable = [
        'user_id',
        'date',
        'category',
        'description',
        'amount',
        'emoji',
        'note',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function getEmojiForCategoryAttribute(): string
    {
        return match ($this->category) {
            'শ্রমিক মজুরি' => '👷',
            'কয়লা ক্রয়'   => '⛏️',
            'মাটি খরচ'     => '🏔️',
            'পরিবহন'       => '🚛',
            'বিদ্যুৎ বিল'  => '⚡',
            default        => $this->emoji ?? '💸',
        };
    }
}
