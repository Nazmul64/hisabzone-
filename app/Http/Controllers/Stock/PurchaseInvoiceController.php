<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseItem;
use App\Models\StockProduct;
use App\Models\StockParty;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseInvoiceController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // GET /stock/purchases
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PurchaseInvoice::where('user_id', $request->user()->id)
                ->with(['items', 'supplier']);

            if ($request->filled('period')) {
                match ($request->period) {
                    'today'      => $query->whereDate('date', today()),
                    'this_month' => $query->whereYear('date', now()->year)
                                         ->whereMonth('date', now()->month),
                    'this_year'  => $query->whereYear('date', now()->year),
                    default      => null,
                };
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('date', [
                    $request->date_from,
                    $request->date_to,
                ]);
            }

            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $invoices = $query->latest('date')->get();

            return response()->json([
                'success' => true,
                'data'    => $invoices,
                'summary' => [
                    'total_purchase' => $invoices->sum('grand_total'),
                    'total_paid'     => $invoices->sum('paid_amount'),
                    'total_due'      => $invoices->sum('due_amount'),
                    'total_vat'      => $invoices->sum('vat_amount'),
                    'total_count'    => $invoices->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('PurchaseInvoice@index: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => basename($e->getFile()),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // GET /stock/purchases/next-number
    // ─────────────────────────────────────────────────────────────
    public function nextNumber(Request $request): JsonResponse
    {
        try {
            $count = PurchaseInvoice::where('user_id', $request->user()->id)
                ->withTrashed()->count();
            return response()->json([
                'success' => true,
                'data'    => 'P-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT),
            ]);
        } catch (\Throwable $e) {
            Log::error('PurchaseInvoice@nextNumber: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // POST /stock/purchases
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date'                 => 'required|date',
                'supplier_id'          => 'nullable|exists:stock_parties,id',
                'supplier_name'        => 'nullable|string|max:255',
                'items'                => 'required|array|min:1',
                'items.*.product_id'   => 'required|exists:stock_products,id',
                'items.*.product_name' => 'required|string',
                'items.*.quantity'     => 'required|numeric|min:0.01',
                'items.*.unit_price'   => 'required|numeric|min:0',
                'items.*.total'        => 'required|numeric|min:0',
                'shipping_cost'        => 'nullable|numeric|min:0',
                'other_cost'           => 'nullable|numeric|min:0',
                'discount'             => 'nullable|numeric|min:0',
                'vat_percent'          => 'nullable|numeric|min:0|max:100',
                'paid_amount'          => 'nullable|numeric|min:0',
                'notes'                => 'nullable|string',
            ]);

            return DB::transaction(function () use ($request, $validated) {
                $items    = $validated['items'];
                $subtotal = collect($items)->sum('total');
                $vatPct   = (float)($validated['vat_percent']   ?? 0);
                $vatAmt   = round($subtotal * $vatPct / 100, 2);
                $grand    = round(
                    $subtotal
                    + (float)($validated['shipping_cost'] ?? 0)
                    + (float)($validated['other_cost']    ?? 0)
                    + $vatAmt
                    - (float)($validated['discount']      ?? 0),
                    2
                );
                $paid   = (float)($validated['paid_amount'] ?? 0);
                $due    = round($grand - $paid, 2);
                $status = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

                $invoice = PurchaseInvoice::create([
                    'user_id'       => $request->user()->id,
                    'date'          => $validated['date'],
                    'supplier_id'   => $validated['supplier_id']   ?? null,
                    'supplier_name' => $validated['supplier_name'] ?? null,
                    'shipping_cost' => $validated['shipping_cost'] ?? 0,
                    'other_cost'    => $validated['other_cost']    ?? 0,
                    'discount'      => $validated['discount']      ?? 0,
                    'vat_percent'   => $vatPct,
                    'vat_amount'    => $vatAmt,
                    'subtotal'      => $subtotal,
                    'grand_total'   => $grand,
                    'paid_amount'   => $paid,
                    'due_amount'    => $due,
                    'notes'         => $validated['notes'] ?? null,
                    'status'        => $status,
                ]);

                foreach ($items as $item) {
                    PurchaseItem::create([
                        'purchase_invoice_id' => $invoice->id,
                        'product_id'          => $item['product_id'],
                        'product_name'        => $item['product_name'],
                        'quantity'            => $item['quantity'],
                        'unit_price'          => $item['unit_price'],
                        'total'               => $item['total'],
                    ]);
                    // stock বাড়াও
                    StockProduct::where('id', $item['product_id'])
                        ->increment('quantity', $item['quantity']);
                }

                // supplier balance কমাও
                if ($due > 0 && !empty($validated['supplier_id'])) {
                    StockParty::where('id', $validated['supplier_id'])
                        ->decrement('balance', $due);
                }

                return response()->json([
                    'success' => true,
                    'data'    => $invoice->load('items'),
                    'message' => 'Purchase invoice created',
                ], 201);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('PurchaseInvoice@store: ' . $e->getMessage()
                . ' | line:' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => basename($e->getFile()),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // GET /stock/purchases/{id}
    // ─────────────────────────────────────────────────────────────
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $purchase = PurchaseInvoice::where('user_id', $request->user()->id)
                ->with(['items', 'supplier'])
                ->findOrFail($id);

            return response()->json(['success' => true, 'data' => $purchase]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PUT/PATCH /stock/purchases/{id}
    // ─────────────────────────────────────────────────────────────
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $purchase = PurchaseInvoice::where('user_id', $request->user()->id)
                ->findOrFail($id);

            $validated = $request->validate([
                'date'                 => 'required|date',
                'supplier_id'          => 'nullable|exists:stock_parties,id',
                'supplier_name'        => 'nullable|string|max:255',
                'items'                => 'required|array|min:1',
                'items.*.product_id'   => 'required|exists:stock_products,id',
                'items.*.product_name' => 'required|string',
                'items.*.quantity'     => 'required|numeric|min:0.01',
                'items.*.unit_price'   => 'required|numeric|min:0',
                'items.*.total'        => 'required|numeric|min:0',
                'shipping_cost'        => 'nullable|numeric|min:0',
                'other_cost'           => 'nullable|numeric|min:0',
                'discount'             => 'nullable|numeric|min:0',
                'vat_percent'          => 'nullable|numeric|min:0|max:100',
                'paid_amount'          => 'nullable|numeric|min:0',
                'notes'                => 'nullable|string',
            ]);

            return DB::transaction(function () use ($validated, $purchase) {

                // ── পুরনো stock কমাও (purchase undo) ──────────────
                foreach ($purchase->items as $oldItem) {
                    if ($oldItem->product_id) {
                        StockProduct::where('id', $oldItem->product_id)
                            ->decrement('quantity', $oldItem->quantity);
                    }
                }

                // ── পুরনো supplier balance ফেরত দাও ──────────────
                if ($purchase->due_amount > 0 && $purchase->supplier_id) {
                    StockParty::where('id', $purchase->supplier_id)
                        ->increment('balance', $purchase->due_amount);
                }

                // ── নতুন calculation ──────────────────────────────
                $items    = $validated['items'];
                $subtotal = collect($items)->sum('total');
                $vatPct   = (float)($validated['vat_percent']   ?? 0);
                $vatAmt   = round($subtotal * $vatPct / 100, 2);
                $grand    = round(
                    $subtotal
                    + (float)($validated['shipping_cost'] ?? 0)
                    + (float)($validated['other_cost']    ?? 0)
                    + $vatAmt
                    - (float)($validated['discount']      ?? 0),
                    2
                );
                $paid   = (float)($validated['paid_amount'] ?? 0);
                $due    = round($grand - $paid, 2);
                $status = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

                // ── invoice update ────────────────────────────────
                $purchase->update([
                    'date'          => $validated['date'],
                    'supplier_id'   => $validated['supplier_id']   ?? null,
                    'supplier_name' => $validated['supplier_name'] ?? null,
                    'shipping_cost' => $validated['shipping_cost'] ?? 0,
                    'other_cost'    => $validated['other_cost']    ?? 0,
                    'discount'      => $validated['discount']      ?? 0,
                    'vat_percent'   => $vatPct,
                    'vat_amount'    => $vatAmt,
                    'subtotal'      => $subtotal,
                    'grand_total'   => $grand,
                    'paid_amount'   => $paid,
                    'due_amount'    => $due,
                    'notes'         => $validated['notes'] ?? null,
                    'status'        => $status,
                ]);

                // ── পুরনো items মুছো, নতুন বানাও ──────────────────
                $purchase->items()->delete();

                foreach ($items as $item) {
                    PurchaseItem::create([
                        'purchase_invoice_id' => $purchase->id,
                        'product_id'          => $item['product_id'],
                        'product_name'        => $item['product_name'],
                        'quantity'            => $item['quantity'],
                        'unit_price'          => $item['unit_price'],
                        'total'               => $item['total'],
                    ]);
                    // নতুন stock বাড়াও
                    StockProduct::where('id', $item['product_id'])
                        ->increment('quantity', $item['quantity']);
                }

                // ── নতুন supplier balance কমাও ────────────────────
                if ($due > 0 && !empty($validated['supplier_id'])) {
                    StockParty::where('id', $validated['supplier_id'])
                        ->decrement('balance', $due);
                }

                return response()->json([
                    'success' => true,
                    'data'    => $purchase->load('items'),
                    'message' => 'Purchase invoice updated',
                ]);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('PurchaseInvoice@update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => basename($e->getFile()),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // DELETE /stock/purchases/{id}
    // ─────────────────────────────────────────────────────────────
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $purchase = PurchaseInvoice::where('user_id', $request->user()->id)
                ->findOrFail($id);

            DB::transaction(function () use ($purchase) {
                foreach ($purchase->items as $item) {
                    if ($item->product_id) {
                        StockProduct::where('id', $item->product_id)
                            ->decrement('quantity', $item->quantity);
                    }
                }
                if ($purchase->due_amount > 0 && $purchase->supplier_id) {
                    StockParty::where('id', $purchase->supplier_id)
                        ->increment('balance', $purchase->due_amount);
                }
                $purchase->items()->delete();
                $purchase->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase invoice deleted',
            ]);

        } catch (\Throwable $e) {
            Log::error('PurchaseInvoice@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
