<?php
namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryGardenArea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryGardenAreaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $areas = NurseryGardenArea::where('user_id', $request->user()->id)->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $areas,
            'summary' => ['total' => $areas->count(), 'total_plants' => $areas->sum('plant_count')],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'emoji'       => 'nullable|string|max:10',
            'plant_count' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:20',
        ]);

        $area = NurseryGardenArea::create([...$validated, 'user_id' => $request->user()->id]);

        return response()->json(['success' => true, 'data' => $area, 'message' => 'Garden area created successfully'], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $area = NurseryGardenArea::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json(['success' => true, 'data' => $area]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $area      = NurseryGardenArea::where('user_id', $request->user()->id)->findOrFail($id);
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'emoji'       => 'nullable|string|max:10',
            'plant_count' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:20',
        ]);

        $area->update($validated);

        return response()->json(['success' => true, 'data' => $area, 'message' => 'Garden area updated successfully']);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        NurseryGardenArea::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Garden area deleted successfully']);
    }
}
