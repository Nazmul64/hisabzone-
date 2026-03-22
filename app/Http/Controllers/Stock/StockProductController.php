<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StockProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $query = StockProduct::where('user_id', $userId)->where('is_active', true);

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('code', 'like', "%$s%")
                  ->orWhere('category', 'like', "%$s%");
            });
        }
        if ($cat = $request->category) {
            $query->where('category', $cat);
        }

        $products = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $products,
            'stats'   => [
                'total_products'    => $products->count(),
                'total_stock_qty'   => $products->sum('quantity'),
                'total_stock_value' => $products->sum(fn($p) => $p->quantity * $p->purchase_price),
                'low_stock_count'   => $products->filter(fn($p) => $p->is_low_stock)->count(),
                'out_of_stock'      => $products->where('quantity', '<=', 0)->count(),
                'categories'        => $products->pluck('category')->filter()->unique()->values(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'nullable|string|max:100',
            'category'        => 'nullable|string|max:100',
            'unit'            => 'nullable|string|max:50',
            'purchase_price'  => 'required|numeric|min:0',
            'sale_price'      => 'required|numeric|min:0',
            'quantity'        => 'required|numeric|min:0',
            'low_stock_alert' => 'nullable|numeric|min:0',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'description'     => 'nullable|string',
        ]);

        $data['user_id']   = $request->user()->id;
        $data['is_active'] = true;

        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/stock'), $imageName);
            $data['image_url'] = 'uploads/stock/' . $imageName;
        }

        $product = StockProduct::create($data);

        return response()->json([
            'success' => true,
            'data'    => $product,
            'message' => 'Product created successfully',
        ], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $product = StockProduct::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $product = StockProduct::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $data = $request->validate([
            'name'            => 'sometimes|required|string|max:255',
            'code'            => 'nullable|string|max:100',
            'category'        => 'nullable|string|max:100',
            'unit'            => 'nullable|string|max:50',
            'purchase_price'  => 'sometimes|required|numeric|min:0',
            'sale_price'      => 'sometimes|required|numeric|min:0',
            'quantity'        => 'sometimes|required|numeric|min:0',
            'low_stock_alert' => 'nullable|numeric|min:0',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'description'     => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_url && file_exists(public_path($product->image_url))) {
                unlink(public_path($product->image_url));
            }
            $image     = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/stock'), $imageName);
            $data['image_url'] = 'uploads/stock/' . $imageName;
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'data'    => $product->fresh(),
            'message' => 'Product updated successfully',
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $product = StockProduct::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        if ($product->image_url && file_exists(public_path($product->image_url))) {
            unlink(public_path($product->image_url));
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }

    public function categories(Request $request): JsonResponse
    {
        $cats = StockProduct::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->whereNotNull('category')
            ->pluck('category')
            ->unique()->sort()->values();

        return response()->json(['success' => true, 'data' => $cats]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $products = StockProduct::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->get()
            ->filter(fn($p) => $p->is_low_stock)
            ->values();

        return response()->json(['success' => true, 'data' => $products]);
    }
}
