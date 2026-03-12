<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BrickProduction;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Worker;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickkilnsDashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $req): JsonResponse
    {
        $req->validate([
            'month' => 'nullable|integer|between:1,12',
            'year'  => 'nullable|integer|min:2000|max:2100',
        ]);

        $month = (int) ($req->month ?? now()->month);
        $year  = (int) ($req->year  ?? now()->year);

        $todayProduction = (int) BrickProduction::whereDate('date', today())
                                                ->sum('burned_bricks');

        $totalStock      = (int) Inventory::sum('available');

        $todaySales      = (float) Sale::whereDate('date', today())
                                       ->sum('total');

        $totalIncome     = (float) Sale::whereYear('date', $year)
                                       ->whereMonth('date', $month)
                                       ->sum('total');

        $totalExpense    = (float) Expense::whereYear('date', $year)
                                          ->whereMonth('date', $month)
                                          ->sum('amount');

        $workerCount     = (int) Worker::where('status', 'active')->count();

        $employeeCount   = (int) Employee::where('status', 'active')->count();

        $netProfit       = (float) Sale::sum('total') - (float) Expense::sum('amount');

        $totalDue        = (float) Sale::sum('due_amount');

        return $this->ok([
            'today_production' => $todayProduction,
            'total_stock'      => $totalStock,
            'today_sales'      => $todaySales,
            'total_income'     => $totalIncome,
            'total_expense'    => $totalExpense,
            'worker_count'     => $workerCount,
            'employee_count'   => $employeeCount,
            'net_profit'       => $netProfit,
            'total_due'        => $totalDue,
            'month'            => $month,
            'year'             => $year,
        ]);
    }
}
