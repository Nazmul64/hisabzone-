<?php
// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property string $name        Admin দেওয়া নাম (fallback)
 * @property string $slug        Translation key — Flutter এ lang.translate(slug)
 * @property string $icon        Material icon name
 * @property bool   $is_expense  true = ব্যয়, false = আয়
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'is_expense',
        'user_id',
    ];

    protected $casts = [
        'is_expense' => 'boolean',
    ];

    // ── Scopes ────────────────────────────────────
    public function scopeExpense($query)
    {
        return $query->where('is_expense', true);
    }

    public function scopeIncome($query)
    {
        return $query->where('is_expense', false);
    }
}
