<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    // GET accounts
    public function index()
    {
        try {
            $accounts = Account::latest()->get()->map(fn($a) => $this->format($a));
            $setting  = UserSetting::where('key', 'active_account')->first();
            $activeId = $setting ? (int) $setting->value : ($accounts->first()['id'] ?? null);

            return response()->json([
                'success' => true,
                'data'    => [
                    'accounts'  => $accounts,
                    'active_id' => $activeId,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST accounts
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $account = Account::create(['name' => $request->name]);

            // প্রথম account হলে active করে দাও
            $count = Account::count();
            if ($count === 1) {
                UserSetting::updateOrCreate(
                    ['key' => 'active_account'],
                    ['value' => $account->id]
                );
            }

            return response()->json([
                'success' => true,
                'data'    => $this->format($account),
                'message' => 'Account created',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST accounts/switch
    public function switchAccount(Request $request)
    {
        try {
            $request->validate(['account_id' => 'required|exists:accounts,id']);

            UserSetting::updateOrCreate(
                ['key' => 'active_account'],
                ['value' => $request->account_id]
            );

            $account = Account::find($request->account_id);

            return response()->json([
                'success' => true,
                'data'    => $this->format($account),
                'message' => 'Account switched',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // DELETE accounts/{id}
    public function destroy(string $id)
    {
        $account = Account::find($id);
        if (!$account) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        try {
            // Active account মুছতে দেওয়া হবে না
            $setting  = UserSetting::where('key', 'active_account')->first();
            $activeId = $setting ? (int) $setting->value : null;

            if ($activeId === (int) $id) {
                return response()->json(['success' => false, 'message' => 'সক্রিয় একাউন্ট মুছা যাবে না'], 422);
            }

            $account->delete();
            return response()->json(['success' => true, 'message' => 'Account deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function format($a): array
    {
        return [
            'id'         => $a->id,
            'name'       => $a->name,
            'created_at' => $a->created_at,
        ];
    }
}
