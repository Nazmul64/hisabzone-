<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ════════════════════════════════════════════════════════════
 * Financemanage Model
 * ════════════════════════════════════════════════════════════
 *
 * @property int         $id
 * @property int         $user_id
 * @property int|null    $category_id
 * @property float       $amount
 * @property string      $type          'income' | 'expense'
 * @property string|null $date          Y-m-d
 * @property string|null $time          H:i | H:i:s
 * @property string|null $description
 */
class Financemanage extends Model
{
    use HasFactory;

    protected $table = 'financemanages';

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'type',
        'date',
        'time',
        'description',
    ];

    // ✅ date cast বাদ — string হিসেবে রাখি
    // fmt() এ manual format করব
    protected $casts = [
        'amount'      => 'float',
        'category_id' => 'integer',
        'user_id'     => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
