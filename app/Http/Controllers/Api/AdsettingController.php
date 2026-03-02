<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Adsetting;
use Illuminate\Http\Request;

class AdsettingController extends Controller
{
    // ════════════════════════════════════════════════════════
    // GET /api/adsetting
    // ─────────────────────────────────────────────────────
    // Flutter AdProvider.fetchAndConfigure() এই endpoint call করে।
    // সব active ad settings একসাথে পাঠায়।
    //
    // Response:
    // {
    //   "success": true,
    //   "data": {
    //     "banner":                { ... } | null,
    //     "interstitial":          { ... } | null,
    //     "rewarded":              { ... } | null,
    //     "rewarded_interstitial": { ... } | null,
    //     "native_advanced":       { ... } | null,
    //     "app_open":              { ... } | null
    //   }
    // }
    // ════════════════════════════════════════════════════════
    public function index()
    {
        $allActive = Adsetting::active()->get();

        // একই type এর একাধিক row থাকলে সবচেয়ে নতুনটা (latest id) নাও
        $grouped = [];
        foreach ($allActive as $ad) {
            $type = $ad->ad_type;
            if (! isset($grouped[$type]) || $ad->id > $grouped[$type]->id) {
                $grouped[$type] = $ad;
            }
        }

        // সব supported type এর জন্য null default দাও
        $data = [];
        foreach (Adsetting::adTypes() as $type) {
            $data[$type] = $grouped[$type] ?? null;
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => 'Ad settings fetched successfully',
        ]);
    }

    // ════════════════════════════════════════════════════════
    // GET /api/adsetting/type/{type}
    // ─────────────────────────────────────────────────────
    // একটি নির্দিষ্ট ad type এর active setting আনে।
    // ⚠️ Route এ adsetting/{id} এর আগে রাখতে হবে
    // ════════════════════════════════════════════════════════
    public function getByType(string $type)
    {
        if (! in_array($type, Adsetting::adTypes())) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => "Unknown ad type: $type",
            ], 422);
        }

        $ad = Adsetting::where('ad_type', $type)
            ->active()
            ->latest('id')
            ->first();

        if (! $ad) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => "No active $type ad found",
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $ad,
            'message' => "$type ad fetched successfully",
        ]);
    }

    // ════════════════════════════════════════════════════════
    // GET /api/adsetting/{id}
    // ─────────────────────────────────────────────────────
    // ID দিয়ে নির্দিষ্ট একটা ad setting আনে।
    // ⚠️ Route এ সবার শেষে রাখতে হবে
    // ════════════════════════════════════════════════════════
    public function show($id)
    {
        $ad = Adsetting::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $ad,
            'message' => 'Ad setting fetched successfully',
        ]);
    }
}
