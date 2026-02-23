<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::latest()->get();
        return view('admin.slider.index', compact('sliders'));
    }

    public function create()
    {
        $titleKeys = $this->getAvailableTitleKeys();
        return view('admin.slider.create', compact('titleKeys'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'title_key' => 'nullable|string|max:100',
            'url'       => 'nullable|url|max:255',
            'photo'     => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'    => 'required|in:0,1',
        ]);

        $photoName = null;
        if ($request->hasFile('photo')) {
            $photo     = $request->file('photo');
            $photoName = time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/slider'), $photoName);
        }

        Slider::create([
            'title'     => $request->title,
            'title_key' => $request->title_key ?? '',
            'url'       => $request->url,
            'photo'     => $photoName,
            'status'    => $request->status,
        ]);

        return redirect()->route('slider.index')
            ->with('success', 'Slider created successfully.');
    }

    public function edit(string $id)
    {
        $slider    = Slider::findOrFail($id);
        $titleKeys = $this->getAvailableTitleKeys();
        return view('admin.slider.edit', compact('slider', 'titleKeys'));
    }

    public function update(Request $request, string $id)
    {
        $slider = Slider::findOrFail($id);

        $request->validate([
            'title'     => 'required|string|max:255',
            'title_key' => 'nullable|string|max:100',
            'url'       => 'nullable|url|max:255',
            'photo'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'    => 'required|in:0,1',
        ]);

        $photoName = $slider->photo;
        if ($request->hasFile('photo')) {
            $oldPath = public_path('uploads/slider/' . $slider->photo);
            if ($slider->photo && file_exists($oldPath)) {
                unlink($oldPath);
            }
            $photo     = $request->file('photo');
            $photoName = time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/slider'), $photoName);
        }

        $slider->update([
            'title'     => $request->title,
            'title_key' => $request->title_key ?? '',
            'url'       => $request->url,
            'photo'     => $photoName,
            'status'    => $request->status,
        ]);

        return redirect()->route('slider.index')
            ->with('success', 'Slider updated successfully.');
    }

    public function destroy(string $id)
    {
        $slider = Slider::findOrFail($id);
        $photoPath = public_path('uploads/slider/' . $slider->photo);
        if ($slider->photo && file_exists($photoPath)) {
            unlink($photoPath);
        }
        $slider->delete();
        return redirect()->route('slider.index')
            ->with('success', 'Slider deleted successfully.');
    }

    private function getAvailableTitleKeys(): array
    {
        return [
            'premium_features'      => 'Premium Features',
            'financial_analysis'    => 'Financial Analysis',
            'backup_restore'        => 'Backup & Restore',
            'stock_management'      => 'Stock Management',
            'income_expense_report' => 'Income-Expense Report',
            'app_name'              => 'App Name',
            'tagline'               => 'Tagline',
        ];
    }
}
