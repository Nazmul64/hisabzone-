<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    // GET pin/status
    public function status()
    {
        try {
            $setting   = UserSetting::where('key', 'pin')->first();
            $isPinSet  = $setting && !empty($setting->value);
            $enabled   = UserSetting::where('key', 'pin_enabled')->value('value');

            return response()->json([
                'success' => true,
                'data'    => [
                    'is_set'     => $isPinSet,
                    'is_enabled' => $enabled === '1' || $enabled === 'true',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST pin/set
    public function setPin(Request $request)
    {
        try {
            $request->validate([
                'pin' => 'required|string|size:4|regex:/^\d{4}$/',
            ], [
                'pin.size'  => 'পিন অবশ্যই ৪ সংখ্যার হতে হবে',
                'pin.regex' => 'পিন শুধু সংখ্যা হতে পারে',
            ]);

            UserSetting::updateOrCreate(
                ['key' => 'pin'],
                ['value' => Hash::make($request->pin)]
            );

            UserSetting::updateOrCreate(
                ['key' => 'pin_enabled'],
                ['value' => '1']
            );

            return response()->json([
                'success' => true,
                'message' => 'পিন সেট হয়েছে',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST pin/verify
    public function verify(Request $request)
    {
        try {
            $request->validate([
                'pin' => 'required|string|size:4',
            ]);

            $setting = UserSetting::where('key', 'pin')->first();
            if (!$setting || empty($setting->value)) {
                return response()->json(['success' => false, 'message' => 'পিন সেট নেই'], 422);
            }

            if (!Hash::check($request->pin, $setting->value)) {
                return response()->json(['success' => false, 'message' => 'ভুল পিন'], 401);
            }

            return response()->json(['success' => true, 'message' => 'পিন সঠিক']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST pin/toggle
    public function toggle(Request $request)
    {
        try {
            $request->validate(['enabled' => 'required|boolean']);

            UserSetting::updateOrCreate(
                ['key' => 'pin_enabled'],
                ['value' => $request->enabled ? '1' : '0']
            );

            return response()->json([
                'success' => true,
                'data'    => ['is_enabled' => $request->boolean('enabled')],
                'message' => 'পিন ' . ($request->enabled ? 'চালু' : 'বন্ধ') . ' হয়েছে',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // DELETE pin
    public function removePin()
    {
        try {
            UserSetting::where('key', 'pin')->delete();
            UserSetting::updateOrCreate(
                ['key' => 'pin_enabled'],
                ['value' => '0']
            );

            return response()->json(['success' => true, 'message' => 'পিন মুছে ফেলা হয়েছে']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
