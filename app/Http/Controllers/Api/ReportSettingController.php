<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class ReportSettingController extends Controller
{
    private array $defaults = [
        'chart_type'      => 'bar',
        'show_income'     => true,
        'show_expense'    => true,
        'show_balance'    => true,
        'show_categories' => true,
        'date_range'      => 'month',
        'color_scheme'    => 'default',
    ];

    // GET report-settings
    public function index()
    {
        try {
            $setting  = UserSetting::where('key', 'report_settings')->first();
            $settings = $setting ? (json_decode($setting->value, true) ?? $this->defaults) : $this->defaults;

            return response()->json([
                'success' => true,
                'data'    => array_merge($this->defaults, $settings),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST report-settings
    public function store(Request $request)
    {
        try {
            $request->validate([
                'chart_type'      => 'nullable|in:bar,pie,line',
                'show_income'     => 'nullable|boolean',
                'show_expense'    => 'nullable|boolean',
                'show_balance'    => 'nullable|boolean',
                'show_categories' => 'nullable|boolean',
                'date_range'      => 'nullable|in:week,month,year',
                'color_scheme'    => 'nullable|string|max:50',
            ]);

            $current  = UserSetting::where('key', 'report_settings')->first();
            $existing = $current ? (json_decode($current->value, true) ?? $this->defaults) : $this->defaults;

            $updated = array_merge($existing, array_filter($request->only(array_keys($this->defaults)), fn($v) => $v !== null));

            UserSetting::updateOrCreate(
                ['key' => 'report_settings'],
                ['value' => json_encode($updated)]
            );

            return response()->json([
                'success' => true,
                'data'    => $updated,
                'message' => 'Report settings saved',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
