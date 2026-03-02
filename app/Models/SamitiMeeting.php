<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiMeeting extends Model
{
    protected $fillable = [
        'user_id', 'meeting_id', 'title', 'date', 'time', 'venue', 'agenda', 'notes', 'attendees',
    ];

    protected $casts = ['date' => 'date'];

    public function user()       { return $this->belongsTo(User::class); }
    public function attendances(){ return $this->hasMany(SamitiAttendance::class); }
}
