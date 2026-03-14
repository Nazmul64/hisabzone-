<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacyCategory extends Model
{
    protected $fillable = ['user_id','name','emoji','color','is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
}
