<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\Worker;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickkilnSalaryController extends Controller
{
    use ApiResponse;

    public function index(Request $req): JsonResponse
    {
        $req->validate([
            'month'       => 'nullable|integer|between:1,12',
            'year'        => 'nullable|integer|min:2000|max:2100',
            'person_type' => 'nullable|in:employee,worker',
        ]);

        $q = Salary::query();

        if ($req->filled('month'))       $q->where('month_number', $req->month);
        if ($req->filled('year'))        $q->where('year_number',  $req->year);
        if ($req->filled('person_type')) $q->where('person_type',  $req->person_type);

        $salaries = $q->orderBy('person_name')->get();

        return $this->ok($salaries);
    }

    public function store(Request $req): JsonResponse
    {
        $validated = $req->validate([
            'person_type'  => 'required|in:employee,worker',
            'person_id'    => 'required|integer',
            'person_name'  => 'nullable|string|max:255',
            'designation'  => 'nullable|string|max:100',
            'month'        => 'nullable|string|max:50',
            'month_number' => 'required|integer|between:1,12',
            'year_number'  => 'required|integer|min:2000|max:2100',
            'base_salary'  => 'required|numeric|min:0',
            'bonus'        => 'nullable|numeric|min:0',
            'advance'      => 'nullable|numeric|min:0',
            'is_paid'      => 'nullable|boolean',
            'paid_date'    => 'nullable|date',
            'note'         => 'nullable|string',
        ]);

        $salary = Salary::create($validated);

        return $this->created($salary, 'বেতন এন্ট্রি তৈরি হয়েছে');
    }

    public function update(Request $req, Salary $salary): JsonResponse
    {
        $validated = $req->validate([
            'base_salary' => 'nullable|numeric|min:0',
            'bonus'       => 'nullable|numeric|min:0',
            'advance'     => 'nullable|numeric|min:0',
            'is_paid'     => 'nullable|boolean',
            'paid_date'   => 'nullable|date',
            'note'        => 'nullable|string',
        ]);

        $salary->update($validated);

        return $this->ok($salary->fresh(), 'আপডেট সফল হয়েছে');
    }

    public function destroy(Salary $salary): JsonResponse
    {
        $salary->delete();
        return $this->ok(null, 'বেতন এন্ট্রি মুছে ফেলা হয়েছে');
    }

    public function pay(Request $req, Salary $salary): JsonResponse
    {
        $salary->update([
            'is_paid'   => true,
            'paid_date' => now()->toDateString(),
        ]);

        return $this->ok($salary->fresh(), 'বেতন পরিশোধ সফল হয়েছে');
    }

    public function generate(Request $req): JsonResponse
    {
        $req->validate([
            'month_number' => 'required|integer|between:1,12',
            'year_number'  => 'required|integer|min:2000|max:2100',
        ]);

        $month  = $req->month_number;
        $year   = $req->year_number;
        $uid    = auth()->id();
        $months = ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন',
                   'জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];

        $monthName = $months[$month - 1];
        $created   = 0;

        // Employees
        $employees = Employee::where('status', 'active')->get();
        foreach ($employees as $emp) {
            $exists = Salary::withoutGlobalScope('user')
                ->where('user_id',     $uid)
                ->where('person_type', 'employee')
                ->where('person_id',   $emp->id)
                ->where('month_number', $month)
                ->where('year_number',  $year)
                ->exists();

            if (!$exists) {
                Salary::create([
                    'person_type'  => 'employee',
                    'person_id'    => $emp->id,
                    'person_name'  => $emp->name,
                    'designation'  => $emp->designation,
                    'month'        => $monthName,
                    'month_number' => $month,
                    'year_number'  => $year,
                    'base_salary'  => $emp->monthly_salary ?? 0,
                    'bonus'        => 0,
                    'advance'      => 0,
                    'is_paid'      => false,
                ]);
                $created++;
            }
        }

        // Workers
        $workers = Worker::where('status', 'active')->get();
        foreach ($workers as $worker) {
            $exists = Salary::withoutGlobalScope('user')
                ->where('user_id',     $uid)
                ->where('person_type', 'worker')
                ->where('person_id',   $worker->id)
                ->where('month_number', $month)
                ->where('year_number',  $year)
                ->exists();

            if (!$exists) {
                Salary::create([
                    'person_type'  => 'worker',
                    'person_id'    => $worker->id,
                    'person_name'  => $worker->name,
                    'designation'  => $worker->work_type ?? 'শ্রমিক',
                    'month'        => $monthName,
                    'month_number' => $month,
                    'year_number'  => $year,
                    'base_salary'  => ($worker->daily_wage ?? 0) * 26, // ~26 working days
                    'bonus'        => 0,
                    'advance'      => 0,
                    'is_paid'      => false,
                ]);
                $created++;
            }
        }

        return $this->ok(['created' => $created], "$created টি বেতন এন্ট্রি স্বয়ংক্রিয়ভাবে তৈরি হয়েছে");
    }
}
