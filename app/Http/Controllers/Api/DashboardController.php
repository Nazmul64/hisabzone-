<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Financemanage;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * মাসিক summary: income, expense, balance
     */
    public function summary(Request $request)
    {
        try {
            $month = $request->input('month', Carbon::now()->month);
            $year  = $request->input('year', Carbon::now()->year);

            $income = Financemanage::where('type', 'income')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            $expense = Financemanage::where('type', 'expense')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            return response()->json([
                'success' => true,
                'data'    => [
                    'income'  => (float) $income,
                    'expense' => (float) $expense,
                    'balance' => (float) ($income - $expense),
                    'month'   => $month,
                    'year'    => $year,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * সাম্প্রতিক transactions (limit সহ)
     */
    public function recentTransactions(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);

            $transactions = Financemanage::with('category')
                ->latest('date')
                ->limit($limit)
                ->get()
                ->map(fn($f) => $this->formatFinance($f));

            return response()->json(['success' => true, 'data' => $transactions]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function formatFinance($f): array
    {
        return [
            'id'          => $f->id,
            'amount'      => (float) $f->amount,
            'type'        => $f->type,
            'date'        => $f->date,
            'time'        => $f->time,
            'description' => $f->description,
            'category'    => $f->category ? [
                'id'         => $f->category->id,
                'name'       => $f->category->name,
                'slug'       => $f->category->slug,
                'icon'       => $f->category->icon,
                'is_expense' => (bool) $f->category->is_expense,
            ] : null,
        ];
    }
}
