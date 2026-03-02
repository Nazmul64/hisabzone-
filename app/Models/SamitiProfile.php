<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiProfile extends Model
{
   protected $fillable = [
        'user_id', 'name', 'reg_no', 'address', 'phone', 'email',
        'president', 'secretary', 'treasurer',
        'weekly_rate', 'loan_rate', 'max_loan_multiplier',
    ];

    protected $casts = [
        'weekly_rate' => 'decimal:2',
        'loan_rate'   => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
