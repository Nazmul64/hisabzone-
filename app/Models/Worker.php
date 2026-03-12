<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    use HasFactory, SoftDeletes, UserScoped;

    protected $fillable = [
        'user_id',
        'name',
        'mobile',
        'address',
        'work_type',
        'daily_wage',
        'join_date',
        'status',
        'nid',
        'note',
    ];

    protected $casts = [
        'daily_wage' => 'decimal:2',
        'join_date'  => 'date',
    ];

    public function productions(): HasMany
    {
        return $this->hasMany(BrickProduction::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class, 'person_id')
                    ->where('person_type', 'worker');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়';
    }
}
