<?php
namespace App\Http\Controllers\Stock;
use App\Http\Controllers\Controller;
use App\Models\StockExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = StockExpense::forUser(Auth::id())->orderByDesc('date');
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }
        return response()->json([
            'success' => true,
            'data'    => $query->get(),
            'summary' => [
                'total_expenses'      => StockExpense::forUser(Auth::id())->sum('amount'),
                'today_expenses'      => StockExpense::forUser(Auth::id())->today()->sum('amount'),
                'this_month_expenses' => StockExpense::forUser(Auth::id())->thisMonth()->sum('amount'),
                'categories'          => StockExpense::forUser(Auth::id())->select('category')->distinct()->pluck('category'),
            ],
        ]);
    }
    public function store(Request $request)
    {
        $v = $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'amount'   => 'required|numeric|min:0.01',
            'date'     => 'required|date',
            'notes'    => 'nullable|string|max:500',
        ]);
        $expense = StockExpense::create(array_merge($v, ['user_id' => Auth::id()]));
        return response()->json(['success' => true, 'data' => $expense, 'message' => 'Expense added'], 201);
    }
    public function update(Request $request, $id)
    {
        $expense = StockExpense::forUser(Auth::id())->findOrFail($id);
        $v = $request->validate([
            'title'    => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:100',
            'amount'   => 'sometimes|required|numeric|min:0.01',
            'date'     => 'sometimes|required|date',
            'notes'    => 'nullable|string|max:500',
        ]);
        $expense->update($v);
        return response()->json(['success' => true, 'data' => $expense->fresh(), 'message' => 'Expense updated']);
    }
    public function destroy($id)
    {
        StockExpense::forUser(Auth::id())->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Expense deleted']);
    }
}
