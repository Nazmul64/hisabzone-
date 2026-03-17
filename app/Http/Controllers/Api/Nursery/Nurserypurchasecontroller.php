<?php

namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryPurchase;
use App\Models\Nursery\NurseryPlant;
use App\Models\Nursery\NurserySupplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryPurchaseController extends Controller
{
    // GET /api/nursery/purchases
    public function index(Request $request): JsonResponse
    {
        $query = NurseryPurchase::where('user_id', $request->user()->id)
            ->with(['supplier', 'plant']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('supplier_id')) {
            $query->where('nursery_supplier_id', $request->supplier_id);
        }

        $purchases = $query->orderByDesc('date')->get();

        return response()->json([
            'success' => true,
            'data'    => $purchases,
            'summary' => [
                'total'        => $purchases->count(),
                'total_amount' => $purchases->sum('total_amount'),
                'pending'      => $purchases->where('status', 'pending')->count(),
                'completed'    => $purchases->where('status', 'completed')->count(),
            ],
        ]);
    }

    // POST /api/nursery/purchases
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nursery_supplier_id' => 'nullable|integer|exists:nursery_suppliers,id',
            'nursery_plant_id'    => 'nullable|integer|exists:nursery_plants,id',
            'supplier_name'       => 'nullable|string|max:255',
            'plant_name'          => 'nullable|string|max:255',
            'quantity'            => 'required|integer|min:1',
            'unit_price'          => 'required|numeric|min:0',
            'date'                => 'required|date',
            'status'              => 'nullable|in:pending,completed,cancelled',
            'notes'               => 'nullable|string',
        ]);

        $validated['total_amount'] = $validated['quantity'] * $validated['unit_price'];
        $validated['user_id']      = $request->user()->id;

        // Auto-fill name from relation
        if (!empty($validated['nursery_supplier_id']) && empty($validated['supplier_name'])) {
            $supplier = NurserySupplier::find($validated['nursery_supplier_id']);
            $validated['supplier_name'] = $supplier?->name;
        }
        if (!empty($validated['nursery_plant_id']) && empty($validated['plant_name'])) {
            $plant = NurseryPlant::find($validated['nursery_plant_id']);
            $validated['plant_name'] = $plant?->name;
        }

        $purchase = NurseryPurchase::create($validated);

        // Update plant stock if completed
        if (($validated['status'] ?? 'completed') === 'completed' && !empty($validated['nursery_plant_id'])) {
            NurseryPlant::where('id', $validated['nursery_plant_id'])
                ->where('user_id', $request->user()->id)
                ->increment('quantity', $validated['quantity']);
        }

        // Update supplier total
        if (!empty($validated['nursery_supplier_id'])) {
            NurserySupplier::where('id', $validated['nursery_supplier_id'])
                ->where('user_id', $request->user()->id)
                ->increment('total_purchase', $validated['total_amount']);
        }

        return response()->json([
            'success' => true,
            'data'    => $purchase->load(['supplier', 'plant']),
            'message' => 'Purchase created successfully',
        ], 201);
    }

    // GET /api/nursery/purchases/{id}
    public function show(Request $request, $id): JsonResponse
    {
        $purchase = NurseryPurchase::where('user_id', $request->user()->id)
            ->with(['supplier', 'plant'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $purchase]);
    }

    // PUT /api/nursery/purchases/{id}
    public function update(Request $request, $id): JsonResponse
    {
        $purchase = NurseryPurchase::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'nursery_supplier_id' => 'nullable|integer|exists:nursery_suppliers,id',
            'nursery_plant_id'    => 'nullable|integer|exists:nursery_plants,id',
            'supplier_name'       => 'nullable|string|max:255',
            'plant_name'          => 'nullable|string|max:255',
            'quantity'            => 'sometimes|integer|min:1',
            'unit_price'          => 'sometimes|numeric|min:0',
            'date'                => 'sometimes|date',
            'status'              => 'nullable|in:pending,completed,cancelled',
            'notes'               => 'nullable|string',
        ]);

        if (isset($validated['quantity']) || isset($validated['unit_price'])) {
            $qty   = $validated['quantity'] ?? $purchase->quantity;
            $price = $validated['unit_price'] ?? $purchase->unit_price;
            $validated['total_amount'] = $qty * $price;
        }

        $purchase->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $purchase->fresh(['supplier', 'plant']),
            'message' => 'Purchase updated successfully',
        ]);
    }

    // DELETE /api/nursery/purchases/{id}
    public function destroy(Request $request, $id): JsonResponse
    {
        $purchase = NurseryPurchase::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $purchase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase deleted successfully',
        ]);
    }
}
