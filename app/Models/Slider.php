<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title',       // Admin যে ভাষায় দিক
        'title_key',   // ✅ Translation key - Flutter এটা দিয়ে 30 ভাষায় translate করবে
                       // উদাহরণ: premium_features, financial_analysis, backup_restore
        'url',
        'photo',
         'new_photo',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
