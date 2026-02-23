<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Saving;
use Illuminate\Http\Request;

class SavingController extends Controller
{
    public function index()
    {
        try {
            $savings = Saving::latest()->get()->map(fn($s) => $this->format($s));
            return response()->json(['success' => true, 'data' => $savings]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title'         => 'required|string|max:255',
                'target_amount' => 'required|numeric|min:0',
                'saved_amount'  => 'nullable|numeric|min:0',
                'deadline'      => 'nullable|date',
                'note'          => 'nullable|string',
            ]);

            $saving = Saving::create([
                'title'         => $request->title,
                'target_amount' => $request->target_amount,
                'saved_amount'  => $request->input('saved_amount', 0),
                'deadline'      => $request->deadline,
                'note'          => $request->note,
            ]);

            return response()->json([
                'success' => true, 'data' => $this->format($saving), 'message' => 'Saving goal created',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $saving = Saving::find($id);
        if (!$saving) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        try {
            $request->validate([
                'title'         => 'sometimes|string|max:255',
                'target_amount' => 'sometimes|numeric|min:0',
                'saved_amount'  => 'sometimes|numeric|min:0',
                'deadline'      => 'nullable|date',
                'note'          => 'nullable|string',
            ]);
            $saving->update($request->only(['title', 'target_amount', 'saved_amount', 'deadline', 'note']));
            return response()->json(['success' => true, 'data' => $this->format($saving->fresh())]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        $saving = Saving::find($id);
        if (!$saving) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        $saving->delete();
        return response()->json(['success' => true, 'message' => 'Saving deleted']);
    }

    private function format($s): array
    {
        return [
            'id'             => $s->id,
            'title'          => $s->title,
            'target_amount'  => (float) $s->target_amount,
            'saved_amount'   => (float) $s->saved_amount,
            'remaining'      => (float) ($s->target_amount - $s->saved_amount),
            'percentage'     => $s->target_amount > 0
                ? round(($s->saved_amount / $s->target_amount) * 100, 1) : 0,
            'deadline'       => $s->deadline,
            'note'           => $s->note,
            'is_completed'   => $s->saved_amount >= $s->target_amount,
            'created_at'     => $s->created_at,
        ];
    }
}
