<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyCategory;
use App\Models\PharmacyMedicine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = auth()->id();

        $categories = PharmacyCategory::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function ($cat) use ($userId) {
                $cat->medicine_count = PharmacyMedicine::where('user_id', $userId)
                    ->where('category', $cat->name)
                    ->count();
                return $cat;
            });

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $category = PharmacyCategory::create(
            array_merge($validated, ['user_id' => auth()->id()])
        );

        return response()->json([
            'success' => true,
            'data'    => $category,
            'message' => 'Category created successfully',
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = PharmacyCategory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'name'  => 'sometimes|required|string|max:100',
            'emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $category->fresh(),
            'message' => 'Category updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $category = PharmacyCategory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
