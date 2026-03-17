<?php
namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NurseryDelivery extends Model
{
    use HasFactory;

    protected $table = 'nursery_deliveries';

    protected $fillable = [
        'user_id', 'nursery_order_id', 'nursery_customer_id',
        'delivery_number', 'customer_name', 'address',
        'date', 'status', 'emoji', 'notes',
    ];

    protected $casts = ['date' => 'date'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function order(): BelongsTo { return $this->belongsTo(NurseryOrder::class, 'nursery_order_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(NurseryCustomer::class, 'nursery_customer_id'); }
}
