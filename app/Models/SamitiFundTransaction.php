<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiFundTransaction extends Model
{
    protected $fillable = [
        'user_id', 'transaction_id', 'type', 'description',
        'amount', 'is_credit', 'reference', 'date',
    ];

    protected $casts = [
        'is_credit' => 'boolean',
        'amount'    => 'decimal:2',
        'date'      => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
