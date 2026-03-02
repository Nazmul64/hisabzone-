<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiLoanController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query  = SamitiLoan::where('user_id', $userId)->with('member');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $loans = $query->latest()->get()->map(fn($l) => [
            'id'            => $l->id,
            'member_name'   => $l->member->name ?? '',
            'loan_id'       => $l->loan_id,
            'loan_amount'   => $l->loan_amount,
            'interest_rate' => $l->interest_rate,
            'total_payable' => $l->total_payable,
            'paid_amount'   => $l->paid_amount,
            'due_amount'    => $l->due_amount,
            'purpose'       => $l->purpose,
            'issue_date'    => $l->issue_date?->format('Y-m-d'),
            'due_date'      => $l->due_date?->format('Y-m-d'),
            'status'        => $l->status,
        ]);

        $totalLoaned = SamitiLoan::where('user_id', $userId)->sum('loan_amount');
        $totalPaid   = SamitiLoan::where('user_id', $userId)->sum('paid_amount');
        $totalDue    = SamitiLoan::where('user_id', $userId)
            ->selectRaw('SUM(total_payable - paid_amount) as due')
            ->value('due') ?? 0;

        return response()->json([
            'success' => true,
            'data'    => [
                'loans'        => $loans,
                'total_loaned' => $totalLoaned,
                'total_paid'   => $totalPaid,
                'total_due'    => $totalDue,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'samiti_member_id' => 'required|integer',
            'loan_amount'      => 'required|numeric|min:1',
            'interest_rate'    => 'nullable|numeric|min:0|max:100',
            'purpose'          => 'nullable|string|max:255',
            'due_date'         => 'nullable|date',
        ]);

        $userId       = Auth::id();
        $count        = SamitiLoan::where('user_id', $userId)->count();
        $loanId       = 'LN-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        $rate         = $request->interest_rate ?? 10;
        $loanAmount   = $request->loan_amount;
        $totalPayable = $loanAmount + ($loanAmount * $rate / 100);

        $loan = SamitiLoan::create([
            'user_id'          => $userId,
            'samiti_member_id' => $request->samiti_member_id,
            'loan_id'          => $loanId,
            'loan_amount'      => $loanAmount,
            'interest_rate'    => $rate,
            'total_payable'    => $totalPayable,
            'paid_amount'      => 0,
            'purpose'          => $request->purpose,
            'issue_date'       => now()->toDateString(),
            'due_date'         => $request->due_date ?? now()->addMonths(6)->toDateString(),
            'status'           => 'active',
        ]);

        return response()->json(['success' => true, 'data' => $loan], 201);
    }

    public function makePayment(Request $request, string $id)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        $loan = SamitiLoan::where('user_id', Auth::id())->findOrFail($id);
        $loan->paid_amount += $request->amount;

        if ($loan->paid_amount >= $loan->total_payable) {
            $loan->paid_amount = $loan->total_payable;
            $loan->status      = 'paid';
        } elseif (now()->toDate() > $loan->due_date) {
            $loan->status = 'overdue';
        }

        $loan->save();

        return response()->json(['success' => true, 'data' => $loan]);
    }

    public function destroy(string $id)
    {
        $loan = SamitiLoan::where('user_id', Auth::id())->findOrFail($id);
        $loan->delete();

        return response()->json(['success' => true, 'message' => 'Loan deleted']);
    }
}
