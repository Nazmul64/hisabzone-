<?php
namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurserySale;
use App\Models\Nursery\NurseryPurchase;
use App\Models\Nursery\NurseryExpense;
use App\Models\Nursery\NurseryPlant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryReportController extends Controller
{
    // GET /api/nursery/reports/daily?date=2025-03-14
    public function daily(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $date   = $request->get('date', now()->toDateString());

        $sales     = NurserySale::where('user_id', $userId)->whereDate('date', $date)->where('status', 'completed')->get();
        $purchases = NurseryPurchase::where('user_id', $userId)->whereDate('date', $date)->where('status', 'completed')->get();
        $expenses  = NurseryExpense::where('user_id', $userId)->whereDate('date', $date)->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'date'            => $date,
                'total_sales'     => $sales->sum('total_amount'),
                'total_purchases' => $purchases->sum('total_amount'),
                'total_expenses'  => $expenses->sum('amount'),
                'profit'          => $sales->sum('total_amount') - $purchases->sum('total_amount') - $expenses->sum('amount'),
                'sales'           => $sales,
                'purchases'       => $purchases,
                'expenses'        => $expenses,
            ],
        ]);
    }

    // GET /api/nursery/reports/monthly?month=3&year=2025
    public function monthly(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $month  = $request->get('month', now()->month);
        $year   = $request->get('year',  now()->year);

        $sales     = NurserySale::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'completed')->sum('total_amount');
        $purchases = NurseryPurchase::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'completed')->sum('total_amount');
        $expenses  = NurseryExpense::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'month'     => $month,
                'year'      => $year,
                'sales'     => $sales,
                'purchases' => $purchases,
                'expenses'  => $expenses,
                'profit'    => $sales - $purchases - $expenses,
            ],
        ]);
    }

    // GET /api/nursery/reports/annual?year=2025
    public function annual(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $year   = $request->get('year', now()->year);
        $rows   = [];

        for ($m = 1; $m <= 12; $m++) {
            $s = NurserySale::where('user_id', $userId)->whereMonth('date', $m)->whereYear('date', $year)->where('status', 'completed')->sum('total_amount');
            $p = NurseryPurchase::where('user_id', $userId)->whereMonth('date', $m)->whereYear('date', $year)->where('status', 'completed')->sum('total_amount');
            $e = NurseryExpense::where('user_id', $userId)->whereMonth('date', $m)->whereYear('date', $year)->sum('amount');
            $rows[] = ['month' => $m, 'sales' => $s, 'purchases' => $p, 'expenses' => $e, 'profit' => $s - $p - $e];
        }

        return response()->json(['success' => true, 'data' => ['year' => $year, 'months' => $rows]]);
    }

    // GET /api/nursery/reports/profit-loss?date_from=2025-01-01&date_to=2025-03-31
    public function profitLoss(Request $request): JsonResponse
    {
        $userId   = $request->user()->id;
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   now()->toDateString());

        $sales     = NurserySale::where('user_id', $userId)->whereBetween('date', [$dateFrom, $dateTo])->where('status', 'completed')->sum('total_amount');
        $purchases = NurseryPurchase::where('user_id', $userId)->whereBetween('date', [$dateFrom, $dateTo])->where('status', 'completed')->sum('total_amount');
        $expenses  = NurseryExpense::where('user_id', $userId)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'date_from'       => $dateFrom,
                'date_to'         => $dateTo,
                'total_sales'     => $sales,
                'total_purchases' => $purchases,
                'total_expenses'  => $expenses,
                'net_profit'      => $sales - $purchases - $expenses,
            ],
        ]);
    }

    // GET /api/nursery/reports/stock
    public function stock(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $plants = NurseryPlant::where('user_id', $userId)->with('plantCategory')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_plants'    => $plants->count(),
                'total_stock'     => $plants->sum('quantity'),
                'low_stock_count' => $plants->filter(fn($p) => $p->quantity < $p->min_stock)->count(),
                'plants'          => $plants->map(fn($p) => [
                    'id'           => $p->id,
                    'name'         => $p->name,
                    'category'     => $p->plantCategory?->name,
                    'quantity'     => $p->quantity,
                    'min_stock'    => $p->min_stock,
                    'is_low_stock' => $p->quantity < $p->min_stock,
                    'price'        => $p->price,
                ]),
            ],
        ]);
    }
}
