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

class SaleInvoiceController extends Controller
{
    // GET /stock/sales
    public function index(Request $request): JsonResponse
    {
        $query = SaleInvoice::forUser($request->user()->id)->with(['items', 'customer']);

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
                'total_items' => $invoices->sum(fn($inv) => $inv->items->sum('quantity')),
            ],
        ]);
    }

    // POST /stock/sales
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'date'                    => 'required|date',
            'customer_id'             => 'nullable|exists:stock_parties,id',
            'customer_name'           => 'nullable|string',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:stock_products,id',
            'items.*.product_name'    => 'required|string',
            'items.*.quantity'        => 'required|numeric|min:0.01',
            'items.*.unit_price'      => 'required|numeric|min:0',
            'items.*.total'           => 'required|numeric|min:0',
            'shipping_cost'           => 'nullable|numeric|min:0',
            'other_cost'              => 'nullable|numeric|min:0',
            'discount'                => 'nullable|numeric|min:0',
            'vat_percent'             => 'nullable|numeric|min:0|max:100',
            'paid_amount'             => 'nullable|numeric|min:0',
            'notes'                   => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $userId   = $request->user()->id;
            $items    = $request->items;
            $subtotal = collect($items)->sum('total');
            $vatPct   = (float)($request->vat_percent ?? 0);
            $vatAmt   = $subtotal * $vatPct / 100;
            $grand    = $subtotal
                + (float)($request->shipping_cost ?? 0)
                + (float)($request->other_cost ?? 0)
                + $vatAmt
                - (float)($request->discount ?? 0);
            $paid   = (float)($request->paid_amount ?? 0);
            $due    = $grand - $paid;
            $status = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $invoice = SaleInvoice::create([
                'user_id'       => $userId,
                'date'          => $request->date,
                'customer_id'   => $request->customer_id,
                'customer_name' => $request->customer_name,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'other_cost'    => $request->other_cost ?? 0,
                'discount'      => $request->discount ?? 0,
                'vat_percent'   => $vatPct,
                'vat_amount'    => $vatAmt,
                'subtotal'      => $subtotal,
                'grand_total'   => $grand,
                'paid_amount'   => $paid,
                'due_amount'    => $due,
                'notes'         => $request->notes,
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

                // Decrease stock
                StockProduct::where('id', $item['product_id'])
                    ->decrement('quantity', $item['quantity']);
            }

            // Update customer balance
            if ($due > 0 && $request->customer_id) {
                StockParty::where('id', $request->customer_id)
                    ->increment('balance', $due);
            }

            return response()->json([
                'success' => true,
                'data'    => $invoice->load('items'),
                'message' => 'Sale invoice created',
            ], 201);
        });
    }

    // GET /stock/sales/{id}
    public function show(Request $request, SaleInvoice $sale): JsonResponse
    {
        abort_if($sale->user_id !== $request->user()->id, 403);
        return response()->json([
            'success' => true,
            'data'    => $sale->load(['items', 'customer', 'payments']),
        ]);
    }

    // DELETE /stock/sales/{id}
    public function destroy(Request $request, SaleInvoice $sale): JsonResponse
    {
        abort_if($sale->user_id !== $request->user()->id, 403);

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                StockProduct::where('id', $item->product_id)
                    ->increment('quantity', $item->quantity);
            }
            if ($sale->due_amount > 0 && $sale->customer_id) {
                StockParty::where('id', $sale->customer_id)
                    ->decrement('balance', $sale->due_amount);
            }
            $sale->items()->delete();
            $sale->delete();
        });

        return response()->json(['success' => true, 'message' => 'Sale invoice deleted']);
    }

    // GET /stock/sales/next-number
    public function nextNumber(Request $request): JsonResponse
    {
        $count = SaleInvoice::where('user_id', $request->user()->id)->withTrashed()->count();
        return response()->json([
            'success' => true,
            'data'    => 'S-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT),
        ]);
    }
}
