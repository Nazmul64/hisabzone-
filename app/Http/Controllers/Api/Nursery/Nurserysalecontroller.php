<?php

namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurserySale;
use App\Models\Nursery\NurseryPlant;
use App\Models\Nursery\NurseryCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurserySaleController extends Controller
{
    // GET /api/nursery/sales
    public function index(Request $request): JsonResponse
    {
        $query = NurserySale::where('user_id', $request->user()->id)
            ->with(['customer', 'plant']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('customer_id')) {
            $query->where('nursery_customer_id', $request->customer_id);
        }

        $sales = $query->orderByDesc('date')->get();

        $today       = now()->toDateString();
        $todaySales  = $sales->filter(fn($s) => $s->date->toDateString() === $today)->sum('total_amount');

        return response()->json([
            'success' => true,
            'data'    => $sales,
            'summary' => [
                'total'        => $sales->count(),
                'total_amount' => $sales->sum('total_amount'),
                'today_amount' => $todaySales,
            ],
        ]);
    }

    // POST /api/nursery/sales
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nursery_customer_id' => 'nullable|integer|exists:nursery_customers,id',
            'nursery_plant_id'    => 'nullable|integer|exists:nursery_plants,id',
            'customer_name'       => 'nullable|string|max:255',
            'plant_name'          => 'nullable|string|max:255',
            'quantity'            => 'required|integer|min:1',
            'unit_price'          => 'required|numeric|min:0',
            'date'                => 'required|date',
            'status'              => 'nullable|in:pending,completed,cancelled',
            'notes'               => 'nullable|string',
        ]);

        $validated['total_amount'] = $validated['quantity'] * $validated['unit_price'];
        $validated['user_id']      = $request->user()->id;

        // Auto-fill names
        if (!empty($validated['nursery_customer_id']) && empty($validated['customer_name'])) {
            $customer = NurseryCustomer::find($validated['nursery_customer_id']);
            $validated['customer_name'] = $customer?->name;
        }
        if (!empty($validated['nursery_plant_id']) && empty($validated['plant_name'])) {
            $plant = NurseryPlant::find($validated['nursery_plant_id']);
            $validated['plant_name'] = $plant?->name;
        }

        $sale = NurserySale::create($validated);

        // Reduce plant stock if completed
        if (($validated['status'] ?? 'completed') === 'completed' && !empty($validated['nursery_plant_id'])) {
            NurseryPlant::where('id', $validated['nursery_plant_id'])
                ->where('user_id', $request->user()->id)
                ->decrement('quantity', $validated['quantity']);
        }

        // Update customer total purchase
        if (!empty($validated['nursery_customer_id'])) {
            $customer = NurseryCustomer::where('id', $validated['nursery_customer_id'])
                ->where('user_id', $request->user()->id)->first();
            if ($customer) {
                $customer->increment('total_purchase', $validated['total_amount']);
                $customer->update(['last_purchase_date' => $validated['date']]);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $sale->load(['customer', 'plant']),
            'message' => 'Sale created successfully',
        ], 201);
    }

    // GET /api/nursery/sales/{id}
    public function show(Request $request, $id): JsonResponse
    {
        $sale = NurserySale::where('user_id', $request->user()->id)
            ->with(['customer', 'plant'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $sale]);
    }

    // PUT /api/nursery/sales/{id}
    public function update(Request $request, $id): JsonResponse
    {
        $sale = NurserySale::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'nursery_customer_id' => 'nullable|integer|exists:nursery_customers,id',
            'nursery_plant_id'    => 'nullable|integer|exists:nursery_plants,id',
            'customer_name'       => 'nullable|string|max:255',
            'plant_name'          => 'nullable|string|max:255',
            'quantity'            => 'sometimes|integer|min:1',
            'unit_price'          => 'sometimes|numeric|min:0',
            'date'                => 'sometimes|date',
            'status'              => 'nullable|in:pending,completed,cancelled',
            'notes'               => 'nullable|string',
        ]);

        if (isset($validated['quantity']) || isset($validated['unit_price'])) {
            $qty   = $validated['quantity'] ?? $sale->quantity;
            $price = $validated['unit_price'] ?? $sale->unit_price;
            $validated['total_amount'] = $qty * $price;
        }

        $sale->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $sale->fresh(['customer', 'plant']),
            'message' => 'Sale updated successfully',
        ]);
    }

    // DELETE /api/nursery/sales/{id}
    public function destroy(Request $request, $id): JsonResponse
    {
        $sale = NurserySale::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sale deleted successfully',
        ]);
    }
}
