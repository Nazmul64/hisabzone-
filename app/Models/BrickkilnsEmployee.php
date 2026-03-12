<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ✅ FIX: original Employee model had:
//   - missing namespace
//   - wrong fillable (was BrickProduction fields copy-pasted)
//   - `use Illuminate\Queue\Worker` instead of App\Models\Worker
//   - wrong relationships
//   - also `BrickkilnsEmployee` class was a duplicate broken copy of this

class Employee extends Model
{
    use HasFactory, SoftDeletes, UserScoped;

    protected $fillable = [
        'user_id',
        'name',
        'designation',
        'mobile',
        'address',
        'monthly_salary',
        'join_date',
        'status',
        'nid',
        'note',
    ];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
        'join_date'      => 'date',
    ];

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class, 'person_id')
                    ->where('person_type', 'employee');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়';
    }
}
