<?php
namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NurseryPurchase extends Model
{
    use HasFactory;

    protected $table = 'nursery_purchases';

    protected $fillable = [
        'user_id', 'nursery_supplier_id', 'nursery_plant_id',
        'supplier_name', 'plant_name', 'quantity',
        'unit_price', 'total_amount', 'date', 'status', 'notes',
    ];

    protected $casts = [
        'unit_price'   => 'float',
        'total_amount' => 'float',
        'quantity'     => 'integer',
        'date'         => 'date',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(NurserySupplier::class, 'nursery_supplier_id'); }
    public function plant(): BelongsTo { return $this->belongsTo(NurseryPlant::class, 'nursery_plant_id'); }
}
