<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{TailorOrder, TailorCustomer, TailorInventory, TailorPayment};
use Illuminate\Http\Request;
class TailordashboardController extends Controller
{
    public function index(Request $request)
    {
        return $this->summary($request);
    }

    public function summary(Request $request)
    {
        $uid = $request->user()->id;

        $today        = today();
        $todayOrders  = TailorOrder::where('user_id', $uid)->whereDate('order_date', $today)->count();
        $pending      = TailorOrder::where('user_id', $uid)->where('status', '!=', 'delivered')->count();
        $completed    = TailorOrder::where('user_id', $uid)->where('status', 'delivered')->count();
        $customers    = TailorCustomer::where('user_id', $uid)->count();
        $todayDel     = TailorOrder::where('user_id', $uid)->whereDate('delivery_date', $today)->count();
        $totalIncome  = TailorPayment::where('user_id', $uid)->sum('amount');
        $totalDue     = TailorOrder::where('user_id', $uid)->selectRaw('SUM(price - paid_amount) as due')->value('due') ?? 0;
        $lowStock     = TailorInventory::where('user_id', $uid)->whereRaw('quantity < low_stock_threshold')->get();
        $pendingOrds  = TailorOrder::with('customer')
                            ->where('user_id', $uid)
                            ->where('status', '!=', 'delivered')
                            ->orderBy('delivery_date')
                            ->take(5)->get();
        $todayDelList = TailorOrder::with('customer')
                            ->where('user_id', $uid)
                            ->whereDate('delivery_date', $today)
                            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'today_orders'     => $todayOrders,
                'pending_orders'   => $pending,
                'completed_orders' => $completed,
                'total_customers'  => $customers,
                'today_delivery'   => $todayDel,
                'total_income'     => (float) $totalIncome,
                'total_due'        => (float) $totalDue,
                'low_stock_items'  => $lowStock,
                'pending_list'     => $pendingOrds,
                'today_delivery_list' => $todayDelList,
            ],
        ]);
    }
}
