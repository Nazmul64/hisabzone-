<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Financemanage;
use Illuminate\Http\Request;

class FinancemanageController extends Controller
{
    public function index()
    {
        $finances = Financemanage::with('category')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $finances,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|min:0',
            'type'        => 'required|in:income,expense',
            'category_id' => 'nullable|exists:categories,id',
            'date'        => 'required|date',
            'time'        => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $finance = Financemanage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Finance record created successfully',
            'data'    => $finance->load('category'),
        ], 201);
    }

    public function show(string $id)
    {
        $finance = Financemanage::with('category')->find($id);

        if (!$finance) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $finance,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $finance = Financemanage::find($id);

        if (!$finance) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ], 404);
        }

        $validated = $request->validate([
            'amount'      => 'sometimes|numeric|min:0',
            'type'        => 'sometimes|in:income,expense',
            'category_id' => 'nullable|exists:categories,id',
            'date'        => 'sometimes|date',
            'time'        => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $finance->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Finance record updated successfully',
            'data'    => $finance->load('category'),
        ], 200);
    }

    public function destroy(string $id)
    {
        $finance = Financemanage::find($id);

        if (!$finance) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ], 404);
        }

        $finance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Finance record deleted successfully',
        ], 200);
    }
}
