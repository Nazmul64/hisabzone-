<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyMedicine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyMedicineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PharmacyMedicine::where('user_id', auth()->id());

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('generic_name', 'like', "%{$s}%")
                ->orWhere('brand', 'like', "%{$s}%")
            );
        }

        if ($request->filled('category'))
            $query->where('category', $request->category);

        if ($request->boolean('low_stock'))
            $query->whereColumn('stock', '<', 'min_stock');

        if ($request->boolean('expiring_soon'))
            $query->whereNotNull('expiry_date')
                  ->whereDate('expiry_date', '<=', now()->addDays(30));

        $medicines = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $medicines,
            'summary' => [
                'total'         => $medicines->count(),
                'low_stock'     => $medicines->filter(fn($m) => $m->stock < $m->min_stock)->count(),
                'expiring_soon' => $medicines->filter(fn($m) =>
                    $m->expiry_date &&
                    \Carbon\Carbon::parse($m->expiry_date)->lte(now()->addDays(30))
                )->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'generic_name'   => 'nullable|string|max:255',
            'brand'          => 'nullable|string|max:255',
            'category'       => 'nullable|string|max:100',
            'batch_no'       => 'nullable|string|max:100',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'min_stock'      => 'nullable|integer|min:0',
            'expiry_date'    => 'nullable|date',
            'unit'           => 'nullable|string|max:50',
            'description'    => 'nullable|string',
        ]);

        $medicine = PharmacyMedicine::create(
            array_merge($validated, ['user_id' => auth()->id()])
        );

        return response()->json([
            'success' => true,
            'data'    => $medicine,
            'message' => 'Medicine added successfully',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $medicine = PharmacyMedicine::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $medicine,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $medicine = PharmacyMedicine::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'generic_name'   => 'nullable|string|max:255',
            'brand'          => 'nullable|string|max:255',
            'category'       => 'nullable|string|max:100',
            'batch_no'       => 'nullable|string|max:100',
            'purchase_price' => 'sometimes|required|numeric|min:0',
            'selling_price'  => 'sometimes|required|numeric|min:0',
            'stock'          => 'sometimes|required|integer|min:0',
            'min_stock'      => 'nullable|integer|min:0',
            'expiry_date'    => 'nullable|date',
            'unit'           => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'is_active'      => 'nullable|boolean',
        ]);

        $medicine->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $medicine->fresh(),
            'message' => 'Medicine updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $medicine = PharmacyMedicine::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $medicine->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medicine deleted successfully',
        ]);
    }

    public function expiry(): JsonResponse
    {
        $medicines = PharmacyMedicine::where('user_id', auth()->id())
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(60))
            ->orderBy('expiry_date')
            ->get()
            ->map(fn($m) => tap($m, fn($m) =>
                $m->days_left = (int) now()->diffInDays($m->expiry_date, false)
            ));

        return response()->json([
            'success' => true,
            'data'    => $medicines,
            'summary' => [
                'expiring_7_days'  => $medicines->where('days_left', '<=', 7)->count(),
                'expiring_30_days' => $medicines->where('days_left', '<=', 30)->count(),
                'total'            => $medicines->count(),
            ],
        ]);
    }

    public function lowStock(): JsonResponse
    {
        $medicines = PharmacyMedicine::where('user_id', auth()->id())
            ->whereColumn('stock', '<', 'min_stock')
            ->orderBy('stock')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $medicines,
            'summary' => ['total' => $medicines->count()],
        ]);
    }
}
