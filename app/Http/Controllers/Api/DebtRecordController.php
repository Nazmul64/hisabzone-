<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DebtRecord;
use Illuminate\Http\Request;

class DebtRecordController extends Controller
{
    public function index()
    {
        try {
            $debts = DebtRecord::latest()->get()->map(fn($d) => $this->format($d));
            return response()->json(['success' => true, 'data' => $debts]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'person_name' => 'required|string|max:255',
                'amount'      => 'required|numeric|min:0',
                'type'        => 'required|in:given,taken', // given=দিয়েছি, taken=নিয়েছি
                'date'        => 'required|date',
                'due_date'    => 'nullable|date',
                'note'        => 'nullable|string',
                'is_settled'  => 'nullable|boolean',
            ]);

            $debt = DebtRecord::create($request->only([
                'person_name', 'amount', 'type', 'date', 'due_date', 'note', 'is_settled',
            ]));

            return response()->json([
                'success' => true, 'data' => $this->format($debt), 'message' => 'Debt record created',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $debt = DebtRecord::find($id);
        if (!$debt) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        try {
            $debt->update($request->only(['person_name', 'amount', 'type', 'date', 'due_date', 'note', 'is_settled']));
            return response()->json(['success' => true, 'data' => $this->format($debt->fresh())]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        $debt = DebtRecord::find($id);
        if (!$debt) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        $debt->delete();
        return response()->json(['success' => true, 'message' => 'Debt record deleted']);
    }

    // মিটমাট করুন (settle)
    public function settle(string $id)
    {
        $debt = DebtRecord::find($id);
        if (!$debt) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        $debt->update(['is_settled' => true]);
        return response()->json(['success' => true, 'data' => $this->format($debt->fresh()), 'message' => 'Settled']);
    }

    private function format($d): array
    {
        return [
            'id'          => $d->id,
            'person_name' => $d->person_name,
            'amount'      => (float) $d->amount,
            'type'        => $d->type,
            'date'        => $d->date,
            'due_date'    => $d->due_date,
            'note'        => $d->note,
            'is_settled'  => (bool) $d->is_settled,
        ];
    }
}
