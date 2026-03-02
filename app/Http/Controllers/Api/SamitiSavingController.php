<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiSaving;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiSavingController extends Controller
{
    public function index(Request $request)
    {
        $userId  = Auth::id();
        $query   = SamitiSaving::where('user_id', $userId)->with('member');

        if ($request->filled('member_id')) {
            $query->where('samiti_member_id', $request->member_id);
        }

        $records = $query->latest()->get()->map(fn($s) => [
            'id'          => $s->id,
            'member_id'   => $s->samiti_member_id,
            'member_name' => $s->member->name ?? '',
            'amount'      => $s->amount,
            'is_deposit'  => $s->is_deposit,
            'note'        => $s->note,
            'date'        => $s->date?->format('Y-m-d'),
        ]);

        $totalDeposit  = SamitiSaving::where('user_id', $userId)->where('is_deposit', true)->sum('amount');
        $totalWithdraw = SamitiSaving::where('user_id', $userId)->where('is_deposit', false)->sum('amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'records'        => $records,
                'total_deposit'  => $totalDeposit,
                'total_withdraw' => $totalWithdraw,
                'net_savings'    => $totalDeposit - $totalWithdraw,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'samiti_member_id' => 'required|integer',
            'amount'           => 'required|numeric|min:0.01',
            'is_deposit'       => 'required|boolean',
            'note'             => 'nullable|string|max:255',
            'date'             => 'nullable|date',
        ]);

        $saving = SamitiSaving::create([
            'user_id'          => Auth::id(),
            'samiti_member_id' => $request->samiti_member_id,
            'amount'           => $request->amount,
            'is_deposit'       => $request->is_deposit,
            'note'             => $request->note ?? ($request->is_deposit ? 'সঞ্চয় জমা' : 'উত্তোলন'),
            'date'             => $request->date ?? now()->toDateString(),
        ]);

        return response()->json(['success' => true, 'data' => $saving], 201);
    }

    public function destroy(string $id)
    {
        $saving = SamitiSaving::where('user_id', Auth::id())->findOrFail($id);
        $saving->delete();

        return response()->json(['success' => true, 'message' => 'Saving deleted']);
    }
}
