<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    protected $fillable = ['title', 'target_amount', 'saved_amount', 'deadline', 'note'];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'saved_amount'  => 'decimal:2',
    ];
}
