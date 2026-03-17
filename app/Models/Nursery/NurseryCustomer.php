<?php
namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class NurseryCustomer extends Model
{
    use HasFactory;

    protected $table = 'nursery_customers';

    protected $fillable = [
        'user_id', 'name', 'phone', 'email', 'address',
        'total_purchase', 'last_purchase_date', 'notes',
    ];

    protected $casts = [
        'total_purchase'     => 'float',
        'last_purchase_date' => 'date',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function sales(): HasMany { return $this->hasMany(NurserySale::class, 'nursery_customer_id'); }
    public function orders(): HasMany { return $this->hasMany(NurseryOrder::class, 'nursery_customer_id'); }
    public function deliveries(): HasMany { return $this->hasMany(NurseryDelivery::class, 'nursery_customer_id'); }
}
