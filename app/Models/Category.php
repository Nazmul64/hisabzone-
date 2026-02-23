<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ════════════════════════════════════════════════════════════════
 * Category Model
 * ════════════════════════════════════════════════════════════════
 *
 * @property int    $id
 * @property string $name       Admin যে ভাষায় দিয়েছে (fallback)
 * @property string $slug       Translation key — Flutter-এ LanguageProvider.translate(slug)
 * @property string $icon       Material icon name (Flutter-এ IconData-তে convert হয়)
 * @property bool   $is_expense true = খরচ, false = আয়
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'is_expense',
    ];

    protected $casts = [
        'is_expense' => 'boolean',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────

    /**
     * শুধু expense categories
     * Usage: Category::expense()->get()
     */
    public function scopeExpense($query)
    {
        return $query->where('is_expense', true);
    }

    /**
     * শুধু income categories
     * Usage: Category::income()->get()
     */
    public function scopeIncome($query)
    {
        return $query->where('is_expense', false);
    }
}
