<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TailorPayment;
use App\Models\TailorEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryHistoryController extends Controller
{
    // ════════════════════════════════════════
    // GET /api/tailor/salary/history/all
    // সব কর্মচারীর সব বেতনের ইতিহাস
    // ════════════════════════════════════════
    public function allHistory()
    {
        $payments = TailorPayment::where('user_id', Auth::id())
            ->where('type', 'salary')
            ->with('employee:id,name,phone,role,salary')
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalPaid = $payments->sum('amount');

        // কর্মচারী অনুযায়ী গ্রুপ করা
        $grouped = $payments->groupBy('employee_id')->map(function ($items) {
            $emp = $items->first()->employee;
            return [
                'employee'   => $emp,
                'total_paid' => $items->sum('amount'),
                'count'      => $items->count(),
                'last_paid'  => $items->first()->payment_date,
                'payments'   => $items->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Salary history fetched',
            'data'    => [
                'summary' => [
                    'total_paid'      => $totalPaid,
                    'total_payments'  => $payments->count(),
                    'total_employees' => $grouped->count(),
                ],
                'grouped'  => $grouped,
                'payments' => $payments,
            ],
        ]);
    }

    // ════════════════════════════════════════
    // GET /api/tailor/salary/history/{employeeId}
    // নির্দিষ্ট কর্মচারীর বেতনের ইতিহাস
    // ════════════════════════════════════════
    public function employeeHistory($employeeId)
    {
        $employee = TailorEmployee::where('id', $employeeId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $payments = TailorPayment::where('user_id', Auth::id())
            ->where('employee_id', $employeeId)
            ->where('type', 'salary')
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalPaid    = $payments->sum('amount');
        $monthlyDue   = $employee->salary;
        $lastPayment  = $payments->first();

        return response()->json([
            'success' => true,
            'message' => 'Employee salary history fetched',
            'data'    => [
                'employee'     => $employee,
                'total_paid'   => $totalPaid,
                'monthly_due'  => $monthlyDue,
                'payment_count'=> $payments->count(),
                'last_payment' => $lastPayment,
                'payments'     => $payments,
            ],
        ]);
    }
}
