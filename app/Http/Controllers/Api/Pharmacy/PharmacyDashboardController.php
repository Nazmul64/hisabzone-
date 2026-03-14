<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyExpense;
use App\Models\PharmacyMedicine;
use App\Models\PharmacyPurchase;
use App\Models\PharmacySale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $month  = $request->integer('month', now()->month);
        $year   = $request->integer('year', now()->year);

        $todaySales = PharmacySale::where('user_id', $userId)
            ->whereDate('date', today())->get();

        $monthSales = PharmacySale::where('user_id', $userId)
            ->whereMonth('date', $month)->whereYear('date', $year)->get();

        $monthPurchases = PharmacyPurchase::where('user_id', $userId)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->sum('total_amount');

        $monthExpenses = PharmacyExpense::where('user_id', $userId)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->sum('amount');

        $medicines = PharmacyMedicine::where('user_id', $userId)->get();

        $recentSales = PharmacySale::where('user_id', $userId)
            ->with('customer')->latest()->limit(5)->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'today' => [
                    'sales_count'  => $todaySales->count(),
                    'sales_amount' => round($todaySales->sum('total_amount'), 2),
                    'profit'       => round($todaySales->sum('total_amount') - $todaySales->sum('discount'), 2),
                ],
                'month' => [
                    'sales_amount'    => round($monthSales->sum('total_amount'), 2),
                    'purchase_amount' => round($monthPurchases, 2),
                    'expense_amount'  => round($monthExpenses, 2),
                    'profit'          => round($monthSales->sum('total_amount') - $monthPurchases - $monthExpenses, 2),
                ],
                'inventory' => [
                    'total_medicines' => $medicines->count(),
                    'total_stock'     => $medicines->sum('stock'),
                    'stock_value'     => round($medicines->sum(fn($m) => $m->stock * (float) $m->purchase_price), 2),
                    'low_stock_count' => PharmacyMedicine::where('user_id', $userId)->whereColumn('stock', '<', 'min_stock')->count(),
                    'expiring_soon'   => PharmacyMedicine::where('user_id', $userId)->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', now()->addDays(30))->count(),
                ],
                'recent_sales' => $recentSales,
            ],
        ]);
    }
}
