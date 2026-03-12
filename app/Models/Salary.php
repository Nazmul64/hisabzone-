<?php

namespace App\Models;

use App\Traits\UserScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    use HasFactory, UserScoped;

    protected $fillable = [
        'user_id',
        'person_type',
        'person_id',
        'person_name',
        'designation',
        'month',
        'month_number',
        'year_number',
        'base_salary',
        'bonus',
        'advance',
        'net_salary',
        'is_paid',
        'paid_date',
        'note',
    ];

    protected $casts = [
        'base_salary'  => 'decimal:2',
        'bonus'        => 'decimal:2',
        'advance'      => 'decimal:2',
        'net_salary'   => 'decimal:2',
        'is_paid'      => 'boolean',
        'paid_date'    => 'date',
        'month_number' => 'integer',
        'year_number'  => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Salary $s) {
            $s->net_salary = ($s->base_salary ?? 0) + ($s->bonus ?? 0) - ($s->advance ?? 0);
        });
    }

    public function person(): BelongsTo
    {
        if ($this->person_type === 'employee') {
            return $this->belongsTo(Employee::class, 'person_id');
        }
        return $this->belongsTo(Worker::class, 'person_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_paid ? 'পরিশোধিত' : 'বাকি';
    }

    public function getPersonTypeLabelAttribute(): string
    {
        return $this->person_type === 'employee' ? 'কর্মচারী' : 'শ্রমিক';
    }
}
