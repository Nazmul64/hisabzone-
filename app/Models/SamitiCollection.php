<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiCollection extends Model
{
     protected $fillable = [
        'user_id', 'samiti_member_id', 'week_number', 'month', 'year',
        'amount', 'is_collected', 'collected_date',
    ];

    protected $casts = [
        'is_collected'   => 'boolean',
        'amount'         => 'decimal:2',
        'collected_date' => 'date',
    ];

    public function member() { return $this->belongsTo(SamitiMember::class, 'samiti_member_id'); }
}
