<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickkilnSupplierController extends Controller
{
    use ApiResponse;

    public function index(Request $req): JsonResponse
    {
        $req->validate([
            'search'        => 'nullable|string|max:100',
            'material_type' => 'nullable|string|max:100',
        ]);

        $q = Supplier::query();

        if ($req->filled('search')) {
            $term = $req->search;
            $q->where(function ($sub) use ($term) {
                $sub->where('name',   'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%");
            });
        }

        if ($req->filled('material_type')) {
            $q->where('material_type', $req->material_type);
        }

        $suppliers = $q->orderBy('name')->get();

        $summary = [
            'total'        => $suppliers->count(),
            'total_supply' => (float) $suppliers->sum('total_supply'),
        ];

        return $this->ok($suppliers);
    }

    public function store(Request $req): JsonResponse
    {
        $validated = $req->validate([
            'name'          => 'required|string|max:255',
            'mobile'        => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'material_type' => 'nullable|string|max:100',
            'emoji'         => 'nullable|string|max:10',
            'note'          => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);

        return $this->created($supplier, 'সরবরাহকারী যোগ করা হয়েছে');
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load([
            'rawMaterialPurchases' => fn ($q) => $q->latest()->limit(20),
        ]);
        return $this->ok($supplier);
    }

    public function update(Request $req, Supplier $supplier): JsonResponse
    {
        $validated = $req->validate([
            'name'          => 'sometimes|required|string|max:255',
            'mobile'        => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'material_type' => 'nullable|string|max:100',
            'emoji'         => 'nullable|string|max:10',
            'note'          => 'nullable|string',
        ]);

        $supplier->update($validated);

        return $this->ok($supplier, 'আপডেট সফল হয়েছে');
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();
        return $this->ok(null, 'সরবরাহকারী মুছে ফেলা হয়েছে');
    }
}
