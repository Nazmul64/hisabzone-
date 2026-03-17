<?php
namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NurserySale extends Model
{
    use HasFactory;

    protected $table = 'nursery_sales';

    protected $fillable = [
        'user_id', 'nursery_customer_id', 'nursery_plant_id',
        'customer_name', 'plant_name', 'quantity',
        'unit_price', 'total_amount', 'date', 'status', 'notes',
    ];

    protected $casts = [
        'unit_price'   => 'float',
        'total_amount' => 'float',
        'quantity'     => 'integer',
        'date'         => 'date',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function customer(): BelongsTo { return $this->belongsTo(NurseryCustomer::class, 'nursery_customer_id'); }
    public function plant(): BelongsTo { return $this->belongsTo(NurseryPlant::class, 'nursery_plant_id'); }
}
