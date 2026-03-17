<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Settings not found',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings fetched successfully',
            'data'    => [
                'id'                       => $setting->id,
                'join_commuity_url'        => $setting->join_commuity_url,
                'video_tutorial_video_url' => $setting->video_tutorial_video_url,
                'developer_portal_url'     => $setting->developer_portal_url,
                'photo'                    => $setting->photo ?? null,
                'rate_app_url'             => $setting->rate_app_url,
                'email'                    => $setting->email,
            ],
        ]);
    }
}
