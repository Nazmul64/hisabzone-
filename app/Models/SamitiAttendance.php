<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiAttendance extends Model
{
   protected $fillable = [
        'user_id', 'samiti_member_id', 'samiti_meeting_id', 'is_present',
    ];

    protected $casts = ['is_present' => 'boolean'];

    public function member()  { return $this->belongsTo(SamitiMember::class, 'samiti_member_id'); }
    public function meeting() { return $this->belongsTo(SamitiMeeting::class, 'samiti_meeting_id'); }
}
