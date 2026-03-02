<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiFine extends Model
{
    protected $fillable = [
        'user_id', 'samiti_member_id', 'fine_id', 'reason',
        'amount', 'date', 'is_paid', 'paid_date',
    ];

    protected $casts = [
        'is_paid'   => 'boolean',
        'amount'    => 'decimal:2',
        'date'      => 'date',
        'paid_date' => 'date',
    ];

    public function member() { return $this->belongsTo(SamitiMember::class, 'samiti_member_id'); }
}
