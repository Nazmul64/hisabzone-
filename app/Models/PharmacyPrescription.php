<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PharmacyPrescription extends Model
{
    protected $fillable = [
        'user_id','customer_id','patient_name','doctor_name',
        'date','medicines','status','note',
    ];

    protected $casts = ['date' => 'date', 'medicines' => 'array'];

    public function user()     { return $this->belongsTo(User::class); }
    public function customer() { return $this->belongsTo(PharmacyCustomer::class, 'customer_id'); }
}
