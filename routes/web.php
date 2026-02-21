<?php

use App\Http\Controllers\Admin\Adminauthcontroller;
use App\Http\Controllers\Admin\Admincontroller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [Admincontroller::class, 'admin'])->name('admin.dashboard');
Route::get('admin/login', [Adminauthcontroller::class, 'login'])->name('admin.login');
Route::post('admin/login/submit', [Adminauthcontroller::class, 'login_submit'])->name('admin.login.submit');
Route::post('admin/logout', [Adminauthcontroller::class, 'logout'])->name('admin.logout');

