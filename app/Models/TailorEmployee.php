<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TailorEmployee extends Model
{
    protected $table    = 'tailor_employees';
    protected $fillable = ['user_id', 'name', 'phone', 'salary', 'role', 'assigned_orders'];
    protected $casts    = ['assigned_orders' => 'array', 'salary' => 'float'];

    public function user() { return $this->belongsTo(User::class); }

    public function activeOrderCount(): int
    {
        $ids = $this->assigned_orders ?? [];
        return count($ids);
    }
}
