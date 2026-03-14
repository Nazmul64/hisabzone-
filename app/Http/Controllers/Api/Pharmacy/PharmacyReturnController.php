<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyReturnController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PharmacyReturn::where('user_id', auth()->id());

        if ($request->filled('return_type'))
            $query->where('return_type', $request->return_type);
        if ($request->filled('date_from'))
            $query->whereDate('date', '>=', $request->date_from);
        if ($request->filled('date_to'))
            $query->whereDate('date', '<=', $request->date_to);

        $returns = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $returns,
            'summary' => [
                'total'            => $returns->count(),
                'customer_returns' => $returns->where('return_type', 'customer')->count(),
                'supplier_returns' => $returns->where('return_type', 'supplier')->count(),
                'total_amount'     => $returns->sum('amount'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'return_type'   => 'required|in:customer,supplier',
            'party_name'    => 'required|string|max:255',
            'medicine_name' => 'required|string|max:255',
            'quantity'      => 'required|integer|min:1',
            'amount'        => 'required|numeric|min:0',
            'reason'        => 'nullable|string|max:500',
            'date'          => 'required|date',
        ]);

        $return = PharmacyReturn::create(
            array_merge($validated, ['user_id' => auth()->id()])
        );

        return response()->json([
            'success' => true,
            'data'    => $return,
            'message' => 'Return recorded successfully',
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $return = PharmacyReturn::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'party_name'    => 'sometimes|required|string|max:255',
            'medicine_name' => 'sometimes|required|string|max:255',
            'quantity'      => 'sometimes|required|integer|min:1',
            'amount'        => 'sometimes|required|numeric|min:0',
            'reason'        => 'nullable|string|max:500',
            'date'          => 'sometimes|required|date',
        ]);

        $return->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $return->fresh(),
            'message' => 'Return updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $return = PharmacyReturn::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $return->delete();

        return response()->json([
            'success' => true,
            'message' => 'Return deleted successfully',
        ]);
    }
}
