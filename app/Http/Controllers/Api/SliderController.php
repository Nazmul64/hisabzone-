<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function index()
{
    $slider = Slider::where('status', 1)->latest()->get()->map(function ($item) {
        $item->photo_url = asset('uploads/slider/' . $item->photo);
        return $item;
    });

    return response()->json([
        'success' => true,
        'data'    => $slider,
        'message' => 'Slider Data Fetch Successfully',
    ]);
}
}
