<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacySupplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacySupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PharmacySupplier::where('user_id', auth()->id());

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('company', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
            );
        }

        $suppliers = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $suppliers,
            'summary' => [
                'total'          => $suppliers->count(),
                'total_purchase' => $suppliers->sum('total_purchase'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier = PharmacySupplier::create(
            array_merge($validated, ['user_id' => auth()->id()])
        );

        return response()->json([
            'success' => true,
            'data'    => $supplier,
            'message' => 'Supplier added successfully',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $supplier = PharmacySupplier::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $supplier->load('purchases.medicine'),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $supplier = PharmacySupplier::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $supplier->fresh(),
            'message' => 'Supplier updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $supplier = PharmacySupplier::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully',
        ]);
    }
}
