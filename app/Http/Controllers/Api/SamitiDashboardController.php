<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiMember;
use App\Models\SamitiSaving;
use App\Models\SamitiLoan;
use App\Models\SamitiCollection;
use App\Models\SamitiFine;
use App\Models\SamitiExpense;
use App\Models\SamitiFundTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiDashboardController extends Controller
{
    public function summary(Request $request)
    {
        $userId = Auth::id();

        $members      = SamitiMember::where('user_id', $userId)->get();
        $totalMembers = $members->count();

        // Total savings (net)
        $totalDeposit  = SamitiSaving::where('user_id', $userId)->where('is_deposit', true)->sum('amount');
        $totalWithdraw = SamitiSaving::where('user_id', $userId)->where('is_deposit', false)->sum('amount');
        $totalSavings  = $totalDeposit - $totalWithdraw;

        // Total loans (active/overdue)
        $totalLoans = SamitiLoan::where('user_id', $userId)
            ->where('status', '!=', 'paid')
            ->sum('loan_amount');

        // Total due
        $totalDue = SamitiLoan::where('user_id', $userId)
            ->where('status', '!=', 'paid')
            ->selectRaw('SUM(total_payable - paid_amount) as due')
            ->value('due') ?? 0;

        // Total fine (unpaid)
        $totalFine = SamitiFine::where('user_id', $userId)
            ->where('is_paid', false)
            ->sum('amount');

        // Total expense
        $totalExpense = SamitiExpense::where('user_id', $userId)->sum('amount');

        // This month collection
        $thisMonthCollect = SamitiCollection::where('user_id', $userId)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->where('is_collected', true)
            ->sum('amount');

        // Total fund balance (credit - debit)
        $totalCredit    = SamitiFundTransaction::where('user_id', $userId)->where('is_credit', true)->sum('amount');
        $totalDebitFund = SamitiFundTransaction::where('user_id', $userId)->where('is_credit', false)->sum('amount');
        $totalFund      = $totalCredit - $totalDebitFund;

        // Recent activities (last 5 savings)
        $recentActivities = SamitiSaving::where('user_id', $userId)
            ->with('member')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($s) => [
                'name'      => $s->member->name ?? '',
                'action'    => $s->is_deposit ? 'সঞ্চয় জমা' : 'উত্তোলন',
                'amount'    => ($s->is_deposit ? '+' : '-') . '৳' . number_format($s->amount, 0),
                'is_income' => $s->is_deposit,
                'time'      => $s->date?->format('d M'),
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'total_members'      => $totalMembers,
                'total_savings'      => $totalSavings,
                'total_loans'        => $totalLoans,
                'total_due'          => $totalDue,
                'total_fine'         => $totalFine,
                'total_expense'      => $totalExpense,
                'this_month_collect' => $thisMonthCollect,
                'total_fund'         => $totalFund,
                'recent_activities'  => $recentActivities,
            ],
        ]);
    }
}
