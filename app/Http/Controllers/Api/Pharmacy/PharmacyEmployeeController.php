<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyEmployee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyEmployeeController extends Controller
{
    public function index(): JsonResponse
    {
        $employees = PharmacyEmployee::where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $employees,
            'summary' => [
                'total'        => $employees->count(),
                'total_salary' => $employees->sum('salary'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'position'     => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'salary'       => 'required|numeric|min:0',
            'joining_date' => 'nullable|date',
            'address'      => 'nullable|string|max:500',
            'emoji'        => 'nullable|string|max:10',
            'color'        => 'nullable|string|max:20',
        ]);

        $employee = PharmacyEmployee::create(
            array_merge($validated, ['user_id' => auth()->id()])
        );

        return response()->json([
            'success' => true,
            'data'    => $employee,
            'message' => 'Employee added successfully',
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $employee = PharmacyEmployee::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'position'     => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'salary'       => 'sometimes|required|numeric|min:0',
            'joining_date' => 'nullable|date',
            'address'      => 'nullable|string|max:500',
            'emoji'        => 'nullable|string|max:10',
            'color'        => 'nullable|string|max:20',
            'is_active'    => 'nullable|boolean',
        ]);

        $employee->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $employee->fresh(),
            'message' => 'Employee updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $employee = PharmacyEmployee::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully',
        ]);
    }
}
