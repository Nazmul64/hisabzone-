<?php
// app/Http/Controllers/Api/CategoryController.php
// ✅ FIX 1: index() — শুধু নিজের category দেখা যাবে (user_id filter)
// ✅ FIX 2: store() — category create হলে user_id save হবে
// ✅ FIX 3: update() — শুধু নিজের category update করা যাবে
// ✅ FIX 4: destroy() — শুধু নিজের category delete করা যাবে
// ✅ FIX 5: show() — শুধু নিজের category দেখা যাবে

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
    // ✅ শুধু নিজের (user_id) category দেখাবে
    // Optional: ?is_expense=1 or ?is_expense=0
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        try {
            // ✅ authenticated user এর id নাও
            $userId = $request->user()->id;

            $query = Category::query()
                ->where('user_id', $userId); // ✅ শুধু নিজের category

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
    // ✅ category create হলে user_id save হবে
    // ✅ slug auto-generate হবে name থেকে (unique per user)
    // ─────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // ✅ slug auto-generate করা হবে — user কে দিতে হবে না
        $rawName = trim($request->input('name', ''));
        $autoSlug = $this->generateSlug($rawName, $userId);

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'icon'       => 'nullable|string|max:100',
            'is_expense' => 'required|boolean',
        ], [
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
                'user_id'    => $userId,            // ✅ user_id save
                'name'       => $rawName,
                'slug'       => $autoSlug,           // ✅ auto slug
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
    // ✅ শুধু নিজের category দেখা যাবে
    // ─────────────────────────────────────────────
    public function show(Request $request, string $id): JsonResponse
    {
        $userId   = $request->user()->id;
        $category = Category::where('id', $id)
            ->where('user_id', $userId) // ✅ user_id check
            ->first();

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
    // ✅ শুধু নিজের category update করা যাবে
    // ─────────────────────────────────────────────
    public function update(Request $request, string $id): JsonResponse
    {
        $userId   = $request->user()->id;
        $category = Category::where('id', $id)
            ->where('user_id', $userId) // ✅ user_id check
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'data'    => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|required|string|max:255',
            'icon'       => 'nullable|string|max:100',
            'is_expense' => 'sometimes|boolean',
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

            if ($request->has('name')) {
                $newName          = trim($request->name);
                $fillable['name'] = $newName;
                // ✅ নাম বদলালে slug ও update করো
                $fillable['slug'] = $this->generateSlug($newName, $userId, $id);
            }

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
    // ✅ শুধু নিজের category delete করা যাবে
    // ─────────────────────────────────────────────
    public function destroy(Request $request, string $id): JsonResponse
    {
        $userId   = $request->user()->id;
        $category = Category::where('id', $id)
            ->where('user_id', $userId) // ✅ user_id check
            ->first();

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

    // ─────────────────────────────────────────────
    // generateSlug() — name থেকে unique slug বানাও
    // ✅ same user এর duplicate slug হবে না
    // ─────────────────────────────────────────────
    private function generateSlug(string $name, int $userId, string $excludeId = null): string
    {
        // lowercase + space→underscore + শুধু alphanumeric/underscore রাখো
        $base = strtolower(trim($name));
        $base = preg_replace('/\s+/', '_', $base);
        $base = preg_replace('/[^a-z0-9_\-]/', '', $base);

        if (empty($base)) {
            $base = 'category_' . time();
        }

        $slug  = $base;
        $count = 1;

        // ✅ same user এর মধ্যে unique করো
        while (
            Category::where('slug', $slug)
                ->where('user_id', $userId)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '_' . $count;
            $count++;
        }

        return $slug;
    }
}
