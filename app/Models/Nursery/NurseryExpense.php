<?php
namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NurseryExpense extends Model
{
    use HasFactory;

    protected $table = 'nursery_expenses';

    protected $fillable = ['user_id', 'type', 'emoji', 'amount', 'date', 'color', 'notes'];

    protected $casts = [
        'amount' => 'float',
        'date'   => 'date',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
