<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adsetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdsettingController extends Controller
{

    private function rules()
    {
        return [
            'ad_type' => [
                'required',
                Rule::in(array_keys(Adsetting::adTypes()))
            ],
            'label'             => 'nullable|string|max:255',
            'ad_unit_id'        => 'required|string|max:255',
            'admob_app_id'      => 'required|string|max:255',
            'trigger'           => [
                'nullable',
                Rule::in(array_keys(Adsetting::triggerOptions()))
            ],
            'trigger_frequency' => 'nullable|integer|min:0|max:9999',
            'notes'             => 'nullable|string|max:1000',
        ];
    }

    // ── Index ──────────────────────────────────────────────────
    public function index()
    {
        $adsettings = Adsetting::latest()->get();

        return view('admin.adsetting.index', [
            'adsettings' => $adsettings,
            'adTypes'    => Adsetting::adTypes(),
        ]);
    }

    // ── Create ─────────────────────────────────────────────────
    public function create()
    {
        return view('admin.adsetting.create', [
            'adTypes'  => Adsetting::adTypes(),
            'triggers' => Adsetting::triggerOptions(),
        ]);
    }

    // ── Store ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->has('is_active');
        Adsetting::create($data);

        return redirect()
            ->route('adsetting.index')
            ->with('success', 'Ad Created Successfully');
    }

    // ── Edit ───────────────────────────────────────────────────
    public function edit($id)
    {
        $adsetting = Adsetting::findOrFail($id);

        return view('admin.adsetting.edit', [
            'adsetting' => $adsetting,
            'adTypes'   => Adsetting::adTypes(),
            'triggers'  => Adsetting::triggerOptions(),
        ]);
    }

    // ── Update ─────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $adsetting = Adsetting::findOrFail($id);
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->has('is_active');
        $adsetting->update($data);

        return redirect()
            ->route('adsetting.index')
            ->with('success', 'Ad Updated Successfully');
    }

    // ── Destroy ────────────────────────────────────────────────
    public function destroy($id)
    {
        Adsetting::findOrFail($id)->delete();
        return back()->with('success', 'Ad Deleted');
    }

    // ══════════════════════════════════════════════════════════
    // ✅ Flutter App API — GET /api/adsetting
    //
    // সমস্যা ছিল:
    //   1. admob_app_id field missing ছিল — Flutter এ দরকার
    //   2. is_active field missing ছিল — Flutter parse করতে পারছিল না
    //   3. label field missing ছিল
    //
    // এখন সব field return করা হচ্ছে
    // ══════════════════════════════════════════════════════════
    public function activeAds()
    {
        // ✅ active() scope — is_active = 1 শুধু
        $ad = Adsetting::active()
            ->latest()
            ->first([
                'id',
                'ad_type',
                'label',
                'ad_unit_id',
                'admob_app_id',      // ✅ Flutter এ দরকার
                'trigger',
                'trigger_frequency',
                'is_active',         // ✅ Flutter parse করে
            ]);

        if (! $ad) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'No active ad setting found',
            ], 200); // 200 দিচ্ছি যাতে Flutter এ error না হয়
        }

        return response()->json([
            'success' => true,
            'data'    => $ad,
            'message' => null,
        ]);
    }
}
