<?php

use App\Http\Controllers\Admin\Adminauthcontroller;
use App\Http\Controllers\Admin\Admincontroller;
use App\Http\Controllers\Admin\AdsettingController;
use App\Http\Controllers\Admin\SliderController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();


Route::get('admin/login', [Adminauthcontroller::class, 'login'])->name('admin.login');
Route::post('admin/login/submit', [Adminauthcontroller::class, 'login_submit'])->name('admin.login.submit');
Route::post('admin/logout', [Adminauthcontroller::class, 'logout'])->name('admin.logout');

// =====================
// Admin Protected (middleware সহ)
// =====================
Route::middleware(['admin'])->group(function () {
    Route::get('admin/dashboard', [Admincontroller::class, 'admin'])->name('admin.dashboard');
    Route::resource('slider', SliderController::class);
    Route::get('adsetting/active-ads', [AdsettingController::class, 'activeAds']);
    Route::resource('adsetting', AdsettingController::class);
});



