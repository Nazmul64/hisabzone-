<?php
namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryPlantCareRecord;
use App\Models\Nursery\NurseryPlant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryPlantCareController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NurseryPlantCareRecord::where('user_id', $request->user()->id)->with('plant');

        if ($request->filled('plant_id'))  { $query->where('nursery_plant_id', $request->plant_id); }
        if ($request->filled('care_type')) { $query->where('care_type', $request->care_type); }
        if ($request->filled('date_from')) { $query->whereDate('date', '>=', $request->date_from); }
        if ($request->filled('date_to'))   { $query->whereDate('date', '<=', $request->date_to); }

        $records = $query->orderByDesc('date')->get();

        return response()->json([
            'success' => true,
            'data'    => $records,
            'summary' => [
                'total'       => $records->count(),
                'watering'    => $records->where('care_type', 'watering')->count(),
                'pruning'     => $records->where('care_type', 'pruning')->count(),
                'treatment'   => $records->where('care_type', 'treatment')->count(),
                'fertilizing' => $records->where('care_type', 'fertilizing')->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nursery_plant_id' => 'nullable|integer|exists:nursery_plants,id',
            'plant_name'       => 'nullable|string|max:255',
            'care_type'        => 'required|in:watering,pruning,treatment,fertilizing,other',
            'care_type_label'  => 'nullable|string|max:100',
            'emoji'            => 'nullable|string|max:10',
            'date'             => 'required|date',
            'note'             => 'nullable|string',
        ]);

        if (!empty($validated['nursery_plant_id']) && empty($validated['plant_name'])) {
            $validated['plant_name'] = NurseryPlant::find($validated['nursery_plant_id'])?->name;
        }

        $record = NurseryPlantCareRecord::create([...$validated, 'user_id' => $request->user()->id]);

        return response()->json(['success' => true, 'data' => $record->load('plant'), 'message' => 'Care record created successfully'], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $record = NurseryPlantCareRecord::where('user_id', $request->user()->id)
            ->with('plant')->findOrFail($id);

        return response()->json(['success' => true, 'data' => $record]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $record    = NurseryPlantCareRecord::where('user_id', $request->user()->id)->findOrFail($id);
        $validated = $request->validate([
            'nursery_plant_id' => 'nullable|integer|exists:nursery_plants,id',
            'plant_name'       => 'nullable|string|max:255',
            'care_type'        => 'sometimes|in:watering,pruning,treatment,fertilizing,other',
            'care_type_label'  => 'nullable|string|max:100',
            'emoji'            => 'nullable|string|max:10',
            'date'             => 'sometimes|date',
            'note'             => 'nullable|string',
        ]);

        $record->update($validated);

        return response()->json(['success' => true, 'data' => $record->fresh('plant'), 'message' => 'Care record updated successfully']);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        NurseryPlantCareRecord::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Care record deleted successfully']);
    }
}
