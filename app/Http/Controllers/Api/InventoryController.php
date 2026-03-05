<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\TailorInventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function uid(Request $r): int { return $r->user()->id; }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => TailorInventory::where('user_id', $this->uid($request))->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cloth_name'     => 'required|string',
            'quantity'       => 'required|integer|min:0',
            'purchase_price' => 'nullable|numeric',
            'supplier'       => 'nullable|string',
        ]);

        $item = TailorInventory::create([...$data, 'user_id' => $this->uid($request)]);
        return response()->json(['success' => true, 'message' => 'স্টক যোগ হয়েছে', 'data' => $item], 201);
    }

    public function show(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'data'    => TailorInventory::where('user_id', $this->uid($request))->findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = TailorInventory::where('user_id', $this->uid($request))->findOrFail($id);
        $item->update($request->validate([
            'cloth_name'     => 'sometimes|string',
            'quantity'       => 'sometimes|integer|min:0',
            'purchase_price' => 'nullable|numeric',
            'supplier'       => 'nullable|string',
        ]));
        return response()->json(['success' => true, 'message' => 'আপডেট হয়েছে', 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        TailorInventory::where('user_id', $this->uid($request))->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'মুছে ফেলা হয়েছে']);
    }

    // PATCH /inventory/{id}/stock
    public function updateStock(Request $request, $id)
    {
        $item = TailorInventory::where('user_id', $this->uid($request))->findOrFail($id);
        $item->update(['quantity' => $request->validate(['quantity' => 'required|integer|min:0'])['quantity']]);
        return response()->json(['success' => true, 'message' => 'স্টক আপডেট হয়েছে', 'data' => $item]);
    }

    // GET /inventory/low-stock/list
    public function lowStock(Request $request)
    {
        $items = TailorInventory::where('user_id', $this->uid($request))
            ->whereRaw('quantity < low_stock_threshold')
            ->get();
        return response()->json(['success' => true, 'data' => $items]);
    }
}
