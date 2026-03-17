<?php
namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryFertilizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryFertilizerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $fertilizers = NurseryFertilizer::where('user_id', $request->user()->id)->orderBy('name')->get();

        return response()->json(['success' => true, 'data' => $fertilizers, 'summary' => ['total' => $fertilizers->count()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'emoji'           => 'nullable|string|max:10',
            'total_quantity'  => 'nullable|string|max:50',
            'used_quantity'   => 'nullable|string|max:50',
            'unit'            => 'nullable|string|max:20',
            'total_qty_value' => 'nullable|numeric|min:0',
            'used_qty_value'  => 'nullable|numeric|min:0',
            'color'           => 'nullable|string|max:20',
            'notes'           => 'nullable|string',
        ]);

        $fertilizer = NurseryFertilizer::create([...$validated, 'user_id' => $request->user()->id]);

        return response()->json(['success' => true, 'data' => $fertilizer, 'message' => 'Fertilizer created successfully'], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $fertilizer = NurseryFertilizer::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json(['success' => true, 'data' => $fertilizer]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $fertilizer = NurseryFertilizer::where('user_id', $request->user()->id)->findOrFail($id);
        $validated  = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'emoji'           => 'nullable|string|max:10',
            'total_quantity'  => 'nullable|string|max:50',
            'used_quantity'   => 'nullable|string|max:50',
            'unit'            => 'nullable|string|max:20',
            'total_qty_value' => 'nullable|numeric|min:0',
            'used_qty_value'  => 'nullable|numeric|min:0',
            'color'           => 'nullable|string|max:20',
            'notes'           => 'nullable|string',
        ]);

        $fertilizer->update($validated);

        return response()->json(['success' => true, 'data' => $fertilizer, 'message' => 'Fertilizer updated successfully']);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        NurseryFertilizer::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Fertilizer deleted successfully']);
    }
}
