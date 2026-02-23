<?php

// ─────────────────────────────────────────────────────────────────────────────
// ThemeSettingController — POST settings/theme
// ─────────────────────────────────────────────────────────────────────────────

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class ThemeSettingController extends Controller
{
    // GET settings/theme
    public function index()
    {
        try {
            $setting = UserSetting::where('key', 'dark_mode')->first();
            return response()->json([
                'success' => true,
                'data'    => ['dark_mode' => $setting ? ($setting->value === '1') : false],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST settings/theme
    public function store(Request $request)
    {
        try {
            $request->validate(['dark_mode' => 'required|boolean']);

            UserSetting::updateOrCreate(
                ['key' => 'dark_mode'],
                ['value' => $request->boolean('dark_mode') ? '1' : '0']
            );

            return response()->json([
                'success' => true,
                'data'    => ['dark_mode' => $request->boolean('dark_mode')],
                'message' => 'Theme updated',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

