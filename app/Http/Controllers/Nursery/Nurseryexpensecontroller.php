<?php
namespace App\Http\Controllers\Nursery;

use App\Http\Controllers\Controller;
use App\Models\Nursery\NurseryExpense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NurseryExpense::where('user_id', $request->user()->id);

        if ($request->filled('date_from')) { $query->whereDate('date', '>=', $request->date_from); }
        if ($request->filled('date_to'))   { $query->whereDate('date', '<=', $request->date_to); }
        if ($request->filled('type'))      { $query->where('type', 'like', '%' . $request->type . '%'); }

        $expenses = $query->orderByDesc('date')->get();

        return response()->json([
            'success' => true,
            'data'    => $expenses,
            'summary' => ['total' => $expenses->count(), 'total_amount' => $expenses->sum('amount')],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'   => 'required|string|max:255',
            'emoji'  => 'nullable|string|max:10',
            'amount' => 'required|numeric|min:0',
            'date'   => 'required|date',
            'color'  => 'nullable|string|max:20',
            'notes'  => 'nullable|string',
        ]);

        $expense = NurseryExpense::create([...$validated, 'user_id' => $request->user()->id]);

        return response()->json(['success' => true, 'data' => $expense, 'message' => 'Expense created successfully'], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $expense = NurseryExpense::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json(['success' => true, 'data' => $expense]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $expense   = NurseryExpense::where('user_id', $request->user()->id)->findOrFail($id);
        $validated = $request->validate([
            'type'   => 'sometimes|string|max:255',
            'emoji'  => 'nullable|string|max:10',
            'amount' => 'sometimes|numeric|min:0',
            'date'   => 'sometimes|date',
            'color'  => 'nullable|string|max:20',
            'notes'  => 'nullable|string',
        ]);

        $expense->update($validated);

        return response()->json(['success' => true, 'data' => $expense, 'message' => 'Expense updated successfully']);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        NurseryExpense::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Expense deleted successfully']);
    }
}
