<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\RawMaterialPurchase;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickkilnsRawMaterialController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $materials = RawMaterial::orderBy('name')->get();

        $lowStock = $materials->filter(fn ($m) => $m->is_low_stock);

        $summary = [
            'total'           => $materials->count(),
            'low_stock_count' => $lowStock->count(),
            'low_stock_items' => $lowStock->pluck('name'),
        ];

        return $this->ok(['materials' => $materials, 'summary' => $summary]);
    }

    public function store(Request $req): JsonResponse
    {
        $validated = $req->validate([
            'name'            => 'required|string|max:255',
            'unit'            => 'required|string|max:50',
            'emoji'           => 'nullable|string|max:10',
            'stock'           => 'nullable|numeric|min:0',
            'used'            => 'nullable|numeric|min:0',
            'unit_price'      => 'nullable|numeric|min:0',
            'low_stock_alert' => 'nullable|numeric|min:0',
        ]);

        $material = RawMaterial::create($validated);

        return $this->created($material, 'কাঁচামাল যোগ করা হয়েছে');
    }

    public function show(RawMaterial $rawMaterial): JsonResponse
    {
        $rawMaterial->load('purchases');
        return $this->ok($rawMaterial);
    }

    public function update(Request $req, RawMaterial $rawMaterial): JsonResponse
    {
        $validated = $req->validate([
            'name'            => 'sometimes|required|string|max:255',
            'unit'            => 'sometimes|required|string|max:50',
            'emoji'           => 'nullable|string|max:10',
            'stock'           => 'nullable|numeric|min:0',
            'used'            => 'nullable|numeric|min:0',
            'unit_price'      => 'nullable|numeric|min:0',
            'low_stock_alert' => 'nullable|numeric|min:0',
        ]);

        $rawMaterial->update($validated);

        return $this->ok($rawMaterial, 'আপডেট সফল হয়েছে');
    }

    public function destroy(RawMaterial $rawMaterial): JsonResponse
    {
        $rawMaterial->delete();
        return $this->ok(null, 'কাঁচামাল মুছে ফেলা হয়েছে');
    }

    public function purchase(Request $req, RawMaterial $rawMaterial): JsonResponse
    {
        $validated = $req->validate([
            'date'          => 'required|date',
            'quantity'      => 'required|numeric|min:0.01',
            'unit_price'    => 'required|numeric|min:0',
            'supplier_id'   => 'nullable|integer',
            'supplier_name' => 'nullable|string|max:255',
            'note'          => 'nullable|string',
        ]);

        $validated['raw_material_id'] = $rawMaterial->id;
        $validated['total_cost']      = $validated['quantity'] * $validated['unit_price'];

        $purchase = RawMaterialPurchase::create($validated);

        $rawMaterial->increment('stock', $validated['quantity']);
        $rawMaterial->update(['unit_price' => $validated['unit_price']]);

        return $this->created(
            $purchase->load('rawMaterial'),
            'ক্রয় সফলভাবে রেকর্ড হয়েছে'
        );
    }

    public function use(Request $req, RawMaterial $rawMaterial): JsonResponse
    {
        $req->validate([
            'quantity' => 'required|numeric|min:0.01',
            'note'     => 'nullable|string',
        ]);

        if ($rawMaterial->stock < $req->quantity) {
            return $this->fail(
                'পর্যাপ্ত স্টক নেই। বর্তমান স্টক: ' . $rawMaterial->stock . ' ' . $rawMaterial->unit
            );
        }

        $rawMaterial->decrement('stock', $req->quantity);
        $rawMaterial->increment('used',  $req->quantity);

        return $this->ok($rawMaterial->fresh(), 'ব্যবহার রেকর্ড হয়েছে');
    }
}
