<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamitiMember extends Model
{
    protected $fillable = [
        'user_id', 'member_id', 'name', 'phone', 'address', 'nid', 'join_date', 'status',
    ];

    protected $casts = ['join_date' => 'date'];

    public function user()        { return $this->belongsTo(User::class); }
    public function savings()     { return $this->hasMany(SamitiSaving::class); }
    public function loans()       { return $this->hasMany(SamitiLoan::class); }
    public function collections() { return $this->hasMany(SamitiCollection::class); }
    public function fines()       { return $this->hasMany(SamitiFine::class); }
    public function dividends()   { return $this->hasMany(SamitiDividend::class); }
    public function attendances() { return $this->hasMany(SamitiAttendance::class); }

    // Virtual computed fields
    public function getTotalSavingsAttribute(): float
    {
        $dep = $this->savings()->where('is_deposit', true)->sum('amount');
        $wit = $this->savings()->where('is_deposit', false)->sum('amount');
        return $dep - $wit;
    }

    public function getTotalLoanAttribute(): float
    {
        return $this->loans()->where('status', '!=', 'paid')->sum('loan_amount');
    }

    public function getTotalDueAttribute(): float
    {
        return $this->loans()->where('status', '!=', 'paid')
            ->selectRaw('SUM(total_payable - paid_amount) as due')
            ->value('due') ?? 0;
    }
}
