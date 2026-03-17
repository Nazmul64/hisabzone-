<?php
namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryEmployee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryEmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employees = NurseryEmployee::where('user_id', $request->user()->id)->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $employees,
            'summary' => ['total' => $employees->count(), 'total_salary' => $employees->sum('salary')],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'position'     => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
            'salary'       => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'emoji'        => 'nullable|string|max:10',
            'notes'        => 'nullable|string',
        ]);

        $employee = NurseryEmployee::create([...$validated, 'user_id' => $request->user()->id]);

        return response()->json(['success' => true, 'data' => $employee, 'message' => 'Employee created successfully'], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $employee = NurseryEmployee::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json(['success' => true, 'data' => $employee]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $employee  = NurseryEmployee::where('user_id', $request->user()->id)->findOrFail($id);
        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'position'     => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
            'salary'       => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'emoji'        => 'nullable|string|max:10',
            'notes'        => 'nullable|string',
        ]);

        $employee->update($validated);

        return response()->json(['success' => true, 'data' => $employee, 'message' => 'Employee updated successfully']);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        NurseryEmployee::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Employee deleted successfully']);
    }
}
