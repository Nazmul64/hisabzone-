<?php
namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class NurserySupplier extends Model
{
    use HasFactory;

    protected $table = 'nursery_suppliers';

    protected $fillable = [
        'user_id', 'name', 'products', 'phone', 'email',
        'address', 'total_purchase', 'notes',
    ];

    protected $casts = ['total_purchase' => 'float'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function purchases(): HasMany { return $this->hasMany(NurseryPurchase::class, 'nursery_supplier_id'); }
}
