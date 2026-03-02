<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiSaving extends Model
{
      protected $fillable = [
        'user_id', 'samiti_member_id', 'amount', 'is_deposit', 'note', 'date',
    ];

    protected $casts = [
        'is_deposit' => 'boolean',
        'amount'     => 'decimal:2',
        'date'       => 'date',
    ];

    public function member() { return $this->belongsTo(SamitiMember::class, 'samiti_member_id'); }
}
