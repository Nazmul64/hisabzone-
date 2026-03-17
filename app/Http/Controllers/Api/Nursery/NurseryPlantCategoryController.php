<?php

namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryPlantCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryPlantCategoryController extends Controller
{
    // GET /api/nursery/plant-categories
    public function index(Request $request): JsonResponse
    {
        $categories = NurseryPlantCategory::where('user_id', $request->user()->id)
            ->withCount('plants')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $categories,
            'message' => 'Categories fetched successfully',
        ]);
    }

    // POST /api/nursery/plant-categories
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $category = NurseryPlantCategory::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $category,
            'message' => 'Category created successfully',
        ], 201);
    }

    // GET /api/nursery/plant-categories/{id}
    public function show(Request $request, $id): JsonResponse
    {
        $category = NurseryPlantCategory::where('user_id', $request->user()->id)
            ->with('plants')
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $category]);
    }

    // PUT /api/nursery/plant-categories/{id}
    public function update(Request $request, $id): JsonResponse
    {
        $category = NurseryPlantCategory::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $category,
            'message' => 'Category updated successfully',
        ]);
    }

    // DELETE /api/nursery/plant-categories/{id}
    public function destroy(Request $request, $id): JsonResponse
    {
        $category = NurseryPlantCategory::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
