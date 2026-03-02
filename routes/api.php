<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Controllers ──────────────────────────────────────────────
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FinancemanageController;
use App\Http\Controllers\Api\FinanceHistoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\SavingController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\DebtRecordController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\AdFreeController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\PinController;
use App\Http\Controllers\Api\ReportSettingController;
use App\Http\Controllers\Api\ThemeSettingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AdsettingController;
use App\Http\Controllers\Api\SamitiAttendanceController;
use App\Http\Controllers\Api\SamitiCollectionController;
use App\Http\Controllers\Api\SamitiDashboardController;
use App\Http\Controllers\Api\SamitiDividendController;
use App\Http\Controllers\Api\SamitiExpenseController;
use App\Http\Controllers\Api\SamitiFineController;
use App\Http\Controllers\Api\SamitiFundController;
use App\Http\Controllers\Api\SamitiLoanController;
use App\Http\Controllers\Api\SamitiMeetingController;
use App\Http\Controllers\Api\SamitiMemberController;
use App\Http\Controllers\Api\SamitiNoticeController;
use App\Http\Controllers\Api\SamitiProfileController;
use App\Http\Controllers\Api\SamitiSavingController;

// ════════════════════════════════════════════════════════════
// PUBLIC ROUTES — Auth এর দরকার নেই
// ════════════════════════════════════════════════════════════
Route::prefix('auth')->group(function () {
    Route::post('register',        [AuthController::class, 'register']);
    Route::post('login',           [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('google/callback', [AuthController::class, 'googleCallback']);
    Route::get('google/redirect',  [AuthController::class, 'googleRedirect']);
});

// ════════════════════════════════════════════════════════════
// PROTECTED ROUTES — auth:sanctum middleware
// ════════════════════════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ─────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::get('me',          [AuthController::class, 'me']);
        Route::post('logout',     [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
    });

    // ── Slider ───────────────────────────────────────────────
    Route::get('slider', [SliderController::class, 'index']);

    // ── Categories & Finance ─────────────────────────────────
    Route::apiResource('categories',     CategoryController::class);
    Route::apiResource('financemanages', FinancemanageController::class);

    // ── Finance History ──────────────────────────────────────
    Route::get('finance/summary',          [FinanceHistoryController::class, 'summary']);
    Route::get('finance/history',          [FinanceHistoryController::class, 'history']);
    Route::get('finance/monthly',          [FinanceHistoryController::class, 'monthly']);
    Route::get('finance/daily',            [FinanceHistoryController::class, 'daily']);
    Route::get('finance/category-summary', [FinanceHistoryController::class, 'categorySummary']);

    // ── Dashboard ─────────────────────────────────────────────
    Route::get('dashboard/summary',             [DashboardController::class, 'summary']);
    Route::get('dashboard/recent-transactions', [DashboardController::class, 'recentTransactions']);

    // ── Reports ───────────────────────────────────────────────
    Route::get('reports/monthly', [ReportController::class, 'monthly']);
    Route::get('reports/annual',  [ReportController::class, 'annual']);
    Route::get('reports/filter',  [ReportController::class, 'filter']);
    Route::get('reports/graph',   [ReportController::class, 'graph']);

    // ── Budget ────────────────────────────────────────────────
    Route::apiResource('budgets', BudgetController::class)->only(['index', 'store', 'destroy']);

    // ── Savings ───────────────────────────────────────────────
    Route::apiResource('savings', SavingController::class)->except(['show']);

    // ── Wallets ───────────────────────────────────────────────
    Route::apiResource('wallets', WalletController::class)->except(['show']);

    // ── Debt Records ──────────────────────────────────────────
    Route::apiResource('debt-records', DebtRecordController::class)->except(['show']);
    Route::patch('debt-records/{id}/settle', [DebtRecordController::class, 'settle']);

    // ── Tasks ─────────────────────────────────────────────────
    Route::apiResource('tasks', TaskController::class)->except(['show']);
    Route::patch('tasks/{id}/toggle-done', [TaskController::class, 'toggleDone']);

    // ── Ad-Free ───────────────────────────────────────────────
    Route::get('ad-free/status',        [AdFreeController::class, 'status']);
    Route::post('ad-free/activate',     [AdFreeController::class, 'activate']);
    Route::delete('ad-free/deactivate', [AdFreeController::class, 'deactivate']);

    // ── Currency ──────────────────────────────────────────────
    Route::get('currencies',         [CurrencyController::class, 'index']);
    Route::post('currencies/select', [CurrencyController::class, 'select']);

    // ── Multi Account ─────────────────────────────────────────
    Route::get('accounts',         [AccountController::class, 'index']);
    Route::post('accounts',        [AccountController::class, 'store']);
    Route::post('accounts/switch', [AccountController::class, 'switchAccount']);
    Route::delete('accounts/{id}', [AccountController::class, 'destroy']);

    // ── PIN Security ──────────────────────────────────────────
    Route::get('pin/status',  [PinController::class, 'status']);
    Route::post('pin/set',    [PinController::class, 'setPin']);
    Route::post('pin/verify', [PinController::class, 'verify']);
    Route::post('pin/toggle', [PinController::class, 'toggle']);
    Route::delete('pin',      [PinController::class, 'removePin']);

    // ── Report Settings ───────────────────────────────────────
    Route::get('report-settings',  [ReportSettingController::class, 'index']);
    Route::post('report-settings', [ReportSettingController::class, 'store']);

    // ── Theme Settings ────────────────────────────────────────
    Route::get('settings/theme',  [ThemeSettingController::class, 'index']);
    Route::post('settings/theme', [ThemeSettingController::class, 'store']);

    // ── Profile ───────────────────────────────────────────────
    Route::get('profile',  [ProfileController::class, 'show']);
    Route::post('profile', [ProfileController::class, 'update']);

    // ── Ad Settings ───────────────────────────────────────────
    Route::get('adsetting',             [AdsettingController::class, 'index']);
    Route::get('adsetting/type/{type}', [AdsettingController::class, 'getByType']);
    Route::get('adsetting/{id}',        [AdsettingController::class, 'show']);

    // ════════════════════════════════════════════════════════
    // SAMITI ROUTES — prefix: /api/samiti/...
    // ════════════════════════════════════════════════════════
    Route::prefix('samiti')->group(function () {

        // ── Dashboard ─────────────────────────────────────────
        Route::get('dashboard', [SamitiDashboardController::class, 'summary']);

        // ── Profile ───────────────────────────────────────────
        Route::get('profile', [SamitiProfileController::class, 'show']);
        Route::put('profile', [SamitiProfileController::class, 'update']);

        // ── Members ───────────────────────────────────────────
        Route::get('members',         [SamitiMemberController::class, 'index']);
        Route::post('members',        [SamitiMemberController::class, 'store']);
        Route::put('members/{id}',    [SamitiMemberController::class, 'update']);
        Route::delete('members/{id}', [SamitiMemberController::class, 'destroy']);

        // ── Savings ───────────────────────────────────────────
        Route::get('savings',         [SamitiSavingController::class, 'index']);
        Route::post('savings',        [SamitiSavingController::class, 'store']);
        Route::delete('savings/{id}', [SamitiSavingController::class, 'destroy']);

        // ── Collections (কিস্তি) ──────────────────────────────
        Route::get('collections',                   [SamitiCollectionController::class, 'index']);
        Route::patch('collections/{id}/toggle',     [SamitiCollectionController::class, 'toggle']);
        Route::post('collections/collect-all',      [SamitiCollectionController::class, 'collectAll']);

        // ── Loans ─────────────────────────────────────────────
        Route::get('loans',              [SamitiLoanController::class, 'index']);
        Route::post('loans',             [SamitiLoanController::class, 'store']);
        Route::patch('loans/{id}/pay',   [SamitiLoanController::class, 'makePayment']);
        Route::delete('loans/{id}',      [SamitiLoanController::class, 'destroy']);

        // ── Fund Transactions ─────────────────────────────────
        Route::get('fund',  [SamitiFundController::class, 'index']);
        Route::post('fund', [SamitiFundController::class, 'store']);

        // ── Expenses ──────────────────────────────────────────
        Route::get('expenses',         [SamitiExpenseController::class, 'index']);
        Route::post('expenses',        [SamitiExpenseController::class, 'store']);
        Route::delete('expenses/{id}', [SamitiExpenseController::class, 'destroy']);

        // ── Fines ─────────────────────────────────────────────
        Route::get('fines',              [SamitiFineController::class, 'index']);
        Route::post('fines',             [SamitiFineController::class, 'store']);
        Route::patch('fines/{id}/toggle',[SamitiFineController::class, 'toggle']);
        Route::delete('fines/{id}',      [SamitiFineController::class, 'destroy']);

        // ── Dividends ─────────────────────────────────────────
        Route::get('dividends',                      [SamitiDividendController::class, 'index']);
        Route::post('dividends/calculate',           [SamitiDividendController::class, 'calculate']);
        Route::post('dividends/distribute-all',      [SamitiDividendController::class, 'distributeAll']);
        Route::patch('dividends/{id}/toggle',        [SamitiDividendController::class, 'toggle']);

        // ── Meetings ──────────────────────────────────────────
        Route::get('meetings',  [SamitiMeetingController::class, 'index']);
        Route::post('meetings', [SamitiMeetingController::class, 'store']);

        // ── Attendance ────────────────────────────────────────
        Route::get('attendance',                 [SamitiAttendanceController::class, 'index']);
        Route::patch('attendance/{id}/toggle',   [SamitiAttendanceController::class, 'toggle']);

        // ── Notices ───────────────────────────────────────────
        Route::get('notices',                  [SamitiNoticeController::class, 'index']);
        Route::post('notices',                 [SamitiNoticeController::class, 'store']);
        Route::patch('notices/{id}/read',      [SamitiNoticeController::class, 'markRead']);
        Route::post('notices/mark-all-read',   [SamitiNoticeController::class, 'markAllRead']);
        Route::delete('notices/{id}',          [SamitiNoticeController::class, 'destroy']);

    }); // end prefix('samiti')

}); // end middleware('auth:sanctum')
