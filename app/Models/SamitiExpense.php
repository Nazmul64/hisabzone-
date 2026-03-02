<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiExpense extends Model
{
   protected $fillable = [
        'user_id', 'expense_id', 'description', 'category',
        'amount', 'date', 'approved_by', 'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'amount'  => 'decimal:2',
        'date'    => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
