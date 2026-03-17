<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiLoanController extends Controller
{
    // ── GET /samiti/loans ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query  = SamitiLoan::where('user_id', $userId)->with('member');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $loans = $query->latest()->get()->map(fn($l) => [
            'id'               => $l->id,
            'samiti_member_id' => $l->samiti_member_id,   // ✅ edit এর জন্য দরকার
            'member_name'      => $l->member->name ?? '',
            'loan_id'          => $l->loan_id,
            'loan_amount'      => (float) $l->loan_amount,
            'interest_rate'    => (float) $l->interest_rate,
            'total_payable'    => (float) $l->total_payable,
            'paid_amount'      => (float) $l->paid_amount,
            'due_amount'       => (float) ($l->total_payable - $l->paid_amount),
            'purpose'          => $l->purpose,
            'issue_date'       => $l->issue_date?->format('Y-m-d'),
            'due_date'         => $l->due_date?->format('Y-m-d'),
            'status'           => $l->status,
        ]);

        $totalLoaned = (float) SamitiLoan::where('user_id', $userId)->sum('loan_amount');
        $totalPaid   = (float) SamitiLoan::where('user_id', $userId)->sum('paid_amount');
        $totalDue    = (float) (SamitiLoan::where('user_id', $userId)
            ->selectRaw('SUM(total_payable - paid_amount) as due')
            ->value('due') ?? 0);

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

    // ── POST /samiti/loans ────────────────────────────────────────────────
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

    // ── PUT /samiti/loans/{id} ─────────────────────────────────────────────
    // ✅ নতুন: এডিট করলে এই method call হবে — নতুন record হবে না
    public function update(Request $request, string $id)
    {
        $request->validate([
            'samiti_member_id' => 'required|integer',
            'loan_amount'      => 'required|numeric|min:1',
            'interest_rate'    => 'nullable|numeric|min:0|max:100',
            'purpose'          => 'nullable|string|max:255',
            'due_date'         => 'nullable|date',
        ]);

        $loan = SamitiLoan::where('user_id', Auth::id())->findOrFail($id);

        $rate         = $request->interest_rate ?? $loan->interest_rate;
        $loanAmount   = $request->loan_amount;
        $totalPayable = $loanAmount + ($loanAmount * $rate / 100);

        // paid_amount যা আছে সেটা ঠিক রেখে বাকি সব আপডেট
        $paidAmount = (float) $loan->paid_amount;
        $status     = $loan->status;

        // status recalculate
        if ($paidAmount >= $totalPayable) {
            $status = 'paid';
        } elseif ($request->due_date && now()->toDate() > \Carbon\Carbon::parse($request->due_date)->toDate()) {
            $status = 'overdue';
        } else {
            $status = 'active';
        }

        $loan->update([
            'samiti_member_id' => $request->samiti_member_id,
            'loan_amount'      => $loanAmount,
            'interest_rate'    => $rate,
            'total_payable'    => $totalPayable,
            'purpose'          => $request->purpose,
            'due_date'         => $request->due_date ?? $loan->due_date,
            'status'           => $status,
        ]);

        return response()->json(['success' => true, 'data' => $loan]);
    }

    // ── PATCH /samiti/loans/{id}/pay ──────────────────────────────────────
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

    // ── DELETE /samiti/loans/{id} ─────────────────────────────────────────
    public function destroy(string $id)
    {
        $loan = SamitiLoan::where('user_id', Auth::id())->findOrFail($id);
        $loan->delete();

        return response()->json(['success' => true, 'message' => 'Loan deleted']);
    }
}
