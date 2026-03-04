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
        $query = StockProduct::forUser($request->user()->id)->active();

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

        $data['user_id'] = $request->user()->id;

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

    public function show(Request $request, StockProduct $stockProduct): JsonResponse
    {
        abort_if($stockProduct->user_id !== $request->user()->id, 403);
        return response()->json(['success' => true, 'data' => $stockProduct]);
    }

    public function update(Request $request, StockProduct $stockProduct): JsonResponse
    {
        abort_if($stockProduct->user_id !== $request->user()->id, 403);

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
            if ($stockProduct->image_url && file_exists(public_path($stockProduct->image_url))) {
                unlink(public_path($stockProduct->image_url));
            }
            $image     = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/stock'), $imageName);
            $data['image_url'] = 'uploads/stock/' . $imageName;
        }

        $stockProduct->update($data);

        return response()->json([
            'success' => true,
            'data'    => $stockProduct->fresh(),
            'message' => 'Product updated successfully',
        ]);
    }

    public function destroy(Request $request, StockProduct $stockProduct): JsonResponse
    {
        abort_if($stockProduct->user_id !== $request->user()->id, 403);

        if ($stockProduct->image_url && file_exists(public_path($stockProduct->image_url))) {
            unlink(public_path($stockProduct->image_url));
        }
        $stockProduct->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }

    public function categories(Request $request): JsonResponse
    {
        $cats = StockProduct::forUser($request->user()->id)
            ->active()->whereNotNull('category')
            ->pluck('category')->unique()->sort()->values();

        return response()->json(['success' => true, 'data' => $cats]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $products = StockProduct::forUser($request->user()->id)->active()->lowStock()->get();
        return response()->json(['success' => true, 'data' => $products]);
    }
}
