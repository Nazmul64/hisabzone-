<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Financemanage extends Model
{
    protected $fillable = [
        'amount',
        'type',
        'category_id',
        'date',
        'time',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
