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
    // ────────────────────────────────────────────────
    // GET /stock/purchase-returns
    // ────────────────────────────────────────────────
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = PurchaseReturn::where('user_id', $userId)
            ->with(['items', 'invoice.supplier'])   // eager-load supplier name
            ->orderByDesc('date');

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        $returns = $query->get()->map(function ($ret) {
            return [
                'id'                      => $ret->id,
                'original_invoice_number' => $ret->original_invoice_number,
                'return_invoice_number'   => $ret->return_invoice_number,
                'refund_amount'           => $ret->refund_amount,
                'reason'                  => $ret->reason,
                'date'                    => $ret->date?->toDateString(),
                'supplier_name'           => $ret->invoice?->supplier?->name
                                            ?? $ret->invoice?->supplier_name
                                            ?? null,
                'items'                   => $ret->items,
            ];
        });

        $summary = [
            'total_returns'       => PurchaseReturn::where('user_id', $userId)->count(),
            'total_refund_amount' => PurchaseReturn::where('user_id', $userId)->sum('refund_amount'),
        ];

        return response()->json([
            'success' => true,
            'data'    => $returns,
            'summary' => $summary,
        ]);
    }

    // ────────────────────────────────────────────────
    // POST /stock/purchase-returns
    // ────────────────────────────────────────────────
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

        $userId   = Auth::id();
        $purchase = PurchaseInvoice::where('id', $validated['original_purchase_id'])
                        ->where('user_id', $userId)
                        ->firstOrFail();

        DB::beginTransaction();
        try {
            $purchaseReturn = PurchaseReturn::create([
                'user_id'                 => $userId,
                'purchase_invoice_id'     => $validated['original_purchase_id'],
                'original_invoice_number' => $purchase->invoice_number ?? '',
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

                // Deduct stock (goods returned to supplier)
                StockProduct::where('id', $item['product_id'])
                    ->where('user_id', $userId)
                    ->decrement('quantity', $item['quantity']);

                // Clamp to 0 — never go negative
                StockProduct::where('id', $item['product_id'])
                    ->where('quantity', '<', 0)
                    ->update(['quantity' => 0]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $purchaseReturn->load('items'),
                'message' => 'Purchase return created: ' . $purchaseReturn->return_invoice_number,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ────────────────────────────────────────────────
    // DELETE /stock/purchase-returns/{id}
    // ────────────────────────────────────────────────
    public function destroy($id)
    {
        $userId = Auth::id();

        $ret = PurchaseReturn::where('user_id', $userId)
                   ->with('items')
                   ->findOrFail($id);

        DB::beginTransaction();
        try {
            // Restore stock (return was cancelled)
            foreach ($ret->items as $item) {
                StockProduct::where('id', $item->product_id)
                    ->where('user_id', $userId)
                    ->increment('quantity', $item->quantity);
            }

            $ret->items()->delete();
            $ret->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase return deleted',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
