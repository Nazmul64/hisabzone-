<?php

namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurserySupplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurserySupplierController extends Controller
{
    // GET /api/nursery/suppliers
    public function index(Request $request): JsonResponse
    {
        $query = NurserySupplier::where('user_id', $request->user()->id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
                  ->orWhere('address', 'like', "%$s%");
            });
        }

        $suppliers = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $suppliers,
            'summary' => [
                'total'          => $suppliers->count(),
                'total_purchase' => $suppliers->sum('total_purchase'),
            ],
        ]);
    }

    // POST /api/nursery/suppliers
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'products' => 'nullable|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:255',
            'address'  => 'nullable|string|max:500',
            'notes'    => 'nullable|string',
        ]);

        $supplier = NurserySupplier::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $supplier,
            'message' => 'Supplier created successfully',
        ], 201);
    }

    // GET /api/nursery/suppliers/{id}
    public function show(Request $request, $id): JsonResponse
    {
        $supplier = NurserySupplier::where('user_id', $request->user()->id)
            ->with('purchases')
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $supplier]);
    }

    // PUT /api/nursery/suppliers/{id}
    public function update(Request $request, $id): JsonResponse
    {
        $supplier = NurserySupplier::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'products' => 'nullable|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:255',
            'address'  => 'nullable|string|max:500',
            'notes'    => 'nullable|string',
        ]);

        $supplier->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $supplier,
            'message' => 'Supplier updated successfully',
        ]);
    }

    // DELETE /api/nursery/suppliers/{id}
    public function destroy(Request $request, $id): JsonResponse
    {
        $supplier = NurserySupplier::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully',
        ]);
    }
}
