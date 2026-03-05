<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TailorPayment extends Model
{
    protected $table = 'tailor_payments';

    protected $fillable = [
        'user_id',
        'order_id',      // nullable — salary payment এ null
        'customer_id',   // nullable — salary payment এ null
        'employee_id',   // ✅ nullable — salary payment এ employee থাকে
        'amount',
        'method',
        'payment_date',
        'notes',
        'type',          // 'order' বা 'salary'
    ];

    protected $casts = [
        'amount'       => 'float',
        'payment_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(TailorOrder::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(TailorCustomer::class, 'customer_id');
    }

    // ✅ salary payment এ employee
    public function employee()
    {
        return $this->belongsTo(TailorEmployee::class, 'employee_id');
    }

    // ✅ salary payment কিনা চেক করার helper
    public function isSalary(): bool
    {
        return $this->type === 'salary';
    }
}
