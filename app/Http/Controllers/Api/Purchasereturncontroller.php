<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseInvoice;
use App\Models\StockProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    // GET /stock/purchase-returns
    public function index(Request $request)
    {
        $query = PurchaseReturn::forUser(Auth::id())
            ->with('items')
            ->orderByDesc('date');

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        $summary = [
            'total_returns'       => PurchaseReturn::forUser(Auth::id())->count(),
            'total_refund_amount' => PurchaseReturn::forUser(Auth::id())->sum('refund_amount'),
        ];

        return response()->json([
            'data'    => $query->get(),
            'summary' => $summary,
        ]);
    }

    // POST /stock/purchase-returns
    public function store(Request $request)
    {
        $validated = $request->validate([
            'original_purchase_id'  => 'required|exists:purchase_invoices,id',
            'refund_amount'         => 'required|numeric|min:0',
            'reason'                => 'nullable|string|max:500',
            'date'                  => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:stock_products,id',
            'items.*.product_name'  => 'required|string',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.total'         => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $purchaseReturn = PurchaseReturn::create([
                'user_id'                 => Auth::id(),
                'purchase_invoice_id'     => $validated['original_purchase_id'],
                'original_invoice_number' => PurchaseInvoice::find($validated['original_purchase_id'])?->invoice_number ?? '',
                'refund_amount'           => $validated['refund_amount'],
                'reason'                  => $validated['reason'] ?? null,
                'date'                    => $validated['date'],
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id'         => $item['product_id'],
                    'product_name'       => $item['product_name'],
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'total'              => $item['total'],
                ]);

                // Deduct stock (returned to supplier)
                $newQty = max(0, StockProduct::find($item['product_id'])?->quantity - $item['quantity']);
                StockProduct::where('id', $item['product_id'])->update(['quantity' => $newQty]);
            }

            DB::commit();

            return response()->json([
                'data'    => $purchaseReturn->load('items'),
                'message' => 'Purchase return created: ' . $purchaseReturn->return_invoice_number,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // DELETE /stock/purchase-returns/{id}
    public function destroy($id)
    {
        $ret = PurchaseReturn::forUser(Auth::id())->with('items')->findOrFail($id);

        DB::beginTransaction();
        try {
            foreach ($ret->items as $item) {
                StockProduct::where('id', $item->product_id)
                    ->increment('quantity', $item->quantity);
            }
            $ret->items()->delete();
            $ret->delete();
            DB::commit();

            return response()->json(['message' => 'Purchase return deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
