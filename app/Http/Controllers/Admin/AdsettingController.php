<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adsetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdsettingController extends Controller
{
    // ── Validation Rules ───────────────────────────────────────
    private function rules(): array
    {
        return [
            'ad_type' => [
                'required',
                Rule::in(array_keys(Adsetting::adTypes())),
            ],
            'label'             => 'nullable|string|max:255',
            'ad_unit_id'        => 'required|string|max:255',
            'admob_app_id'      => 'required|string|max:255',
            'trigger'           => [
                'nullable',
                Rule::in(array_keys(Adsetting::triggerOptions())),
            ],
            'trigger_frequency' => 'nullable|integer|min:1|max:9999',
            'notes'             => 'nullable|string|max:1000',
        ];
    }

    // ── Index ──────────────────────────────────────────────────
    public function index()
    {
        $adsettings = Adsetting::latest()->get();
        $adTypes    = Adsetting::adTypes();

        return view('admin.adsetting.index', compact('adsettings', 'adTypes'));
    }

    // ── Create ─────────────────────────────────────────────────
    public function create()
    {
        $adTypes  = Adsetting::adTypes();
        $triggers = Adsetting::triggerOptions();

        return view('admin.adsetting.create', compact('adTypes', 'triggers'));
    }

    // ── Store ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $data['trigger_frequency'] = max(1, (int) ($data['trigger_frequency'] ?? 1));
        $data['is_active']         = $request->boolean('is_active');

        Adsetting::create($data);

        return redirect()
            ->route('adsetting.index')
            ->with('success', 'Ad Setting সফলভাবে তৈরি হয়েছে ✅');
    }

    // ── Edit ───────────────────────────────────────────────────
    public function edit(Adsetting $adsetting)
    {
        $adTypes  = Adsetting::adTypes();
        $triggers = Adsetting::triggerOptions();

        return view('admin.adsetting.edit', compact('adsetting', 'adTypes', 'triggers'));
    }

    // ── Update ─────────────────────────────────────────────────
    public function update(Request $request, Adsetting $adsetting)
    {
        $data = $request->validate($this->rules());

        $data['trigger_frequency'] = max(1, (int) ($data['trigger_frequency'] ?? 1));
        $data['is_active']         = $request->boolean('is_active');

        $adsetting->update($data);

        return redirect()
            ->route('adsetting.index')
            ->with('success', 'Ad Setting সফলভাবে আপডেট হয়েছে ✅');
    }

    // ── Destroy ────────────────────────────────────────────────
    public function destroy(Adsetting $adsetting)
    {
        $adsetting->delete();

        return back()->with('success', 'Ad Setting মুছে গেছে ✅');
    }
}
