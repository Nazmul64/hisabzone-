<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'join_commuity_url',
        'video_tutorial_video_url',
        'developer_portal_url',
        'photo',
        'rate_app_url',
        'email',
    ];
}
