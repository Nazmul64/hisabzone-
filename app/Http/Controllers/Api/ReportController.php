<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Financemanage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // মাসিক রিপোর্ট
    // ──────────────────────────────────────────────────────────────
    public function monthly(Request $request)
    {
        try {
            $month = $request->input('month', Carbon::now()->month);
            $year  = $request->input('year',  Carbon::now()->year);

            $records = Financemanage::with('category')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date', 'desc')
                ->get();

            $income  = $records->where('type', 'income')->sum('amount');
            $expense = $records->where('type', 'expense')->sum('amount');

            // Category breakdown
            $byCategory = $records->groupBy('category_id')->map(function ($items) {
                $cat = $items->first()->category;
                return [
                    'category_name' => $cat ? $cat->name : 'অন্যান্য',
                    'category_icon' => $cat ? $cat->icon : 'category',
                    'is_expense'    => $cat ? (bool) $cat->is_expense : false,
                    'total'         => (float) $items->sum('amount'),
                    'count'         => $items->count(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data'    => [
                    'month'       => $month,
                    'year'        => $year,
                    'income'      => (float) $income,
                    'expense'     => (float) $expense,
                    'balance'     => (float) ($income - $expense),
                    'by_category' => $byCategory,
                    'transactions'=> $records->map(fn($f) => $this->formatFinance($f))->values(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // বাৎসরিক রিপোর্ট
    // ──────────────────────────────────────────────────────────────
    public function annual(Request $request)
    {
        try {
            $year = $request->input('year', Carbon::now()->year);

            $monthlyData = [];
            for ($m = 1; $m <= 12; $m++) {
                $records = Financemanage::whereMonth('date', $m)
                    ->whereYear('date', $year)
                    ->get();

                $monthlyData[] = [
                    'month'   => $m,
                    'income'  => (float) $records->where('type', 'income')->sum('amount'),
                    'expense' => (float) $records->where('type', 'expense')->sum('amount'),
                ];
            }

            $allRecords = Financemanage::whereYear('date', $year)->get();
            $totalIncome  = (float) $allRecords->where('type', 'income')->sum('amount');
            $totalExpense = (float) $allRecords->where('type', 'expense')->sum('amount');

            return response()->json([
                'success' => true,
                'data'    => [
                    'year'          => $year,
                    'total_income'  => $totalIncome,
                    'total_expense' => $totalExpense,
                    'total_balance' => $totalIncome - $totalExpense,
                    'monthly'       => $monthlyData,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // ফিল্টার করা transactions
    // ──────────────────────────────────────────────────────────────
    public function filter(Request $request)
    {
        try {
            $request->validate([
                'type'        => 'nullable|in:income,expense',
                'category_id' => 'nullable|exists:categories,id',
                'date_from'   => 'nullable|date',
                'date_to'     => 'nullable|date',
                'month'       => 'nullable|integer|between:1,12',
                'year'        => 'nullable|integer',
            ]);

            $query = Financemanage::with('category')->orderBy('date', 'desc');

            if ($request->filled('type'))        $query->where('type', $request->type);
            if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
            if ($request->filled('date_from'))   $query->whereDate('date', '>=', $request->date_from);
            if ($request->filled('date_to'))     $query->whereDate('date', '<=', $request->date_to);
            if ($request->filled('month'))       $query->whereMonth('date', $request->month);
            if ($request->filled('year'))        $query->whereYear('date', $request->year);

            $records = $query->get();
            $income  = (float) $records->where('type', 'income')->sum('amount');
            $expense = (float) $records->where('type', 'expense')->sum('amount');

            return response()->json([
                'success' => true,
                'data'    => [
                    'income'       => $income,
                    'expense'      => $expense,
                    'balance'      => $income - $expense,
                    'count'        => $records->count(),
                    'transactions' => $records->map(fn($f) => $this->formatFinance($f))->values(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // Graph data (pie/bar chart এর জন্য)
    // ──────────────────────────────────────────────────────────────
    public function graph(Request $request)
    {
        try {
            $month = $request->input('month', Carbon::now()->month);
            $year  = $request->input('year',  Carbon::now()->year);

            $records = Financemanage::with('category')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();

            // Category-wise expense breakdown (pie chart)
            $expenseByCategory = $records
                ->where('type', 'expense')
                ->groupBy('category_id')
                ->map(function ($items) {
                    $cat = $items->first()->category;
                    return [
                        'label'  => $cat ? $cat->name : 'অন্যান্য',
                        'icon'   => $cat ? $cat->icon : 'category',
                        'amount' => (float) $items->sum('amount'),
                    ];
                })->values();

            // Category-wise income breakdown
            $incomeByCategory = $records
                ->where('type', 'income')
                ->groupBy('category_id')
                ->map(function ($items) {
                    $cat = $items->first()->category;
                    return [
                        'label'  => $cat ? $cat->name : 'অন্যান্য',
                        'icon'   => $cat ? $cat->icon : 'category',
                        'amount' => (float) $items->sum('amount'),
                    ];
                })->values();

            // Daily bar chart data
            $dailyData = $records->groupBy(fn($f) => Carbon::parse($f->date)->day)
                ->map(function ($items, $day) {
                    return [
                        'day'     => $day,
                        'income'  => (float) $items->where('type', 'income')->sum('amount'),
                        'expense' => (float) $items->where('type', 'expense')->sum('amount'),
                    ];
                })->values();

            return response()->json([
                'success' => true,
                'data'    => [
                    'expense_by_category' => $expenseByCategory,
                    'income_by_category'  => $incomeByCategory,
                    'daily'               => $dailyData,
                ],
            ]);
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
