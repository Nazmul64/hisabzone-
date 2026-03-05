<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TailorPayment;
use App\Models\TailorEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TailorSalaryController extends Controller
{
    // ════════════════════════════════════════
    // POST /api/tailor/salary/pay
    // ════════════════════════════════════════
    public function pay(Request $request)
    {
        $validated = $request->validate([
            'employee_id'  => 'required|exists:tailor_employees,id',
            'amount'       => 'required|numeric|min:1',
            'method'       => 'required|in:cash,bkash,nagad,bank,rocket',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        $employee = TailorEmployee::where('id', $validated['employee_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $payment = TailorPayment::create([
            'user_id'      => Auth::id(),
            'employee_id'  => $employee->id,
            'customer_id'  => null,
            'order_id'     => null,
            'amount'       => $validated['amount'],
            'method'       => $validated['method'],
            'payment_date' => $validated['payment_date'],
            'notes'        => $validated['notes'] ?? null,
            'type'         => 'salary',
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$employee->name} কে ৳{$validated['amount']} বেতন দেওয়া হয়েছে",
            'data'    => $payment->load('employee'),
        ], 201);
    }

    // ════════════════════════════════════════
    // GET /api/tailor/salary/all
    // ✅ Flutter SalaryHistoryPage এর জন্য সঠিক structure
    // ════════════════════════════════════════
    public function all()
    {
        $payments = TailorPayment::where('user_id', Auth::id())
            ->where('type', 'salary')
            ->with('employee:id,name,phone,role,salary')
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalPaid = $payments->sum('amount');

        // কর্মচারী অনুযায়ী গ্রুপ
        $grouped = $payments->groupBy('employee_id')
            ->map(function ($items) {
                $emp = $items->first()->employee;
                return [
                    'employee'   => $emp,
                    'total_paid' => (float) $items->sum('amount'),
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
                    'total_paid'      => (float) $totalPaid,
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
    // ════════════════════════════════════════
    public function history($employeeId)
    {
        $employee = TailorEmployee::where('id', $employeeId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $payments = TailorPayment::where('user_id', Auth::id())
            ->where('employee_id', $employeeId)
            ->where('type', 'salary')
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Employee salary history fetched',
            'data'    => [
                'employee'      => $employee,
                'total_paid'    => (float) $payments->sum('amount'),
                'monthly_due'   => (float) $employee->salary,
                'payment_count' => $payments->count(),
                'last_payment'  => $payments->first(),
                'payments'      => $payments,
            ],
        ]);
    }
}
