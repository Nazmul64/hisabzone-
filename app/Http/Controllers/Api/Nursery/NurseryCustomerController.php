<?php

namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryCustomerController extends Controller
{
    // GET /api/nursery/customers
    public function index(Request $request): JsonResponse
    {
        $query = NurseryCustomer::where('user_id', $request->user()->id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
                  ->orWhere('address', 'like', "%$s%");
            });
        }

        $customers = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $customers,
            'summary' => [
                'total'          => $customers->count(),
                'total_purchase' => $customers->sum('total_purchase'),
            ],
        ]);
    }

    // POST /api/nursery/customers
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:255',
            'address'            => 'nullable|string|max:500',
            'last_purchase_date' => 'nullable|date',
            'notes'              => 'nullable|string',
        ]);

        $customer = NurseryCustomer::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $customer,
            'message' => 'Customer created successfully',
        ], 201);
    }

    // GET /api/nursery/customers/{id}
    public function show(Request $request, $id): JsonResponse
    {
        $customer = NurseryCustomer::where('user_id', $request->user()->id)
            ->with(['sales', 'orders'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $customer]);
    }

    // PUT /api/nursery/customers/{id}
    public function update(Request $request, $id): JsonResponse
    {
        $customer = NurseryCustomer::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:255',
            'address'            => 'nullable|string|max:500',
            'last_purchase_date' => 'nullable|date',
            'notes'              => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $customer,
            'message' => 'Customer updated successfully',
        ]);
    }

    // DELETE /api/nursery/customers/{id}
    public function destroy(Request $request, $id): JsonResponse
    {
        $customer = NurseryCustomer::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully',
        ]);
    }
}
