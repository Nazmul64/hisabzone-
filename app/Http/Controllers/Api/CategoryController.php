<?php
// app/Http/Controllers/Api/CategoryController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    // ─────────────────────────────────────────────
    // GET /api/categories
    // Optional: ?is_expense=1 or ?is_expense=0
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Category::query();

            if ($request->has('is_expense')) {
                $query->where('is_expense',
                    filter_var($request->is_expense, FILTER_VALIDATE_BOOLEAN));
            }

            $categories = $query
                ->orderBy('is_expense', 'desc')
                ->orderBy('name')
                ->get()
                ->map(fn($item) => [
                    'id'         => $item->id,
                    'name'       => $item->name  ?? '',
                    'slug'       => $item->slug  ?? '',
                    'icon'       => $item->icon  ?? 'category',
                    'is_expense' => (bool) $item->is_expense,
                ]);

            return response()->json([
                'success' => true,
                'data'    => $categories,
                'message' => 'Categories retrieved successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────
    // POST /api/categories
    // ─────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'slug'       => 'required|string|max:255|unique:categories,slug|regex:/^[a-z0-9_\-]+$/',
            'icon'       => 'nullable|string|max:100',
            'is_expense' => 'required|boolean',
        ], [
            'slug.unique'   => 'এই slug ইতিমধ্যে আছে',
            'slug.regex'    => 'Slug শুধু lowercase, number, underscore, hyphen হতে পারে',
            'name.required' => 'নাম দিন',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => $validator->errors()->first(),
                'data'    => null,
            ], 422);
        }

        try {
            $category = Category::create([
                'name'       => trim($request->name),
                'slug'       => strtolower(trim($request->slug)),
                'icon'       => $request->input('icon', 'category'),
                'is_expense' => $request->boolean('is_expense'),
            ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'         => $category->id,
                    'name'       => $category->name,
                    'slug'       => $category->slug,
                    'icon'       => $category->icon,
                    'is_expense' => (bool) $category->is_expense,
                ],
                'message' => 'Category created successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    // ─────────────────────────────────────────────
    // GET /api/categories/{id}
    // ─────────────────────────────────────────────
    public function show(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $category->id,
                'name'       => $category->name,
                'slug'       => $category->slug,
                'icon'       => $category->icon,
                'is_expense' => (bool) $category->is_expense,
            ],
            'message' => 'Category retrieved successfully',
        ], 200);
    }

    // ─────────────────────────────────────────────
    // PUT /api/categories/{id}
    // ─────────────────────────────────────────────
    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'data'    => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|required|string|max:255',
            'slug'       => 'sometimes|required|string|max:255|unique:categories,slug,' . $id . '|regex:/^[a-z0-9_\-]+$/',
            'icon'       => 'nullable|string|max:100',
            'is_expense' => 'sometimes|boolean',
        ], [
            'slug.unique' => 'এই slug ইতিমধ্যে আছে',
            'slug.regex'  => 'Slug শুধু lowercase, number, underscore, hyphen হতে পারে',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => $validator->errors()->first(),
                'data'    => null,
            ], 422);
        }

        try {
            $fillable = [];
            if ($request->has('name'))       $fillable['name']       = trim($request->name);
            if ($request->has('slug'))       $fillable['slug']       = strtolower(trim($request->slug));
            if ($request->has('icon'))       $fillable['icon']       = $request->input('icon', 'category');
            if ($request->has('is_expense')) $fillable['is_expense'] = $request->boolean('is_expense');

            $category->update($fillable);
            $category->refresh();

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'         => $category->id,
                    'name'       => $category->name,
                    'slug'       => $category->slug,
                    'icon'       => $category->icon,
                    'is_expense' => (bool) $category->is_expense,
                ],
                'message' => 'Category updated successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    // ─────────────────────────────────────────────
    // DELETE /api/categories/{id}
    // ─────────────────────────────────────────────
    public function destroy(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'data'    => null,
            ], 404);
        }

        try {
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
                'data'    => null,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}
