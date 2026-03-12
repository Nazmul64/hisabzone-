<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BrickProduction;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Sale;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BrickkilnReportController extends Controller
{
    use ApiResponse;

    // GET /api/brickkilns/reports/daily-production?days=7
    public function dailyProduction(Request $req): JsonResponse
    {
        $days = (int) ($req->days ?? 7);
        $data = [];

        $dayNames = ['রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহ', 'শুক্র', 'শনি'];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date   = now()->subDays($i)->toDateString();
            $amount = (int) BrickProduction::whereDate('date', $date)->sum('burned_bricks');
            $dow    = (int) now()->subDays($i)->dayOfWeek;

            $data[] = [
                'date'   => $date,
                'day'    => $dayNames[$dow],
                'amount' => $amount,
            ];
        }

        return $this->ok($data);
    }

    // GET /api/brickkilns/reports/monthly-sales?year=2025
    public function monthlySales(Request $req): JsonResponse
    {
        $year = (int) ($req->year ?? now()->year);

        $months = ['জানু','ফেব্রু','মার্চ','এপ্রিল','মে','জুন',
                   'জুলাই','আগস্ট','সেপ্টে','অক্টো','নভে','ডিসে'];

        $data = [];

        for ($m = 1; $m <= 12; $m++) {
            $income  = (float) Sale::whereYear('date', $year)->whereMonth('date', $m)->sum('total');
            $expense = (float) Expense::whereYear('date', $year)->whereMonth('date', $m)->sum('amount');

            $data[] = [
                'month'   => $months[$m - 1],
                'month_n' => $m,
                'income'  => $income,
                'expense' => $expense,
                'profit'  => $income - $expense,
            ];
        }

        return $this->ok($data);
    }

    // GET /api/brickkilns/reports/profit-loss?year=2025
    public function profitLoss(Request $req): JsonResponse
    {
        $year = (int) ($req->year ?? now()->year);

        $totalIncome  = (float) Sale::whereYear('date', $year)->sum('total');
        $totalExpense = (float) Expense::whereYear('date', $year)->sum('amount');

        $breakdown = Expense::whereYear('date', $year)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->map(fn ($v) => (float) $v);

        return $this->ok([
            'total_income'  => $totalIncome,
            'total_expense' => $totalExpense,
            'net_profit'    => $totalIncome - $totalExpense,
            'breakdown'     => $breakdown,
        ]);
    }

    // GET /api/brickkilns/reports/stock-summary
    public function stockSummary(): JsonResponse
    {
        $inventory = Inventory::orderBy('brick_type')->get();

        return $this->ok($inventory);
    }
}
