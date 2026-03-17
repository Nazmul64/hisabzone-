<?php

namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NurseryOrder::where('user_id', $request->user()->id)
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

        $orders = $query->orderByDesc('date')->get();

        return response()->json([
            'success' => true,
            'data'    => $orders,
            'summary' => [
                'total'        => $orders->count(),
                'pending'      => $orders->where('status', 'pending')->count(),
                'completed'    => $orders->where('status', 'completed')->count(),
                'total_amount' => $orders->sum('total_amount'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nursery_customer_id' => 'nullable|integer|exists:nursery_customers,id',
            'nursery_plant_id'    => 'nullable|integer|exists:nursery_plants,id',
            'customer_name'       => 'nullable|string|max:255',
            'plant_name'          => 'nullable|string|max:255',
            'quantity'            => 'required|integer|min:1',
            'total_amount'        => 'required|numeric|min:0',
            'date'                => 'required|date',
            'status'              => 'nullable|in:pending,completed,cancelled',
            'notes'               => 'nullable|string',
        ]);

        // Auto generate order number
        $lastOrder = NurseryOrder::where('user_id', $request->user()->id)
            ->orderByDesc('id')->first();
        $num = $lastOrder ? (intval(substr($lastOrder->order_number ?? '#ORD-000', 5)) + 1) : 1;
        $validated['order_number'] = '#ORD-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        $validated['user_id']      = $request->user()->id;

        $order = NurseryOrder::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $order->load(['customer', 'plant']),
            'message' => 'Order created successfully',
        ], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $order = NurseryOrder::where('user_id', $request->user()->id)
            ->with(['customer', 'plant'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $order]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $order = NurseryOrder::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'nursery_customer_id' => 'nullable|integer|exists:nursery_customers,id',
            'nursery_plant_id'    => 'nullable|integer|exists:nursery_plants,id',
            'customer_name'       => 'nullable|string|max:255',
            'plant_name'          => 'nullable|string|max:255',
            'quantity'            => 'sometimes|integer|min:1',
            'total_amount'        => 'sometimes|numeric|min:0',
            'date'                => 'sometimes|date',
            'status'              => 'nullable|in:pending,completed,cancelled',
            'notes'               => 'nullable|string',
        ]);

        $order->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $order->fresh(['customer', 'plant']),
            'message' => 'Order updated successfully',
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $order = NurseryOrder::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully',
        ]);
    }
}
