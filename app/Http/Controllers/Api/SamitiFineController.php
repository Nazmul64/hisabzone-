<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiFine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiFineController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $fines = SamitiFine::where('user_id', $userId)
            ->with('member')
            ->latest()
            ->get()
            ->map(fn($f) => [
                'id'          => $f->id,
                'fine_id'     => $f->fine_id,
                'member_id'   => $f->samiti_member_id,
                'member_name' => $f->member->name ?? '',
                'reason'      => $f->reason,
                'amount'      => $f->amount,
                'date'        => $f->date?->format('Y-m-d'),
                'is_paid'     => $f->is_paid,
                'paid_date'   => $f->paid_date?->format('Y-m-d'),
            ]);

        $totalFines = SamitiFine::where('user_id', $userId)->sum('amount');
        $totalPaid  = SamitiFine::where('user_id', $userId)->where('is_paid', true)->sum('amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'fines'         => $fines,
                'total_fines'   => $totalFines,
                'total_paid'    => $totalPaid,
                'total_pending' => $totalFines - $totalPaid,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'samiti_member_id' => 'required|integer',
            'reason'           => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'date'             => 'nullable|date',
        ]);

        $userId = Auth::id();
        $count  = SamitiFine::where('user_id', $userId)->count();

        $fine = SamitiFine::create([
            'user_id'          => $userId,
            'samiti_member_id' => $request->samiti_member_id,
            'fine_id'          => 'F' . str_pad($count + 1, 3, '0', STR_PAD_LEFT),
            'reason'           => $request->reason,
            'amount'           => $request->amount,
            'date'             => $request->date ?? now()->toDateString(),
            'is_paid'          => false,
        ]);

        return response()->json(['success' => true, 'data' => $fine], 201);
    }

    public function toggle(string $id)
    {
        $fine            = SamitiFine::where('user_id', Auth::id())->findOrFail($id);
        $fine->is_paid   = !$fine->is_paid;
        $fine->paid_date = $fine->is_paid ? now()->toDateString() : null;
        $fine->save();

        return response()->json(['success' => true, 'data' => $fine]);
    }

    public function destroy(string $id)
    {
        $fine = SamitiFine::where('user_id', Auth::id())->findOrFail($id);
        $fine->delete();

        return response()->json(['success' => true, 'message' => 'Fine deleted']);
    }
}
