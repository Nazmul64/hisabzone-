<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiNotice extends Model
{
    protected $fillable = [
        'user_id', 'notice_id', 'title', 'body', 'category', 'date', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'date'    => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
