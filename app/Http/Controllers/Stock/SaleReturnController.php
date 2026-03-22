<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SaleInvoice;
use App\Models\StockProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SaleReturn::forUser(Auth::id())->with('items')->orderByDesc('date');

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
            'summary' => [
                'total_returns'       => SaleReturn::forUser(Auth::id())->count(),
                'total_refund_amount' => SaleReturn::forUser(Auth::id())->sum('refund_amount'),
                'this_month'          => SaleReturn::forUser(Auth::id())->thisMonth()->sum('refund_amount'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'original_sale_id'     => 'required|exists:sale_invoices,id',
            'refund_amount'        => 'required|numeric|min:0',
            'reason'               => 'nullable|string|max:500',
            'date'                 => 'required|date',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:stock_products,id',
            'items.*.product_name' => 'required|string',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.total'        => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $ret = SaleReturn::create([
                'user_id'                 => Auth::id(),
                'sale_invoice_id'         => $v['original_sale_id'],
                'original_invoice_number' => SaleInvoice::find($v['original_sale_id'])?->invoice_number ?? '',
                'refund_amount'           => $v['refund_amount'],
                'reason'                  => $v['reason'] ?? null,
                'date'                    => $v['date'],
            ]);

            foreach ($v['items'] as $item) {
                SaleReturnItem::create([
                    'sale_return_id' => $ret->id,
                    'product_id'     => $item['product_id'],
                    'product_name'   => $item['product_name'],
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['unit_price'],
                    'total'          => $item['total'],
                ]);
                StockProduct::where('id', $item['product_id'])->increment('quantity', $item['quantity']);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data'    => $ret->load('items'),
                'message' => 'Sale return created: ' . $ret->return_invoice_number,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
{
    $ret = SaleReturn::forUser(Auth::id())->with('items')->findOrFail($id);

    $v = $request->validate([
        'original_sale_id'     => 'required|exists:sale_invoices,id',
        'refund_amount'        => 'required|numeric|min:0',
        'reason'               => 'nullable|string|max:500',
        'date'                 => 'required|date',
        'items'                => 'required|array|min:1',
        'items.*.product_id'   => 'required|exists:stock_products,id',
        'items.*.product_name' => 'required|string',
        'items.*.quantity'     => 'required|numeric|min:0.01',
        'items.*.unit_price'   => 'required|numeric|min:0',
        'items.*.total'        => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        // পুরনো stock ফেরত দাও
        foreach ($ret->items as $item) {
            StockProduct::where('id', $item->product_id)
                ->decrement('quantity', $item->quantity);
        }
        $ret->items()->delete();

        $ret->update([
            'sale_invoice_id'         => $v['original_sale_id'],
            'original_invoice_number' => SaleInvoice::find($v['original_sale_id'])?->invoice_number ?? '',
            'refund_amount'           => $v['refund_amount'],
            'reason'                  => $v['reason'] ?? null,
            'date'                    => $v['date'],
        ]);

        foreach ($v['items'] as $item) {
            SaleReturnItem::create([
                'sale_return_id' => $ret->id,
                'product_id'     => $item['product_id'],
                'product_name'   => $item['product_name'],
                'quantity'       => $item['quantity'],
                'unit_price'     => $item['unit_price'],
                'total'          => $item['total'],
            ]);
            StockProduct::where('id', $item['product_id'])
                ->increment('quantity', $item['quantity']);
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'data'    => $ret->load('items'),
            'message' => 'Sale return updated',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function destroy($id)
    {
        $ret = SaleReturn::forUser(Auth::id())->with('items')->findOrFail($id);

        DB::beginTransaction();
        try {
            foreach ($ret->items as $item) {
                StockProduct::where('id', $item->product_id)->decrement('quantity', $item->quantity);
            }
            $ret->items()->delete();
            $ret->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Sale return deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
