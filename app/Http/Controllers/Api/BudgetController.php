<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Financemanage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        try {
            $month = $request->input('month', Carbon::now()->month);
            $year  = $request->input('year',  Carbon::now()->year);

            $budgets = Budget::with('category')
                ->where('month', $month)
                ->where('year',  $year)
                ->get()
                ->map(function ($b) use ($month, $year) {
                    // এই মাসে কত খরচ হয়েছে
                    $spent = (float) Financemanage::where('type', 'expense')
                        ->where('category_id', $b->category_id)
                        ->whereMonth('date', $month)
                        ->whereYear('date',  $year)
                        ->sum('amount');

                    return [
                        'id'            => $b->id,
                        'category_id'   => $b->category_id,
                        'category_name' => $b->category ? $b->category->name : 'অন্যান্য',
                        'category_icon' => $b->category ? $b->category->icon : 'category',
                        'limit_amount'  => (float) $b->limit_amount,
                        'spent'         => $spent,
                        'remaining'     => (float) $b->limit_amount - $spent,
                        'percentage'    => $b->limit_amount > 0
                            ? round(($spent / $b->limit_amount) * 100, 1)
                            : 0,
                        'month'         => $b->month,
                        'year'          => $b->year,
                    ];
                });

            return response()->json(['success' => true, 'data' => $budgets]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_id'  => 'required|exists:categories,id',
                'limit_amount' => 'required|numeric|min:0',
                'month'        => 'required|integer|between:1,12',
                'year'         => 'required|integer',
            ]);

            $budget = Budget::updateOrCreate(
                [
                    'category_id' => $request->category_id,
                    'month'       => $request->month,
                    'year'        => $request->year,
                ],
                ['limit_amount' => $request->limit_amount]
            );

            return response()->json([
                'success' => true,
                'data'    => $budget,
                'message' => 'Budget saved successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        $budget = Budget::find($id);
        if (!$budget) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        $budget->delete();
        return response()->json(['success' => true, 'message' => 'Budget deleted']);
    }
}
