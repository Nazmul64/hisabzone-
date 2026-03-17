<?php
namespace App\Models\Nursery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NurseryPlantCareRecord extends Model
{
    use HasFactory;

    protected $table = 'nursery_plant_care_records';

    protected $fillable = [
        'user_id', 'nursery_plant_id', 'plant_name',
        'care_type', 'care_type_label', 'emoji', 'date', 'note',
    ];

    protected $casts = ['date' => 'date'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function plant(): BelongsTo { return $this->belongsTo(NurseryPlant::class, 'nursery_plant_id'); }
}
