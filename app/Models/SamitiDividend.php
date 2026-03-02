<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiDividend extends Model
{
    protected $fillable = [
        'user_id', 'samiti_member_id', 'dividend_id', 'year',
        'total_savings', 'dividend_percent', 'dividend_amount',
        'profit_pool', 'is_distributed', 'distributed_date',
    ];

    protected $casts = [
        'is_distributed'   => 'boolean',
        'total_savings'    => 'decimal:2',
        'dividend_percent' => 'decimal:4',
        'dividend_amount'  => 'decimal:2',
        'profit_pool'      => 'decimal:2',
        'distributed_date' => 'date',
    ];

    public function member() { return $this->belongsTo(SamitiMember::class, 'samiti_member_id'); }
}
