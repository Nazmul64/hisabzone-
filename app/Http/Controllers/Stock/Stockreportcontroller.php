<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\SaleInvoice;
use App\Models\PurchaseInvoice;
use App\Models\StockProduct;
use App\Models\StockExpense;
use App\Models\SaleReturn;
use App\Models\PurchaseReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StockReportController extends Controller
{
    private function uid() { return Auth::id(); }

    // GET /stock/reports/today
    public function today()
    {
        $date = now()->toDateString();
        return response()->json(['data' => $this->buildReport($date, $date)]);
    }

    // GET /stock/reports/weekly
    public function weekly()
    {
        $from = now()->startOfWeek()->toDateString();
        $to   = now()->endOfWeek()->toDateString();
        return response()->json(['data' => $this->buildReport($from, $to)]);
    }

    // GET /stock/reports/monthly
    public function monthly(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);
        $from  = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $to    = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        return response()->json(['data' => $this->buildReport($from, $to)]);
    }

    // GET /stock/reports/yearly
    public function yearly(Request $request)
    {
        $year = $request->get('year', now()->year);
        $from = "$year-01-01";
        $to   = "$year-12-31";

        $data = $this->buildReport($from, $to);

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $mFrom = Carbon::create($year, $m, 1)->startOfMonth()->toDateString();
            $mTo   = Carbon::create($year, $m, 1)->endOfMonth()->toDateString();

            $mSales     = SaleInvoice::forUser($this->uid())->whereBetween('date', [$mFrom, $mTo])->get();
            $mPurchases = PurchaseInvoice::forUser($this->uid())->whereBetween('date', [$mFrom, $mTo])->get();
            $mExpenses  = StockExpense::forUser($this->uid())->whereBetween('date', [$mFrom, $mTo])->sum('amount');

            $monthly[] = [
                'month'          => $m,
                'month_name'     => Carbon::create($year, $m)->format('F'),
                'total_sale'     => $mSales->sum('grand_total'),
                'total_purchase' => $mPurchases->sum('grand_total'),
                'total_expense'  => $mExpenses,
                'net_profit'     => $mSales->sum('grand_total') - $mPurchases->sum('grand_total'),
                'sale_due'       => $mSales->sum('due_amount'),
            ];
        }

        $data['monthly_breakdown'] = $monthly;
        return response()->json(['data' => $data]);
    }

    // GET /stock/reports/custom
    public function custom(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);
        return response()->json([
            'data' => $this->buildReport($request->date_from, $request->date_to),
        ]);
    }

    // ── Core Build Report ─────────────────────────────────
    private function buildReport(string $from, string $to): array
    {
        $uid = $this->uid();

        $sales           = SaleInvoice::forUser($uid)->whereBetween('date', [$from, $to])->with('items')->get();
        $purchases       = PurchaseInvoice::forUser($uid)->whereBetween('date', [$from, $to])->with('items')->get();
        $expenses        = StockExpense::forUser($uid)->whereBetween('date', [$from, $to])->get();
        $saleReturns     = SaleReturn::forUser($uid)->whereBetween('date', [$from, $to])->get();
        $purchaseReturns = PurchaseReturn::forUser($uid)->whereBetween('date', [$from, $to])->get();

        $totalSale     = $sales->sum('grand_total');
        $totalPurchase = $purchases->sum('grand_total');
        $totalExpense  = $expenses->sum('amount');

        // Gross profit per item
        $grossProfit = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $cost = StockProduct::find($item->product_id)?->purchase_price ?? 0;
                $grossProfit += ($item->unit_price - $cost) * $item->quantity;
            }
        }

        // Top selling products
        $productQty  = [];
        $productRev  = [];
        $productName = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productQty[$item->product_id]  = ($productQty[$item->product_id] ?? 0) + $item->quantity;
                $productRev[$item->product_id]  = ($productRev[$item->product_id] ?? 0) + $item->total;
                $productName[$item->product_id] = $item->product_name;
            }
        }
        arsort($productQty);
        $topProducts = array_slice(array_map(fn($pid, $qty) => [
            'product_id' => $pid,
            'name'       => $productName[$pid] ?? '—',
            'quantity'   => $qty,
            'revenue'    => $productRev[$pid] ?? 0,
        ], array_keys($productQty), $productQty), 0, 5);

        return [
            'period'                   => ['from' => $from, 'to' => $to],
            'total_sale'               => $totalSale,
            'total_purchase'           => $totalPurchase,
            'total_sale_due'           => $sales->sum('due_amount'),
            'total_purchase_due'       => $purchases->sum('due_amount'),
            'total_sale_paid'          => $sales->sum('paid_amount'),
            'total_purchase_paid'      => $purchases->sum('paid_amount'),
            'total_sale_vat'           => $sales->sum('vat_amount'),
            'total_purchase_vat'       => $purchases->sum('vat_amount'),
            'total_sale_discount'      => $sales->sum('discount'),
            'total_expense'            => $totalExpense,
            'net_profit'               => $totalSale - $totalPurchase,
            'gross_profit'             => $grossProfit,
            'net_profit_after_expense' => $totalSale - $totalPurchase - $totalExpense,
            'total_items_sold'         => $sales->flatMap->items->sum('quantity'),
            'total_items_purchased'    => $purchases->flatMap->items->sum('quantity'),
            'total_sale_invoices'      => $sales->count(),
            'total_purchase_invoices'  => $purchases->count(),
            'total_sale_returns'       => $saleReturns->count(),
            'total_purchase_returns'   => $purchaseReturns->count(),
            'sale_return_total'        => $saleReturns->sum('refund_amount'),
            'purchase_return_total'    => $purchaseReturns->sum('refund_amount'),
            'top_products'             => array_values($topProducts),
            'expense_by_category'      => $expenses->groupBy('category')->map(fn($g) => $g->sum('amount')),
        ];
    }
}
