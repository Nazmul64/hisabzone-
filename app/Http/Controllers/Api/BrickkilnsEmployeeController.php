<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickkilnsEmployeeController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive',
        ]);

        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name',        'like', "%{$search}%")
                  ->orWhere('mobile',      'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->orderBy('name')->get();

        $summary = [
            'total'                => $employees->count(),
            'active'               => $employees->where('status', 'active')->count(),
            'inactive'             => $employees->where('status', 'inactive')->count(),
            'total_monthly_salary' => (float) $employees->where('status', 'active')->sum('monthly_salary'),
        ];

        return $this->ok(['employees' => $employees, 'summary' => $summary]);
    }

   public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'name'           => 'required|string|max:255',
        'designation'    => 'required|string|max:100',
        'mobile'         => 'nullable|string|max:20',
        'address'        => 'nullable|string',
        'monthly_salary' => 'nullable|numeric|min:0',
        'join_date'      => 'nullable|date',
        'status'         => 'nullable|in:active,inactive',
        'nid'            => 'nullable|string|max:50',
        'note'           => 'nullable|string',
    ]);

    // ✅ Fixed: manually set user_id
    $validated['user_id'] = auth()->id();

    $employee = Employee::create($validated);

    return $this->created($employee, 'কর্মচারী সফলভাবে যোগ করা হয়েছে');
}

    public function show(Employee $employee): JsonResponse
    {
        $employee->load('salaries');
        return $this->ok($employee);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'designation'    => 'sometimes|required|string|max:100',
            'mobile'         => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'monthly_salary' => 'nullable|numeric|min:0',
            'join_date'      => 'nullable|date',
            'status'         => 'nullable|in:active,inactive',
            'nid'            => 'nullable|string|max:50',
            'note'           => 'nullable|string',
        ]);

        $employee->update($validated);

        return $this->ok($employee, 'আপডেট সফল হয়েছে');
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();
        return $this->ok(null, 'কর্মচারী মুছে ফেলা হয়েছে');
    }
}
