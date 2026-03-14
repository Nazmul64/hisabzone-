<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyExpense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PharmacyExpense::where('user_id', auth()->id());

        if ($request->filled('date_from')) $query->whereDate('date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('date', '<=', $request->date_to);
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->month)->whereYear('date', $request->year);
        }

        $expenses = $query->latest('date')->get();

        return response()->json([
            'success' => true,
            'data'    => $expenses,
            'summary' => [
                'total'        => $expenses->count(),
                'total_amount' => $expenses->sum('amount'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'   => 'required|string|max:100',
            'emoji'  => 'nullable|string|max:10',
            'amount' => 'required|numeric|min:0',
            'date'   => 'required|date',
            'color'  => 'nullable|string|max:20',
            'note'   => 'nullable|string',
        ]);

        $expense = PharmacyExpense::create(
            array_merge($validated, ['user_id' => auth()->id()])
        );

        return response()->json([
            'success' => true,
            'data'    => $expense,
            'message' => 'Expense recorded successfully',
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $expense = PharmacyExpense::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'type'   => 'sometimes|required|string|max:100',
            'emoji'  => 'nullable|string|max:10',
            'amount' => 'sometimes|required|numeric|min:0',
            'date'   => 'sometimes|required|date',
            'color'  => 'nullable|string|max:20',
            'note'   => 'nullable|string',
        ]);

        $expense->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $expense->fresh(),
            'message' => 'Expense updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $expense = PharmacyExpense::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully',
        ]);
    }
}
