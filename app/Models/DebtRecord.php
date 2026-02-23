<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtRecord extends Model
{
    protected $fillable = ['person_name', 'amount', 'type', 'date', 'due_date', 'note', 'is_settled'];

    protected $casts = [
        'amount'     => 'decimal:2',
        'is_settled' => 'boolean',
    ];
}
