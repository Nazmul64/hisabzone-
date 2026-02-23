<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        try {
            $wallets = Wallet::latest()->get()->map(fn($w) => $this->format($w));
            return response()->json(['success' => true, 'data' => $wallets]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'    => 'required|string|max:255',
                'balance' => 'nullable|numeric',
                'icon'    => 'nullable|string|max:100',
                'color'   => 'nullable|string|max:20',
            ]);

            $wallet = Wallet::create([
                'name'    => $request->name,
                'balance' => $request->input('balance', 0),
                'icon'    => $request->input('icon', 'account_balance_wallet'),
                'color'   => $request->input('color', '#0EA5E9'),
            ]);

            return response()->json(['success' => true, 'data' => $this->format($wallet), 'message' => 'Wallet created'], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $wallet = Wallet::find($id);
        if (!$wallet) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        try {
            $wallet->update($request->only(['name', 'balance', 'icon', 'color']));
            return response()->json(['success' => true, 'data' => $this->format($wallet->fresh())]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        $wallet = Wallet::find($id);
        if (!$wallet) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        $wallet->delete();
        return response()->json(['success' => true, 'message' => 'Wallet deleted']);
    }

    private function format($w): array
    {
        return [
            'id'      => $w->id,
            'name'    => $w->name,
            'balance' => (float) $w->balance,
            'icon'    => $w->icon,
            'color'   => $w->color,
        ];
    }
}
