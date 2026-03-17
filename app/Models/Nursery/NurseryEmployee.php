<?php

namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NurseryEmployee extends Model
{
    use HasFactory;

    protected $table = 'nursery_employees';

    protected $fillable = [
        'user_id', 'name', 'position', 'phone', 'email',
        'salary', 'joining_date', 'emoji', 'notes',
    ];

    protected $casts = [
        'salary'       => 'float',
        'joining_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
