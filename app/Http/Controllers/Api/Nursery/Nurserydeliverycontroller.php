<?php

namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryDeliveryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NurseryDelivery::where('user_id', $request->user()->id)
            ->with(['order', 'customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $deliveries = $query->orderByDesc('date')->get();

        return response()->json([
            'success' => true,
            'data'    => $deliveries,
            'summary' => [
                'total'      => $deliveries->count(),
                'pending'    => $deliveries->where('status', 'pending')->count(),
                'in_transit' => $deliveries->where('status', 'in_transit')->count(),
                'completed'  => $deliveries->where('status', 'completed')->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nursery_order_id'    => 'nullable|integer|exists:nursery_orders,id',
            'nursery_customer_id' => 'nullable|integer|exists:nursery_customers,id',
            'customer_name'       => 'nullable|string|max:255',
            'address'             => 'nullable|string|max:500',
            'date'                => 'required|date',
            'status'              => 'nullable|in:pending,in_transit,completed,cancelled',
            'emoji'               => 'nullable|string|max:10',
            'notes'               => 'nullable|string',
        ]);

        // Auto generate delivery number
        $last = NurseryDelivery::where('user_id', $request->user()->id)
            ->orderByDesc('id')->first();
        $num = $last ? (intval(substr($last->delivery_number ?? '#DEL-000', 5)) + 1) : 1;
        $validated['delivery_number'] = '#DEL-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        $validated['user_id']         = $request->user()->id;

        $delivery = NurseryDelivery::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $delivery->load(['order', 'customer']),
            'message' => 'Delivery created successfully',
        ], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $delivery = NurseryDelivery::where('user_id', $request->user()->id)
            ->with(['order', 'customer'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $delivery]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $delivery = NurseryDelivery::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'nursery_order_id'    => 'nullable|integer|exists:nursery_orders,id',
            'nursery_customer_id' => 'nullable|integer|exists:nursery_customers,id',
            'customer_name'       => 'nullable|string|max:255',
            'address'             => 'nullable|string|max:500',
            'date'                => 'sometimes|date',
            'status'              => 'nullable|in:pending,in_transit,completed,cancelled',
            'emoji'               => 'nullable|string|max:10',
            'notes'               => 'nullable|string',
        ]);

        $delivery->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $delivery->fresh(['order', 'customer']),
            'message' => 'Delivery updated successfully',
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $delivery = NurseryDelivery::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $delivery->delete();

        return response()->json([
            'success' => true,
            'message' => 'Delivery deleted successfully',
        ]);
    }
}
