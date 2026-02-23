<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdFreeController extends Controller
{
    // GET ad-free/status
    public function status(Request $request)
    {
        try {
            $setting = UserSetting::firstOrCreate(
                ['key' => 'ad_free'],
                ['value' => json_encode(['is_ad_free' => false, 'expires_at' => null, 'activated_at' => null])]
            );

            $data = json_decode($setting->value, true) ?? [];
            $isAdFree = $data['is_ad_free'] ?? false;

            // মেয়াদ শেষ হয়ে গেলে reset
            if ($isAdFree && isset($data['expires_at']) && $data['expires_at'] !== null) {
                $expiresAt = Carbon::parse($data['expires_at']);
                if ($expiresAt->isPast()) {
                    $isAdFree = false;
                    $data['is_ad_free'] = false;
                    $setting->update(['value' => json_encode($data)]);
                }
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'is_ad_free'    => $isAdFree,
                    'expires_at'    => $data['expires_at'] ?? null,
                    'activated_at'  => $data['activated_at'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST ad-free/activate
    public function activate(Request $request)
    {
        try {
            $expiresAt = Carbon::now()->addDays(30)->toDateTimeString();

            $data = [
                'is_ad_free'   => true,
                'expires_at'   => $expiresAt,
                'activated_at' => Carbon::now()->toDateTimeString(),
            ];

            UserSetting::updateOrCreate(
                ['key' => 'ad_free'],
                ['value' => json_encode($data)]
            );

            return response()->json([
                'success' => true,
                'data'    => [
                    'is_ad_free'   => true,
                    'expires_at'   => $expiresAt,
                ],
                'message' => 'Ad-free activated for 30 days',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // DELETE ad-free/deactivate
    public function deactivate()
    {
        try {
            UserSetting::where('key', 'ad_free')->update([
                'value' => json_encode(['is_ad_free' => false, 'expires_at' => null]),
            ]);
            return response()->json(['success' => true, 'message' => 'Ad-free deactivated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
