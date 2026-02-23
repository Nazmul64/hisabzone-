<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Support\Facades\Log;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::where('status', 1)
            ->latest()
            ->get()
            ->map(function ($item) {

                // ✅ Debug: title_key DB থেকে কী আসছে লগ করো
                Log::info('Slider data', [
                    'id'        => $item->id,
                    'title'     => $item->title,
                    'title_key' => $item->title_key,
                ]);

                return [
                    'id'        => $item->id,
                    'title'     => $item->title      ?? '',
                    'title_key' => $item->title_key  ?? '',  // ✅ null হলে empty string
                    'url'       => $item->url        ?? '',
                    'photo'     => $item->photo
                        ? asset('uploads/slider/' . $item->photo)
                        : '',
                    'status'    => (bool) $item->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $sliders,
            'message' => 'Slider Data Fetch Successfully',
        ]);
    }
}
