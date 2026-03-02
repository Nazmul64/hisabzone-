<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SamitiFundTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SamitiFundController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $transactions = SamitiFundTransaction::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id'             => $t->id,
                'transaction_id' => $t->transaction_id,
                'type'           => $t->type,
                'description'    => $t->description,
                'amount'         => $t->amount,
                'is_credit'      => $t->is_credit,
                'reference'      => $t->reference,
                'date'           => $t->date?->format('Y-m-d'),
            ]);

        $totalCredit = SamitiFundTransaction::where('user_id', $userId)->where('is_credit', true)->sum('amount');
        $totalDebit  = SamitiFundTransaction::where('user_id', $userId)->where('is_credit', false)->sum('amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'transactions' => $transactions,
                'total_credit' => $totalCredit,
                'total_debit'  => $totalDebit,
                'balance'      => $totalCredit - $totalDebit,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'        => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'is_credit'   => 'required|boolean',
            'reference'   => 'nullable|string|max:100',
            'date'        => 'nullable|date',
        ]);

        $userId = Auth::id();
        $count  = SamitiFundTransaction::where('user_id', $userId)->count();

        $tx = SamitiFundTransaction::create([
            'user_id'        => $userId,
            'transaction_id' => 'TX-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT),
            'type'           => $request->type,
            'description'    => $request->description,
            'amount'         => $request->amount,
            'is_credit'      => $request->is_credit,
            'reference'      => $request->reference ?? ('REF-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT)),
            'date'           => $request->date ?? now()->toDateString(),
        ]);

        return response()->json(['success' => true, 'data' => $tx], 201);
    }
}
