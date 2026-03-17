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
    public function index(Request $request): JsonResponse
    {
        $query = NurseryPurchase::where('user_id', $request->user()->id)->with(['supplier', 'plant']);

        if ($request->filled('status'))      { $query->where('status', $request->status); }
        if ($request->filled('date_from'))   { $query->whereDate('date', '>=', $request->date_from); }
        if ($request->filled('date_to'))     { $query->whereDate('date', '<=', $request->date_to); }
        if ($request->filled('supplier_id')) { $query->where('nursery_supplier_id', $request->supplier_id); }

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

        if (!empty($validated['nursery_supplier_id']) && empty($validated['supplier_name'])) {
            $validated['supplier_name'] = NurserySupplier::find($validated['nursery_supplier_id'])?->name;
        }
        if (!empty($validated['nursery_plant_id']) && empty($validated['plant_name'])) {
            $validated['plant_name'] = NurseryPlant::find($validated['nursery_plant_id'])?->name;
        }

        $purchase = NurseryPurchase::create($validated);

        if (($validated['status'] ?? 'completed') === 'completed' && !empty($validated['nursery_plant_id'])) {
            NurseryPlant::where('id', $validated['nursery_plant_id'])
                ->where('user_id', $request->user()->id)
                ->increment('quantity', $validated['quantity']);
        }

        if (!empty($validated['nursery_supplier_id'])) {
            NurserySupplier::where('id', $validated['nursery_supplier_id'])
                ->where('user_id', $request->user()->id)
                ->increment('total_purchase', $validated['total_amount']);
        }

        return response()->json(['success' => true, 'data' => $purchase->load(['supplier', 'plant']), 'message' => 'Purchase created successfully'], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $purchase = NurseryPurchase::where('user_id', $request->user()->id)
            ->with(['supplier', 'plant'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $purchase]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $purchase  = NurseryPurchase::where('user_id', $request->user()->id)->findOrFail($id);
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
            $validated['total_amount'] = ($validated['quantity'] ?? $purchase->quantity) * ($validated['unit_price'] ?? $purchase->unit_price);
        }

        $purchase->update($validated);

        return response()->json(['success' => true, 'data' => $purchase->fresh(['supplier', 'plant']), 'message' => 'Purchase updated successfully']);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        NurseryPurchase::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Purchase deleted successfully']);
    }
}
