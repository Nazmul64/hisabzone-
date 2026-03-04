<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\SaleInvoice;
use App\Models\PurchaseInvoice;
use App\Models\StockProduct;
use App\Models\StockParty;
use App\Models\StockExpense;
use App\Models\SaleReturn;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\Auth;

class StockDashboardController extends Controller
{
    public function index()
    {
        $uid   = Auth::id();
        $today = now()->toDateString();
        $year  = now()->year;
        $month = now()->month;

        $todaySales     = SaleInvoice::forUser($uid)->whereDate('date', $today)->get();
        $todayPurchases = PurchaseInvoice::forUser($uid)->whereDate('date', $today)->get();
        $todayExpenses  = StockExpense::forUser($uid)->whereDate('date', $today)->sum('amount');

        $monthSales     = SaleInvoice::forUser($uid)->whereYear('date', $year)->whereMonth('date', $month)->get();
        $monthPurchases = PurchaseInvoice::forUser($uid)->whereYear('date', $year)->whereMonth('date', $month)->get();

        $allSales     = SaleInvoice::forUser($uid)->with('items')->get();
        $allPurchases = PurchaseInvoice::forUser($uid)->get();
        $products     = StockProduct::forUser($uid)->active()->get();

        // Top Selling Products
        $productQty = $productRev = $productName = [];
        foreach ($allSales as $sale) {
            foreach ($sale->items as $item) {
                $productQty[$item->product_id]  = ($productQty[$item->product_id] ?? 0) + $item->quantity;
                $productRev[$item->product_id]  = ($productRev[$item->product_id] ?? 0) + $item->total;
                $productName[$item->product_id] = $item->product_name;
            }
        }
        arsort($productQty);
        $topProducts = array_map(fn($pid, $qty) => [
            'name'    => $productName[$pid] ?? '',
            'qty'     => $qty,
            'revenue' => $productRev[$pid] ?? 0,
        ], array_keys(array_slice($productQty, 0, 5, true)), array_slice($productQty, 0, 5, true));

        // Top Customers
        $custTotal = $custName = [];
        foreach ($allSales as $sale) {
            if ($sale->customer_id) {
                $custTotal[$sale->customer_id] = ($custTotal[$sale->customer_id] ?? 0) + $sale->grand_total;
                $custName[$sale->customer_id]  = $sale->customer_name;
            }
        }
        arsort($custTotal);
        $topCustomers = array_map(fn($id, $total) => [
            'name'  => $custName[$id] ?? '',
            'total' => $total,
        ], array_keys(array_slice($custTotal, 0, 5, true)), array_slice($custTotal, 0, 5, true));

        // Monthly Chart (last 6 months)
        $monthlyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $t = now()->subMonths($i);
            $mS = SaleInvoice::forUser($uid)->whereYear('date', $t->year)->whereMonth('date', $t->month)->sum('grand_total');
            $mP = PurchaseInvoice::forUser($uid)->whereYear('date', $t->year)->whereMonth('date', $t->month)->sum('grand_total');
            $monthlyChart[] = [
                'month'    => $t->format('M'),
                'sale'     => $mS,
                'purchase' => $mP,
                'profit'   => $mS - $mP,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'today' => [
                    'sale'           => $todaySales->sum('grand_total'),
                    'purchase'       => $todayPurchases->sum('grand_total'),
                    'sale_due'       => $todaySales->sum('due_amount'),
                    'purchase_due'   => $todayPurchases->sum('due_amount'),
                    'expense'        => $todayExpenses,
                    'profit'         => $todaySales->sum('grand_total') - $todayPurchases->sum('grand_total'),
                    'sale_count'     => $todaySales->count(),
                    'purchase_count' => $todayPurchases->count(),
                ],
                'this_month' => [
                    'sale'     => $monthSales->sum('grand_total'),
                    'purchase' => $monthPurchases->sum('grand_total'),
                    'sale_due' => $monthSales->sum('due_amount'),
                    'profit'   => $monthSales->sum('grand_total') - $monthPurchases->sum('grand_total'),
                ],
                'all_time' => [
                    'sale'         => $allSales->sum('grand_total'),
                    'purchase'     => $allPurchases->sum('grand_total'),
                    'sale_due'     => $allSales->sum('due_amount'),
                    'purchase_due' => $allPurchases->sum('due_amount'),
                    'profit'       => $allSales->sum('grand_total') - $allPurchases->sum('grand_total'),
                ],
                'stock' => [
                    'total_products'     => $products->count(),
                    'total_stock_qty'    => $products->sum('quantity'),
                    'stock_value'        => $products->sum(fn($p) => $p->quantity * $p->purchase_price),
                    'low_stock_count'    => $products->filter(fn($p) => $p->is_low_stock)->count(),
                    'out_of_stock_count' => $products->where('quantity', '<=', 0)->count(),
                ],
                'parties' => [
                    'total_customers' => StockParty::forUser($uid)->customers()->count(),
                    'total_suppliers' => StockParty::forUser($uid)->suppliers()->count(),
                ],
                'returns' => [
                    'sale_returns'     => SaleReturn::forUser($uid)->count(),
                    'purchase_returns' => PurchaseReturn::forUser($uid)->count(),
                ],
                'top_products'  => array_values($topProducts),
                'top_customers' => array_values($topCustomers),
                'monthly_chart' => $monthlyChart,
            ],
        ]);
    }
}
