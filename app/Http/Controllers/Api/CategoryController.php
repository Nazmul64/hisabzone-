<?php
// app/Http/Controllers/Api/CategoryController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    // ✅ সব category list
    public function index()
    {
        try {
            $categories = Category::orderBy('is_expense', 'desc')
                ->orderBy('name')
                ->get()
                ->map(function ($item) {
                    return [
                        'id'         => $item->id,
                        'name'       => $item->name       ?? '',
                        'slug'       => $item->slug       ?? '',
                        'icon'       => $item->icon       ?? 'category',
                        'is_expense' => (bool) $item->is_expense,
                    ];
                });

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

    // ✅ নতুন category তৈরি
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // ✅ name unique সরিয়ে দিলাম — slug unique check যথেষ্ট
            'name'       => 'required|string|max:255',
            'slug'       => 'required|string|max:255|unique:categories,slug|regex:/^[a-z0-9_\-]+$/',
            'icon'       => 'nullable|string|max:100',
            'is_expense' => 'required|boolean',
        ], [
            'slug.regex'    => 'Slug শুধু lowercase letter, number, underscore এবং hyphen দিয়ে হতে পারে',
            'slug.unique'   => 'এই slug ইতিমধ্যে ব্যবহার হয়েছে',
            'name.required' => 'নাম দিন',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $category = Category::create([
                'name'       => $request->name,
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
            ], 500);
        }
    }

    // ✅ একটি category দেখাও
    public function show(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
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

    // ✅ category update
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|required|string|max:255',
            'slug'       => 'sometimes|required|string|max:255|unique:categories,slug,' . $id . '|regex:/^[a-z0-9_\-]+$/',
            'icon'       => 'nullable|string|max:100',
            'is_expense' => 'sometimes|boolean',
        ], [
            'slug.regex'  => 'Slug শুধু lowercase letter, number, underscore এবং hyphen দিয়ে হতে পারে',
            'slug.unique' => 'এই slug ইতিমধ্যে ব্যবহার হয়েছে',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $fillable = [];
            if ($request->has('name'))       $fillable['name']       = $request->name;
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
            ], 500);
        }
    }

    // ✅ category delete
    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        try {
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
