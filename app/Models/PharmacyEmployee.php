<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacyEmployee extends Model
{
    protected $fillable = [
        'user_id','name','position','phone','salary',
        'joining_date','address','emoji','color','is_active',
    ];

    protected $casts = ['joining_date' => 'date', 'is_active' => 'boolean', 'salary' => 'float'];

    public function user() { return $this->belongsTo(User::class); }
}
