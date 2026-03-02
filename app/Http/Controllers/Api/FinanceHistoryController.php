<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Financemanage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceHistoryController extends Controller
{
    // ════════════════════════════════════════════════════════
    // GET /api/finance/history
    // সব transaction history (filter সহ)
    //
    // Query params:
    //   ?type=income|expense
    //   ?month=2026-02        (YYYY-MM)
    //   ?year=2026
    //   ?category_id=3
    //   ?limit=20
    // ════════════════════════════════════════════════════════
    public function history(Request $request)
    {
        $query = Financemanage::with('category')->latest('date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('month')) {
            $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$request->month]);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $limit    = $request->get('limit', 50);
        $finances = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'count'   => $finances->count(),
            'data'    => $finances,
        ]);
    }

    // ════════════════════════════════════════════════════════
    // GET /api/finance/summary
    // Overall summary — total income, expense, balance
    // ════════════════════════════════════════════════════════
    public function summary()
    {
        $totalIncome  = Financemanage::where('type', 'income')->sum('amount');
        $totalExpense = Financemanage::where('type', 'expense')->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        return response()->json([
            'success' => true,
            'data'    => [
                'total_income'  => round($totalIncome,  2),
                'total_expense' => round($totalExpense, 2),
                'balance'       => round($balance,      2),
                'total_records' => Financemanage::count(),
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════
    // GET /api/finance/monthly
    // Month-wise income & expense breakdown
    //
    // Query params:
    //   ?year=2026   (default: current year)
    // ════════════════════════════════════════════════════════
    public function monthly(Request $request)
    {
        $year = $request->get('year', now()->year);

        $rows = Financemanage::whereYear('date', $year)
            ->select(
                DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
                DB::raw("DATE_FORMAT(date, '%b %Y')  as month_label"),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month', 'month_label', 'type')
            ->orderBy('month')
            ->get();

        // Group করো month অনুযায়ী
        $grouped = [];
        foreach ($rows as $row) {
            $m = $row->month;
            if (!isset($grouped[$m])) {
                $grouped[$m] = [
                    'month'       => $m,
                    'month_label' => $row->month_label,
                    'income'      => 0,
                    'expense'     => 0,
                    'balance'     => 0,
                ];
            }
            $grouped[$m][$row->type] = round((float) $row->total, 2);
        }

        // balance calculate করো
        foreach ($grouped as &$item) {
            $item['balance'] = round($item['income'] - $item['expense'], 2);
        }

        return response()->json([
            'success' => true,
            'year'    => $year,
            'data'    => array_values($grouped),
        ]);
    }

    // ════════════════════════════════════════════════════════
    // GET /api/finance/category-summary
    // Category-wise income/expense breakdown
    //
    // Query params:
    //   ?type=income|expense
    //   ?month=2026-02
    //   ?year=2026
    // ════════════════════════════════════════════════════════
    public function categorySummary(Request $request)
    {
        $query = Financemanage::with('category')
            ->select(
                'category_id',
                'type',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('category_id', 'type');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('month')) {
            $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$request->month]);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        $rows = $query->get();

        $result = $rows->map(function ($row) {
            return [
                'category_id'   => $row->category_id,
                'category_name' => $row->category?->name ?? 'Uncategorized',
                'type'          => $row->type,
                'total'         => round((float) $row->total, 2),
                'count'         => $row->count,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    // ════════════════════════════════════════════════════════
    // GET /api/finance/daily
    // Last 30 days daily income & expense
    // ════════════════════════════════════════════════════════
    public function daily()
    {
        $rows = Financemanage::where('date', '>=', now()->subDays(30)->toDateString())
            ->select(
                'date',
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date', 'type')
            ->orderBy('date', 'desc')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $d = $row->date;
            if (!isset($grouped[$d])) {
                $grouped[$d] = [
                    'date'    => $d,
                    'income'  => 0,
                    'expense' => 0,
                    'balance' => 0,
                ];
            }
            $grouped[$d][$row->type] = round((float) $row->total, 2);
        }

        foreach ($grouped as &$item) {
            $item['balance'] = round($item['income'] - $item['expense'], 2);
        }

        return response()->json([
            'success' => true,
            'data'    => array_values($grouped),
        ]);
    }
}
