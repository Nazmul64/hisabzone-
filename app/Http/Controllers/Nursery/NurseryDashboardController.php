<?php

namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryPlant;
use App\Models\Nursery\NurserySale;
use App\Models\Nursery\NurseryPurchase;
use App\Models\Nursery\NurseryOrder;
use App\Models\Nursery\NurseryDelivery;
use App\Models\Nursery\NurseryExpense;
use App\Models\Nursery\NurseryCustomer;
use App\Models\Nursery\NurseryPlantCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NurseryDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $today  = now()->toDateString();
            $month  = now()->month;
            $year   = now()->year;

            // আজকের বিক্রয়
            $todaySales = NurserySale::where('user_id', $userId)
                ->whereDate('date', $today)
                ->where('status', 'completed')
                ->sum('total_amount');

            // মোট গাছ ও কম স্টক
            $totalPlants   = NurseryPlant::where('user_id', $userId)->count();
            $lowStockCount = NurseryPlant::where('user_id', $userId)
                ->whereRaw('quantity < min_stock')->count();
            $totalStock    = NurseryPlant::where('user_id', $userId)->sum('quantity');

            // মোট গ্রাহক
            $totalCustomers = NurseryCustomer::where('user_id', $userId)->count();

            // পেন্ডিং অর্ডার
            $pendingOrders = NurseryOrder::where('user_id', $userId)
                ->where('status', 'pending')->count();

            // পেন্ডিং ডেলিভারি
            $pendingDeliveries = NurseryDelivery::where('user_id', $userId)
                ->whereIn('status', ['pending', 'in_transit'])->count();

            // মোট ক্যাটাগরি
            $totalCategories = NurseryPlantCategory::where('user_id', $userId)->count();

            // এই মাসের summary
            $monthSales = NurserySale::where('user_id', $userId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 'completed')
                ->sum('total_amount');

            $monthPurchases = NurseryPurchase::where('user_id', $userId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 'completed')
                ->sum('total_amount');

            $monthExpenses = NurseryExpense::where('user_id', $userId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            $monthProfit = $monthSales - $monthPurchases - $monthExpenses;

            // সাম্প্রতিক ৫টি বিক্রয় — safe load
            $recentSales = NurserySale::where('user_id', $userId)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'plant_name', 'customer_name', 'total_amount', 'date', 'status']);

            // কম stock গাছ
            $lowStockPlants = NurseryPlant::where('user_id', $userId)
                ->whereRaw('quantity < min_stock')
                ->orderBy('quantity')
                ->limit(5)
                ->get(['id', 'name', 'emoji', 'quantity', 'min_stock']);

            return response()->json([
                'success' => true,
                'data'    => [
                    'summary_cards' => [
                        'today_sales'     => (float) $todaySales,
                        'total_plants'    => (int) $totalPlants,
                        'total_stock'     => (int) $totalStock,
                        'total_customers' => (int) $totalCustomers,
                    ],
                    'quick_stats' => [
                        'low_stock'          => (int) $lowStockCount,
                        'pending_orders'     => (int) $pendingOrders,
                        'pending_deliveries' => (int) $pendingDeliveries,
                        'total_categories'   => (int) $totalCategories,
                    ],
                    'month_summary' => [
                        'month'     => $month,
                        'year'      => $year,
                        'sales'     => (float) $monthSales,
                        'purchases' => (float) $monthPurchases,
                        'expenses'  => (float) $monthExpenses,
                        'profit'    => (float) $monthProfit,
                    ],
                    'recent_sales'     => $recentSales,
                    'low_stock_plants' => $lowStockPlants,
                ],
                'message' => 'Dashboard data fetched successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('NurseryDashboard Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Dashboard error: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}
