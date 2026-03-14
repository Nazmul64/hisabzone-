<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyCustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PharmacyCustomer::where('user_id', auth()->id());

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
            );
        }

        $customers = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $customers,
            'summary' => [
                'total'          => $customers->count(),
                'total_purchase' => $customers->sum('total_purchase'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer = PharmacyCustomer::create(
            array_merge($validated, ['user_id' => auth()->id()])
        );

        return response()->json([
            'success' => true,
            'data'    => $customer,
            'message' => 'Customer added successfully',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $customer = PharmacyCustomer::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $customer,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $customer = PharmacyCustomer::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $customer->fresh(),
            'message' => 'Customer updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $customer = PharmacyCustomer::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully',
        ]);
    }
}
