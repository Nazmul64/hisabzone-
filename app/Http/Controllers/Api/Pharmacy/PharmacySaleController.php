<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyCustomer;
use App\Models\PharmacyMedicine;
use App\Models\PharmacySale;
use App\Models\PharmacySaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PharmacySaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PharmacySale::where('user_id', auth()->id())
            ->with(['customer', 'items']);

        if ($request->filled('customer_id'))
            $query->where('customer_id', $request->customer_id);
        if ($request->filled('date_from'))
            $query->whereDate('date', '>=', $request->date_from);
        if ($request->filled('date_to'))
            $query->whereDate('date', '<=', $request->date_to);

        $sales = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $sales,
            'summary' => [
                'total_sales'  => $sales->count(),
                'total_amount' => round($sales->sum('total_amount'), 2),
                'total_paid'   => round($sales->sum('paid_amount'), 2),
                'total_due'    => round($sales->sum('due_amount'), 2),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'           => 'nullable|exists:pharmacy_customers,id',
            'items'                 => 'required|array|min:1',
            'items.*.medicine_id'   => 'nullable|exists:pharmacy_medicines,id',
            'items.*.medicine_name' => 'required|string|max:255',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'discount'              => 'nullable|numeric|min:0',
            'paid_amount'           => 'nullable|numeric|min:0',
            'payment_method'        => 'nullable|in:cash,card,bkash,other',
            'date'                  => 'required|date',
            'note'                  => 'nullable|string',
        ]);

        $sale = null;

        DB::transaction(function () use ($validated, &$sale) {
            $subtotal   = collect($validated['items'])
                ->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $discount   = $validated['discount'] ?? 0;
            $total      = max(0, $subtotal - $discount);
            $paidAmount = $validated['paid_amount'] ?? $total;

            $sale = PharmacySale::create([
                'user_id'        => auth()->id(),
                'customer_id'    => $validated['customer_id'] ?? null,
                'invoice_no'     => 'INV-' . strtoupper(uniqid()),
                'subtotal'       => round($subtotal, 2),
                'discount'       => round($discount, 2),
                'total_amount'   => round($total, 2),
                'paid_amount'    => round($paidAmount, 2),
                'due_amount'     => round(max(0, $total - $paidAmount), 2),
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'date'           => $validated['date'],
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                PharmacySaleItem::create([
                    'sale_id'       => $sale->id,
                    'medicine_id'   => $item['medicine_id'] ?? null,
                    'medicine_name' => $item['medicine_name'],
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $item['unit_price'],
                    'total_price'   => $item['quantity'] * $item['unit_price'],
                ]);

                if (!empty($item['medicine_id'])) {
                    PharmacyMedicine::where('id', $item['medicine_id'])
                        ->where('user_id', auth()->id())
                        ->decrement('stock', $item['quantity']);
                }
            }

            if (!empty($validated['customer_id'])) {
                PharmacyCustomer::where('id', $validated['customer_id'])
                    ->where('user_id', auth()->id())
                    ->increment('total_purchase', $total);

                PharmacyCustomer::where('id', $validated['customer_id'])
                    ->where('user_id', auth()->id())
                    ->update(['last_visit' => $validated['date']]);
            }
        });

        return response()->json([
            'success' => true,
            'data'    => $sale->load(['customer', 'items']),
            'message' => 'Sale completed successfully',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $sale = PharmacySale::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['customer', 'items.medicine'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $sale,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $sale = PharmacySale::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'discount'       => 'nullable|numeric|min:0',
            'paid_amount'    => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,card,bkash,other',
            'note'           => 'nullable|string',
        ]);

        $discount   = array_key_exists('discount', $validated)
            ? ($validated['discount'] ?? 0)
            : $sale->discount;

        $total      = max(0, $sale->subtotal - $discount);

        $paidAmount = array_key_exists('paid_amount', $validated)
            ? ($validated['paid_amount'] ?? $total)
            : $sale->paid_amount;

        $sale->update([
            'discount'       => round($discount, 2),
            'total_amount'   => round($total, 2),
            'paid_amount'    => round($paidAmount, 2),
            'due_amount'     => round(max(0, $total - $paidAmount), 2),
            'payment_method' => $validated['payment_method'] ?? $sale->payment_method,
            'note'           => array_key_exists('note', $validated) ? $validated['note'] : $sale->note,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $sale->fresh()->load(['customer', 'items']),
            'message' => 'Sale updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $sale = PharmacySale::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sale deleted successfully',
        ]);
    }
}
