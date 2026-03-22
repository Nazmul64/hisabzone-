<?php
// ════════════════════════════════════════════════════════════
//  app/Models/SamitiFine.php
//  ✅ fine_id $fillable থেকে সরানো হয়েছে — এটাই 500 এর কারণ
//  ✅ amount cast float — Flutter এ String হিসেবে আসে না
// ════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SamitiFine extends Model
{
    protected $table = 'samiti_fines';

    // ✅ fine_id নেই — migration এও নেই
    protected $fillable = [
        'user_id',
        'samiti_member_id',
        'reason',
        'amount',
        'date',
        'is_paid',
        'paid_date',
    ];

    protected $casts = [
        'amount'    => 'float',      // ✅ decimal নয়, float — Flutter safe
        'is_paid'   => 'boolean',
        'date'      => 'date',
        'paid_date' => 'date',
    ];

    // ── Scope ────────────────────────────────────────────────
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ── Relations ────────────────────────────────────────────
    public function member(): BelongsTo
    {
        return $this->belongsTo(SamitiMember::class, 'samiti_member_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
