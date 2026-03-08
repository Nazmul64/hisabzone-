<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Admincontroller extends Controller
{
    public function admin()
{
    $total_users  = \App\Models\User::count();
    $total_sliders = \App\Models\Slider::count();
    $active_users = \App\Models\User::latest()->take(10)->get(['name', 'email']);

    return view('admin.index', compact('total_users', 'total_sliders', 'active_users'));
}

}
