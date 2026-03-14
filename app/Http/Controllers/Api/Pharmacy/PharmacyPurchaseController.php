<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyMedicine;
use App\Models\PharmacyPurchase;
use App\Models\PharmacySupplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PharmacyPurchaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PharmacyPurchase::where('user_id', auth()->id())
            ->with(['supplier', 'medicine']);

        if ($request->filled('supplier_id'))
            $query->where('supplier_id', $request->supplier_id);
        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('date_from'))
            $query->whereDate('date', '>=', $request->date_from);
        if ($request->filled('date_to'))
            $query->whereDate('date', '<=', $request->date_to);

        $purchases = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $purchases,
            'summary' => [
                'total'        => $purchases->count(),
                'total_amount' => $purchases->sum('total_amount'),
                'pending'      => $purchases->where('status', 'pending')->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:pharmacy_suppliers,id',
            'medicine_id' => 'required|exists:pharmacy_medicines,id',
            'invoice_no'  => 'nullable|string|max:100',
            'quantity'    => 'required|integer|min:1',
            'unit_price'  => 'required|numeric|min:0',
            'date'        => 'required|date',
            'status'      => 'nullable|in:pending,completed,cancelled',
            'note'        => 'nullable|string',
        ]);

        $validated['total_amount'] = $validated['quantity'] * $validated['unit_price'];
        $validated['user_id']      = auth()->id();
        $validated['status']       = $validated['status'] ?? 'completed';

        $purchase = null;

        DB::transaction(function () use ($validated, &$purchase) {
            $purchase = PharmacyPurchase::create($validated);

            if ($validated['status'] === 'completed') {
                PharmacyMedicine::where('id', $validated['medicine_id'])
                    ->where('user_id', auth()->id())
                    ->increment('stock', $validated['quantity']);

                PharmacyMedicine::where('id', $validated['medicine_id'])
                    ->where('user_id', auth()->id())
                    ->update(['purchase_price' => $validated['unit_price']]);
            }

            if (!empty($validated['supplier_id'])) {
                PharmacySupplier::where('id', $validated['supplier_id'])
                    ->where('user_id', auth()->id())
                    ->increment('total_purchase', $validated['total_amount']);
            }
        });

        return response()->json([
            'success' => true,
            'data'    => $purchase->load(['supplier', 'medicine']),
            'message' => 'Purchase recorded successfully',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $purchase = PharmacyPurchase::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['supplier', 'medicine'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $purchase,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $purchase = PharmacyPurchase::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'status' => 'nullable|in:pending,completed,cancelled',
            'note'   => 'nullable|string',
        ]);

        $purchase->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $purchase->fresh(),
            'message' => 'Purchase updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $purchase = PharmacyPurchase::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $purchase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase deleted successfully',
        ]);
    }
}
