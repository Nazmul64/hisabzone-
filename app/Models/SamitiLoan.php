<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiLoan extends Model
{
     protected $fillable = [
        'user_id', 'samiti_member_id', 'loan_id', 'loan_amount', 'interest_rate',
        'total_payable', 'paid_amount', 'purpose', 'issue_date', 'due_date', 'status',
    ];

    protected $casts = [
        'loan_amount'   => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_payable' => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'issue_date'    => 'date',
        'due_date'      => 'date',
    ];

    public function member() { return $this->belongsTo(SamitiMember::class, 'samiti_member_id'); }

    public function getDueAmountAttribute(): float
    {
        return $this->total_payable - $this->paid_amount;
    }
}
