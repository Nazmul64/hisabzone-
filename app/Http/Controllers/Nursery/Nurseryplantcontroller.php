<?php
namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryPlant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryPlantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NurseryPlant::where('user_id', $request->user()->id)->with('plantCategory');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")
                ->orWhere('category', 'like', "%$s%")
                ->orWhere('scientific_name', 'like', "%$s%"));
        }
        if ($request->filled('category'))               { $query->where('category', $request->category); }
        if ($request->get('low_stock') == '1')          { $query->whereRaw('quantity < min_stock'); }

        $plants = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $plants,
            'summary' => [
                'total'     => $plants->count(),
                'low_stock' => $plants->filter(fn($p) => $p->quantity < $p->min_stock)->count(),
                'normal'    => $plants->filter(fn($p) => $p->quantity >= $p->min_stock)->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                      => 'required|string|max:255',
            'scientific_name'           => 'nullable|string|max:255',
            'nursery_plant_category_id' => 'nullable|integer|exists:nursery_plant_categories,id',
            'category'                  => 'nullable|string|max:100',
            'price'                     => 'nullable|numeric|min:0',
            'quantity'                  => 'nullable|integer|min:0',
            'min_stock'                 => 'nullable|integer|min:0',
            'age'                       => 'nullable|string|max:50',
            'size'                      => 'nullable|string|max:50',
            'emoji'                     => 'nullable|string|max:10',
            'description'               => 'nullable|string',
        ]);

        $plant = NurseryPlant::create([...$validated, 'user_id' => $request->user()->id]);

        return response()->json(['success' => true, 'data' => $plant, 'message' => 'Plant created successfully'], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $plant = NurseryPlant::where('user_id', $request->user()->id)
            ->with(['plantCategory', 'careRecords'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $plant]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $plant     = NurseryPlant::where('user_id', $request->user()->id)->findOrFail($id);
        $validated = $request->validate([
            'name'                      => 'sometimes|string|max:255',
            'scientific_name'           => 'nullable|string|max:255',
            'nursery_plant_category_id' => 'nullable|integer|exists:nursery_plant_categories,id',
            'category'                  => 'nullable|string|max:100',
            'price'                     => 'nullable|numeric|min:0',
            'quantity'                  => 'nullable|integer|min:0',
            'min_stock'                 => 'nullable|integer|min:0',
            'age'                       => 'nullable|string|max:50',
            'size'                      => 'nullable|string|max:50',
            'emoji'                     => 'nullable|string|max:10',
            'description'               => 'nullable|string',
        ]);

        $plant->update($validated);

        return response()->json(['success' => true, 'data' => $plant, 'message' => 'Plant updated successfully']);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        NurseryPlant::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Plant deleted successfully']);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $plants = NurseryPlant::where('user_id', $request->user()->id)
            ->whereRaw('quantity < min_stock')->orderBy('quantity')->get();

        return response()->json(['success' => true, 'data' => $plants]);
    }
}
