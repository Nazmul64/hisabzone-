<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\SaleInvoice;
use App\Models\SaleItem;
use App\Models\StockProduct;
use App\Models\StockParty;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleInvoiceController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // GET /stock/sales
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        try {
            $query = SaleInvoice::forUser($request->user()->id)
                ->with(['items', 'customer']);

            if ($request->filled('period')) {
                match ($request->period) {
                    'today'      => $query->today(),
                    'this_month' => $query->thisMonth(),
                    'this_year'  => $query->thisYear(),
                    default      => null,
                };
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->dateRange($request->date_from, $request->date_to);
            }

            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $invoices = $query->latest('date')->get();

            return response()->json([
                'success' => true,
                'data'    => $invoices,
                'summary' => [
                    'total_sale'  => $invoices->sum('grand_total'),
                    'total_paid'  => $invoices->sum('paid_amount'),
                    'total_due'   => $invoices->sum('due_amount'),
                    'total_vat'   => $invoices->sum('vat_amount'),
                    'total_count' => $invoices->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('SaleInvoice@index: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => basename($e->getFile()),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // GET /stock/sales/next-number
    // ─────────────────────────────────────────────────────────────
    public function nextNumber(Request $request): JsonResponse
    {
        try {
            $count = SaleInvoice::forUser($request->user()->id)
                ->withTrashed()->count();
            return response()->json([
                'success' => true,
                'data'    => 'S-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT),
            ]);
        } catch (\Throwable $e) {
            Log::error('SaleInvoice@nextNumber: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // POST /stock/sales
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date'                 => 'required|date',
                'customer_id'          => 'nullable|exists:stock_parties,id',
                'customer_name'        => 'nullable|string|max:255',
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

                $invoice = SaleInvoice::create([
                    'user_id'       => $request->user()->id,
                    'date'          => $validated['date'],
                    'customer_id'   => $validated['customer_id']   ?? null,
                    'customer_name' => $validated['customer_name'] ?? null,
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
                    SaleItem::create([
                        'sale_invoice_id' => $invoice->id,
                        'product_id'      => $item['product_id'],
                        'product_name'    => $item['product_name'],
                        'quantity'        => $item['quantity'],
                        'unit_price'      => $item['unit_price'],
                        'total'           => $item['total'],
                    ]);
                    StockProduct::where('id', $item['product_id'])
                        ->decrement('quantity', $item['quantity']);
                }

                if ($due > 0 && !empty($validated['customer_id'])) {
                    StockParty::where('id', $validated['customer_id'])
                        ->increment('balance', $due);
                }

                return response()->json([
                    'success' => true,
                    'data'    => $invoice->load('items'),
                    'message' => 'Sale invoice created',
                ], 201);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('SaleInvoice@store: ' . $e->getMessage() . ' | line:' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => basename($e->getFile()),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // GET /stock/sales/{id}
    // ─────────────────────────────────────────────────────────────
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $sale = SaleInvoice::forUser($request->user()->id)
                ->with(['items', 'customer'])
                ->findOrFail($id);

            return response()->json(['success' => true, 'data' => $sale]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PUT/PATCH /stock/sales/{id}
    // ─────────────────────────────────────────────────────────────
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $sale = SaleInvoice::forUser($request->user()->id)->findOrFail($id);

            $validated = $request->validate([
                'date'                 => 'required|date',
                'customer_id'          => 'nullable|exists:stock_parties,id',
                'customer_name'        => 'nullable|string|max:255',
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

            return DB::transaction(function () use ($validated, $sale) {
                // পুরনো stock ফেরত দাও
                foreach ($sale->items as $oldItem) {
                    if ($oldItem->product_id) {
                        StockProduct::where('id', $oldItem->product_id)
                            ->increment('quantity', $oldItem->quantity);
                    }
                }

                // পুরনো customer balance কমাও
                if ($sale->due_amount > 0 && $sale->customer_id) {
                    StockParty::where('id', $sale->customer_id)
                        ->decrement('balance', $sale->due_amount);
                }

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

                $sale->update([
                    'date'          => $validated['date'],
                    'customer_id'   => $validated['customer_id']   ?? null,
                    'customer_name' => $validated['customer_name'] ?? null,
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

                // পুরনো items মুছো, নতুন বানাও
                $sale->items()->delete();

                foreach ($items as $item) {
                    SaleItem::create([
                        'sale_invoice_id' => $sale->id,
                        'product_id'      => $item['product_id'],
                        'product_name'    => $item['product_name'],
                        'quantity'        => $item['quantity'],
                        'unit_price'      => $item['unit_price'],
                        'total'           => $item['total'],
                    ]);
                    StockProduct::where('id', $item['product_id'])
                        ->decrement('quantity', $item['quantity']);
                }

                // নতুন customer balance বাড়াও
                if ($due > 0 && !empty($validated['customer_id'])) {
                    StockParty::where('id', $validated['customer_id'])
                        ->increment('balance', $due);
                }

                return response()->json([
                    'success' => true,
                    'data'    => $sale->load('items'),
                    'message' => 'Sale invoice updated',
                ]);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('SaleInvoice@update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => basename($e->getFile()),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // DELETE /stock/sales/{id}
    // ─────────────────────────────────────────────────────────────
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $sale = SaleInvoice::forUser($request->user()->id)->findOrFail($id);

            DB::transaction(function () use ($sale) {
                foreach ($sale->items as $item) {
                    if ($item->product_id) {
                        StockProduct::where('id', $item->product_id)
                            ->increment('quantity', $item->quantity);
                    }
                }
                if ($sale->due_amount > 0 && $sale->customer_id) {
                    StockParty::where('id', $sale->customer_id)
                        ->decrement('balance', $sale->due_amount);
                }
                $sale->items()->delete();
                $sale->delete();
            });

            return response()->json(['success' => true, 'message' => 'Sale invoice deleted']);

        } catch (\Throwable $e) {
            Log::error('SaleInvoice@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
