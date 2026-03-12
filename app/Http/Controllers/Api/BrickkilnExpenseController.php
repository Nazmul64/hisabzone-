<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrickkilnExpenseController extends Controller
{
    use ApiResponse;

    public function index(Request $req): JsonResponse
    {
        $req->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'category'  => 'nullable|string|max:100',
        ]);

        $q = Expense::query();

        if ($req->filled('date_from')) $q->whereDate('date', '>=', $req->date_from);
        if ($req->filled('date_to'))   $q->whereDate('date', '<=', $req->date_to);
        if ($req->filled('category'))  $q->where('category', $req->category);

        $expenses = $q->orderBy('date', 'desc')->get();

        $summary = [
            'total'       => (float) $expenses->sum('amount'),
            'count'       => $expenses->count(),
            'by_category' => $expenses
                ->groupBy('category')
                ->map(fn ($g) => (float) $g->sum('amount')),
        ];

        return $this->ok($expenses);
    }

    public function store(Request $req): JsonResponse
    {
        $validated = $req->validate([
            'date'        => 'required|date',
            'category'    => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'emoji'       => 'nullable|string|max:10',
            'note'        => 'nullable|string',
        ]);

        $expense = Expense::create($validated);

        return $this->created($expense, 'খরচ রেকর্ড হয়েছে');
    }

    public function update(Request $req, Expense $expense): JsonResponse
    {
        $validated = $req->validate([
            'date'        => 'sometimes|required|date',
            'category'    => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount'      => 'sometimes|required|numeric|min:0.01',
            'emoji'       => 'nullable|string|max:10',
            'note'        => 'nullable|string',
        ]);

        $expense->update($validated);

        return $this->ok($expense, 'আপডেট সফল হয়েছে');
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();
        return $this->ok(null, 'খরচ মুছে ফেলা হয়েছে');
    }

    public function categories(): JsonResponse
    {
        $defaults = [
            'শ্রমিক মজুরি',
            'কয়লা ক্রয়',
            'মাটি খরচ',
            'পরিবহন',
            'বিদ্যুৎ বিল',
            'অন্যান্য',
        ];

        $used = Expense::distinct()->orderBy('category')->pluck('category');
        $all  = collect($defaults)->merge($used)->unique()->values();

        return $this->ok($all);
    }
}
