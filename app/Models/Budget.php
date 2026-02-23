<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['category_id', 'limit_amount', 'month', 'year'];

    protected $casts = ['limit_amount' => 'decimal:2'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
