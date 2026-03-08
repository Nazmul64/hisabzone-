<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display the settings (only one row exists).
     */
    public function index()
    {
        $setting = Setting::first();
        return view('admin.setting.index', compact('setting'));
    }

    /**
     * Show the form for creating settings (only if none exists).
     */
    public function create()
    {
        if (Setting::exists()) {
            return redirect()->route('settings.index')
                ->with('info', 'Settings already exist. Please edit instead.');
        }
        return view('admin.setting.create');
    }

    /**
     * Store a newly created setting in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'join_commuity_url'       => 'required|url|max:255|unique:settings,join_commuity_url',
            'video_tutorial_video_url'=> 'nullable|url',
            'developer_portal_url'    => 'required|url|max:255|unique:settings,developer_portal_url',
            'photo'                   => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'rate_app_url'            => 'required|url|max:255|unique:settings,rate_app_url',
            'email'                   => 'required|email|max:255|unique:settings,email',
        ]);

        $data = $request->only([
            'join_commuity_url',
            'video_tutorial_video_url',
            'developer_portal_url',
            'rate_app_url',
            'email',
        ]);

        // Handle photo upload → public/uploads/setting/
        if ($request->hasFile('photo')) {
            $file     = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/setting'), $filename);
            $data['photo'] = $filename;
        }

        Setting::create($data);

        return redirect()->route('settings.index')
            ->with('success', 'Settings created successfully.');
    }

    /**
     * Display the specified resource (not used — redirect to index).
     */
    public function show(string $id)
    {
        return redirect()->route('settings.index');
    }

    /**
     * Show the form for editing the setting.
     */
    public function edit(string $id)
    {
        $setting = Setting::findOrFail($id);
        return view('admin.setting.edit', compact('setting'));
    }

    /**
     * Update the specified setting in storage.
     */
    public function update(Request $request, string $id)
    {
        $setting = Setting::findOrFail($id);

        $request->validate([
            'join_commuity_url'       => 'required|url|max:255|unique:settings,join_commuity_url,' . $id,
            'video_tutorial_video_url'=> 'nullable|url',
            'developer_portal_url'    => 'required|url|max:255|unique:settings,developer_portal_url,' . $id,
            'photo'                   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'rate_app_url'            => 'required|url|max:255|unique:settings,rate_app_url,' . $id,
            'email'                   => 'required|email|max:255|unique:settings,email,' . $id,
        ]);

        $data = $request->only([
            'join_commuity_url',
            'video_tutorial_video_url',
            'developer_portal_url',
            'rate_app_url',
            'email',
        ]);

        // Handle photo upload → public/uploads/setting/
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            $oldPath = public_path('uploads/setting/' . $setting->photo);
            if ($setting->photo && file_exists($oldPath)) {
                unlink($oldPath);
            }

            $file     = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/setting'), $filename);
            $data['photo'] = $filename;
        }

        $setting->update($data);

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Remove the specified setting from storage.
     */
    public function destroy(string $id)
    {
        $setting = Setting::findOrFail($id);

        // Delete photo from uploads/setting/
        $oldPath = public_path('uploads/setting/' . $setting->photo);
        if ($setting->photo && file_exists($oldPath)) {
            unlink($oldPath);
        }

        $setting->delete();

        return redirect()->route('settings.index')
            ->with('success', 'Settings deleted successfully.');
    }
}
