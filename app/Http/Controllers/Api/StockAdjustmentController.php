<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\StockProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = StockAdjustment::forUser(Auth::id())->with('product')->orderByDesc('date');

        if ($request->filled('product_id')) $query->where('product_id', $request->product_id);
        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    // type: 'add' | 'subtract' | 'set'
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:stock_products,id',
            'type'       => 'required|in:add,subtract,set',
            'quantity'   => 'required|numeric|min:0.01',
            'unit_cost'  => 'nullable|numeric|min:0',
            'reason'     => 'nullable|string|max:500',
            'date'       => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $product    = StockProduct::forUser(Auth::id())->findOrFail($validated['product_id']);
            $adjustment = StockAdjustment::create(array_merge($validated, [
                'user_id'      => Auth::id(),
                'product_name' => $product->name,
            ]));

            if ($validated['type'] === 'add') {
                $product->increment('quantity', $validated['quantity']);
            } elseif ($validated['type'] === 'subtract') {
                $product->update(['quantity' => max(0, $product->quantity - $validated['quantity'])]);
            } else { // set
                $product->update(['quantity' => $validated['quantity']]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data'    => $adjustment->load('product'),
                'message' => 'Stock adjustment created',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $adj = StockAdjustment::forUser(Auth::id())->findOrFail($id);

        DB::beginTransaction();
        try {
            $product = StockProduct::find($adj->product_id);
            if ($product) {
                if ($adj->type === 'add') {
                    $product->update(['quantity' => max(0, $product->quantity - $adj->quantity)]);
                } elseif ($adj->type === 'subtract') {
                    $product->increment('quantity', $adj->quantity);
                }
                // 'set' type reverse kora possible na, skip
            }
            $adj->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Adjustment deleted and stock reversed']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
