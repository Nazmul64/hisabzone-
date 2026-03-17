<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\TailorDealer;
use Illuminate\Http\Request;

class DealerController extends Controller
{
    private function uid(Request $r): int { return $r->user()->id; }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => TailorDealer::where('user_id', $this->uid($request))->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
            'address'  => 'nullable|string|max:500',
            'products' => 'nullable|string|max:500',
        ]);

        $dealer = TailorDealer::create([...$data, 'user_id' => $this->uid($request)]);

        return response()->json([
            'success' => true,
            'message' => 'ডিলার যোগ হয়েছে',
            'data'    => $dealer,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'data'    => TailorDealer::where('user_id', $this->uid($request))->findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $dealer = TailorDealer::where('user_id', $this->uid($request))->findOrFail($id);

        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'phone'          => 'sometimes|required|string|max:20',
            'address'        => 'nullable|string|max:500',
            'products'       => 'nullable|string|max:500',
            'total_purchase' => 'nullable|numeric|min:0',
        ]);

        $dealer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'ডিলার আপডেট হয়েছে',
            'data'    => $dealer->fresh(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        TailorDealer::where('user_id', $this->uid($request))->findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'ডিলার মুছে ফেলা হয়েছে',
        ]);
    }
}
