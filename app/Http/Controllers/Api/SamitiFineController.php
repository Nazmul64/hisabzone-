<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiFine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiFineController extends Controller
{
    // ── GET /samiti/fines ─────────────────────────────────────────────────
    public function index()
    {
        $userId = Auth::id();

        $fines = SamitiFine::where('user_id', $userId)
            ->with('member')
            ->latest()
            ->get()
            ->map(fn($f) => [
                'id'               => $f->id,
                'samiti_member_id' => $f->samiti_member_id,   // ✅ edit-এর জন্য
                'member_name'      => $f->member->name ?? '',
                'reason'           => $f->reason,
                'amount'           => (float) $f->amount,      // ✅ float cast — String bug fix
                'date'             => $f->date?->format('Y-m-d'),
                'is_paid'          => (bool) $f->is_paid,
                'paid_date'        => $f->paid_date?->format('Y-m-d'),
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'fines'         => $fines,
                'total_fines'   => (float) SamitiFine::where('user_id', $userId)->sum('amount'),
                'total_paid'    => (float) SamitiFine::where('user_id', $userId)->where('is_paid', true)->sum('amount'),
                'total_pending' => (float) SamitiFine::where('user_id', $userId)->where('is_paid', false)->sum('amount'),
            ],
        ]);
    }

    // ── POST /samiti/fines ────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'samiti_member_id' => 'required|integer',
            'reason'           => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'date'             => 'nullable|date',
        ]);

        $fine = SamitiFine::create([
            'user_id'          => Auth::id(),
            'samiti_member_id' => $request->samiti_member_id,
            'reason'           => $request->reason,
            'amount'           => $request->amount,
            'date'             => $request->date ?? now()->toDateString(),
            'is_paid'          => false,
        ]);

        return response()->json(['success' => true, 'data' => $fine], 201);
    }

    // ── PUT /samiti/fines/{id} ─────────────────────────────────────────────
    // ✅ নতুন: এডিট করলে এই method call হবে — নতুন record হবে না
    public function update(Request $request, string $id)
    {
        $request->validate([
            'samiti_member_id' => 'required|integer',
            'reason'           => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'date'             => 'nullable|date',
        ]);

        $fine = SamitiFine::where('user_id', Auth::id())->findOrFail($id);

        $fine->update([
            'samiti_member_id' => $request->samiti_member_id,
            'reason'           => $request->reason,
            'amount'           => $request->amount,
            'date'             => $request->date ?? $fine->date,
        ]);

        return response()->json(['success' => true, 'data' => $fine]);
    }

    // ── PATCH /samiti/fines/{id}/toggle ───────────────────────────────────
    public function toggle(string $id)
    {
        $fine = SamitiFine::where('user_id', Auth::id())->findOrFail($id);
        $fine->is_paid   = !$fine->is_paid;
        $fine->paid_date = $fine->is_paid ? now()->toDateString() : null;
        $fine->save();

        return response()->json(['success' => true, 'data' => $fine]);
    }

    // ── DELETE /samiti/fines/{id} ─────────────────────────────────────────
    public function destroy(string $id)
    {
        $fine = SamitiFine::where('user_id', Auth::id())->findOrFail($id);
        $fine->delete();

        return response()->json(['success' => true, 'message' => 'Fine deleted']);
    }
}
