<?php

use App\Http\Controllers\Admin\Admincontroller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('admindashboard', [Admincontroller::class, 'admin'])->name('admin.dashboard');
