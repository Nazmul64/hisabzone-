<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiExpenseController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query  = SamitiExpense::where('user_id', $userId);

        if ($request->filled('category') && $request->category !== 'সব') {
            $query->where('category', $request->category);
        }

        $expenses = $query->latest()->get()->map(fn($e) => [
            'id'          => $e->id,
            'expense_id'  => $e->expense_id,
            'description' => $e->description,
            'category'    => $e->category,
            'amount'      => $e->amount,
            'date'        => $e->date?->format('Y-m-d'),
            'approved_by' => $e->approved_by,
            'is_paid'     => $e->is_paid,
        ]);

        $totalExpense = SamitiExpense::where('user_id', $userId)->sum('amount');
        $thisMonth    = SamitiExpense::where('user_id', $userId)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'expenses'      => $expenses,
                'total_expense' => $totalExpense,
                'this_month'    => $thisMonth,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'amount'      => 'required|numeric|min:0.01',
            'approved_by' => 'nullable|string|max:255',
            'date'        => 'nullable|date',
        ]);

        $userId = Auth::id();
        $count  = SamitiExpense::where('user_id', $userId)->count();

        $expense = SamitiExpense::create([
            'user_id'     => $userId,
            'expense_id'  => 'E' . str_pad($count + 1, 3, '0', STR_PAD_LEFT),
            'description' => $request->description,
            'category'    => $request->category,
            'amount'      => $request->amount,
            'date'        => $request->date ?? now()->toDateString(),
            'approved_by' => $request->approved_by ?? 'সভাপতি',
            'is_paid'     => true,
        ]);

        return response()->json(['success' => true, 'data' => $expense], 201);
    }

    public function destroy(string $id)
    {
        $expense = SamitiExpense::where('user_id', Auth::id())->findOrFail($id);
        $expense->delete();

        return response()->json(['success' => true, 'message' => 'Expense deleted']);
    }
}
