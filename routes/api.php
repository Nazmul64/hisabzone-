<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\SliderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// ✅ Slider
Route::get('slider', [SliderController::class, 'index']);

// ✅ Categories CRUD
Route::apiResource('categories', CategoryController::class);
Route::apiResource('financemanages', FinancemanageController::class);
// ── Dashboard ───────────────────────────────────────────────────
// Dashboard
Route::get('dashboard/summary',             [DashboardController::class, 'summary']);
Route::get('dashboard/recent-transactions', [DashboardController::class, 'recentTransactions']);

// Reports
Route::get('reports/monthly',  [ReportController::class, 'monthly']);
Route::get('reports/annual',   [ReportController::class, 'annual']);
Route::get('reports/filter',   [ReportController::class, 'filter']);
Route::get('reports/graph',    [ReportController::class, 'graph']);

// Budget
Route::apiResource('budgets', BudgetController::class)->only(['index', 'store', 'destroy']);

// Savings
Route::apiResource('savings', SavingController::class)->except(['show']);

// Wallets
Route::apiResource('wallets', WalletController::class)->except(['show']);

// Debt Records
Route::apiResource('debt-records', DebtRecordController::class)->except(['show']);
Route::patch('debt-records/{id}/settle', [DebtRecordController::class, 'settle']);

// Tasks
Route::apiResource('tasks', TaskController::class)->except(['show']);
Route::patch('tasks/{id}/toggle-done', [TaskController::class, 'toggleDone']);


// ════════════════════════════════════════════════════════════
// ── নতুন Routes (Dynamic Pages এর জন্য)
// ════════════════════════════════════════════════════════════

// ── Ad-Free ─────────────────────────────────────────────────
Route::get('ad-free/status',      [AdFreeController::class, 'status']);
Route::post('ad-free/activate',   [AdFreeController::class, 'activate']);
Route::delete('ad-free/deactivate', [AdFreeController::class, 'deactivate']);

// ── Currency ────────────────────────────────────────────────
Route::get('currencies',         [CurrencyController::class, 'index']);
Route::post('currencies/select', [CurrencyController::class, 'select']);

// ── Multi Account ───────────────────────────────────────────
Route::get('accounts',             [AccountController::class, 'index']);
Route::post('accounts',            [AccountController::class, 'store']);
Route::post('accounts/switch',     [AccountController::class, 'switchAccount']);
Route::delete('accounts/{id}',     [AccountController::class, 'destroy']);

// ── PIN Security ────────────────────────────────────────────
Route::get('pin/status',    [PinController::class, 'status']);
Route::post('pin/set',      [PinController::class, 'setPin']);
Route::post('pin/verify',   [PinController::class, 'verify']);
Route::post('pin/toggle',   [PinController::class, 'toggle']);
Route::delete('pin',        [PinController::class, 'removePin']);

// ── Report Settings (Customization) ─────────────────────────
Route::get('report-settings',  [ReportSettingController::class, 'index']);
Route::post('report-settings', [ReportSettingController::class, 'store']);

// ── Theme Settings (Dark Mode) ───────────────────────────────
Route::get('settings/theme',  [ThemeSettingController::class, 'index']);
Route::post('settings/theme', [ThemeSettingController::class, 'store']);


Route::get('profile',  [ProfileController::class, 'show']);
Route::post('profile', [ProfileController::class, 'update']);
