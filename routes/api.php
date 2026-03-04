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
use App\Http\Controllers\Stock\StockProductController;
use App\Http\Controllers\Stock\StockPartyController;
use App\Http\Controllers\Stock\SaleInvoiceController;
use App\Http\Controllers\Stock\PurchaseInvoiceController;
use App\Http\Controllers\Stock\StockAdjustmentController;
use App\Http\Controllers\Stock\StockPaymentController;
use App\Http\Controllers\Stock\StockExpenseController;
use App\Http\Controllers\Stock\SaleReturnController;
use App\Http\Controllers\Stock\PurchaseReturnController;
use App\Http\Controllers\Stock\StockReportController;
use App\Http\Controllers\Stock\StockDashboardController;
use App\Http\Controllers\Stock\InvoiceSettingsController;

// ════════════════════════════════════════════════════════════
// PUBLIC ROUTES — No authentication required
// ════════════════════════════════════════════════════════════
Route::prefix('auth')->group(function () {
    Route::post('register',        [AuthController::class, 'register']);
    Route::post('login',           [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('google/callback', [AuthController::class, 'googleCallback']);
    Route::get('google/redirect',  [AuthController::class, 'googleRedirect']);
});

// ════════════════════════════════════════════════════════════
// PROTECTED ROUTES — Requires auth:sanctum
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
    Route::prefix('finance')->group(function () {
        Route::get('summary',          [FinanceHistoryController::class, 'summary']);
        Route::get('history',          [FinanceHistoryController::class, 'history']);
        Route::get('monthly',          [FinanceHistoryController::class, 'monthly']);
        Route::get('daily',            [FinanceHistoryController::class, 'daily']);
        Route::get('category-summary', [FinanceHistoryController::class, 'categorySummary']);
    });

    // ── Dashboard ─────────────────────────────────────────────
    Route::prefix('dashboard')->group(function () {
        Route::get('summary',             [DashboardController::class, 'summary']);
        Route::get('recent-transactions', [DashboardController::class, 'recentTransactions']);
    });

    // ── Reports ───────────────────────────────────────────────
    Route::prefix('reports')->group(function () {
        Route::get('monthly', [ReportController::class, 'monthly']);
        Route::get('annual',  [ReportController::class, 'annual']);
        Route::get('filter',  [ReportController::class, 'filter']);
        Route::get('graph',   [ReportController::class, 'graph']);
    });

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
    Route::prefix('ad-free')->group(function () {
        Route::get('status',        [AdFreeController::class, 'status']);
        Route::post('activate',     [AdFreeController::class, 'activate']);
        Route::delete('deactivate', [AdFreeController::class, 'deactivate']);
    });

    // ── Currency ──────────────────────────────────────────────
    Route::get('currencies',         [CurrencyController::class, 'index']);
    Route::post('currencies/select', [CurrencyController::class, 'select']);

    // ── Multi Account ─────────────────────────────────────────
    Route::prefix('accounts')->group(function () {
        Route::get('/',        [AccountController::class, 'index']);
        Route::post('/',       [AccountController::class, 'store']);
        Route::post('switch',  [AccountController::class, 'switchAccount']);
        Route::delete('{id}',  [AccountController::class, 'destroy']);
    });

    // ── PIN Security ──────────────────────────────────────────
    Route::prefix('pin')->group(function () {
        Route::get('status',  [PinController::class, 'status']);
        Route::post('set',    [PinController::class, 'setPin']);
        Route::post('verify', [PinController::class, 'verify']);
        Route::post('toggle', [PinController::class, 'toggle']);
        Route::delete('/',    [PinController::class, 'removePin']);
    });

    // ── Report Settings ───────────────────────────────────────
    Route::get('report-settings',  [ReportSettingController::class, 'index']);
    Route::post('report-settings', [ReportSettingController::class, 'store']);

    // ── Theme Settings ────────────────────────────────────────
    Route::prefix('settings')->group(function () {
        Route::get('theme',  [ThemeSettingController::class, 'index']);
        Route::post('theme', [ThemeSettingController::class, 'store']);
    });

    // ── Profile ───────────────────────────────────────────────
    Route::get('profile',  [ProfileController::class, 'show']);
    Route::post('profile', [ProfileController::class, 'update']);

    // ── Ad Settings ───────────────────────────────────────────
    Route::prefix('adsetting')->group(function () {
        Route::get('/',           [AdsettingController::class, 'index']);
        Route::get('type/{type}', [AdsettingController::class, 'getByType']);
        Route::get('{id}',        [AdsettingController::class, 'show']);
    });

    // ════════════════════════════════════════════════════════
    // SAMITI ROUTES — prefix: /api/samiti/...
    // ════════════════════════════════════════════════════════
    Route::prefix('samiti')->group(function () {

        // Dashboard
        Route::get('dashboard', [SamitiDashboardController::class, 'summary']);

        // Profile
        Route::get('profile', [SamitiProfileController::class, 'show']);
        Route::put('profile', [SamitiProfileController::class, 'update']);

        // Members
        Route::get('members',         [SamitiMemberController::class, 'index']);
        Route::post('members',        [SamitiMemberController::class, 'store']);
        Route::put('members/{id}',    [SamitiMemberController::class, 'update']);
        Route::delete('members/{id}', [SamitiMemberController::class, 'destroy']);

        // Savings
        Route::get('savings',         [SamitiSavingController::class, 'index']);
        Route::post('savings',        [SamitiSavingController::class, 'store']);
        Route::delete('savings/{id}', [SamitiSavingController::class, 'destroy']);

        // Collections
        Route::get('collections',               [SamitiCollectionController::class, 'index']);
        Route::patch('collections/{id}/toggle', [SamitiCollectionController::class, 'toggle']);
        Route::post('collections/collect-all',  [SamitiCollectionController::class, 'collectAll']);

        // Loans
        Route::get('loans',            [SamitiLoanController::class, 'index']);
        Route::post('loans',           [SamitiLoanController::class, 'store']);
        Route::patch('loans/{id}/pay', [SamitiLoanController::class, 'makePayment']);
        Route::delete('loans/{id}',    [SamitiLoanController::class, 'destroy']);

        // Fund
        Route::get('fund',  [SamitiFundController::class, 'index']);
        Route::post('fund', [SamitiFundController::class, 'store']);

        // Expenses
        Route::get('expenses',         [SamitiExpenseController::class, 'index']);
        Route::post('expenses',        [SamitiExpenseController::class, 'store']);
        Route::delete('expenses/{id}', [SamitiExpenseController::class, 'destroy']);

        // Fines
        Route::get('fines',               [SamitiFineController::class, 'index']);
        Route::post('fines',              [SamitiFineController::class, 'store']);
        Route::patch('fines/{id}/toggle', [SamitiFineController::class, 'toggle']);
        Route::delete('fines/{id}',       [SamitiFineController::class, 'destroy']);

        // Dividends
        Route::get('dividends',                 [SamitiDividendController::class, 'index']);
        Route::post('dividends/calculate',      [SamitiDividendController::class, 'calculate']);
        Route::post('dividends/distribute-all', [SamitiDividendController::class, 'distributeAll']);
        Route::patch('dividends/{id}/toggle',   [SamitiDividendController::class, 'toggle']);

        // Meetings
        Route::get('meetings',  [SamitiMeetingController::class, 'index']);
        Route::post('meetings', [SamitiMeetingController::class, 'store']);

        // Attendance
        Route::get('attendance',               [SamitiAttendanceController::class, 'index']);
        Route::patch('attendance/{id}/toggle', [SamitiAttendanceController::class, 'toggle']);

        // Notices
        Route::get('notices',                [SamitiNoticeController::class, 'index']);
        Route::post('notices',               [SamitiNoticeController::class, 'store']);
        Route::patch('notices/{id}/read',    [SamitiNoticeController::class, 'markRead']);
        Route::post('notices/mark-all-read', [SamitiNoticeController::class, 'markAllRead']);
        Route::delete('notices/{id}',        [SamitiNoticeController::class, 'destroy']);

    }); // end prefix('samiti')

    // ════════════════════════════════════════════════════════
    // STOCK ROUTES — prefix: /api/stock/...
    // ════════════════════════════════════════════════════════
    Route::prefix('stock')->group(function () {

        // Dashboard
        Route::get('dashboard', [StockDashboardController::class, 'index']);

        // Products
        Route::get('products/categories', [StockProductController::class, 'categories']);
        Route::get('products/low-stock',  [StockProductController::class, 'lowStock']);
        Route::apiResource('products',    StockProductController::class);

        // Parties
        Route::get('parties/{id}/ledger', [StockPartyController::class, 'ledger']);
        Route::apiResource('parties',     StockPartyController::class);

        // Sales
        Route::get('sales/next-number', [SaleInvoiceController::class, 'nextNumber']);
        Route::apiResource('sales',     SaleInvoiceController::class);

        // Purchases
        Route::get('purchases/next-number', [PurchaseInvoiceController::class, 'nextNumber']);
        Route::apiResource('purchases',     PurchaseInvoiceController::class);

        // Adjustments
        Route::get('adjustments',         [StockAdjustmentController::class, 'index']);
        Route::post('adjustments',        [StockAdjustmentController::class, 'store']);
        Route::delete('adjustments/{id}', [StockAdjustmentController::class, 'destroy']);

        // Payments
        Route::get('payments',         [StockPaymentController::class, 'index']);
        Route::post('payments',        [StockPaymentController::class, 'store']);
        Route::delete('payments/{id}', [StockPaymentController::class, 'destroy']);

        // Expenses
        Route::apiResource('expenses', StockExpenseController::class)->except(['show']);

        // Sale Returns
        Route::get('sale-returns',         [SaleReturnController::class, 'index']);
        Route::post('sale-returns',        [SaleReturnController::class, 'store']);
        Route::delete('sale-returns/{id}', [SaleReturnController::class, 'destroy']);

        // Purchase Returns
        Route::get('purchase-returns',         [PurchaseReturnController::class, 'index']);
        Route::post('purchase-returns',        [PurchaseReturnController::class, 'store']);
        Route::delete('purchase-returns/{id}', [PurchaseReturnController::class, 'destroy']);

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('today',   [StockReportController::class, 'today']);
            Route::get('weekly',  [StockReportController::class, 'weekly']);
            Route::get('monthly', [StockReportController::class, 'monthly']);
            Route::get('yearly',  [StockReportController::class, 'yearly']);
            Route::get('custom',  [StockReportController::class, 'custom']);
        });

        // Invoice Settings
        Route::get('invoice-settings',  [InvoiceSettingsController::class, 'show']);
        Route::post('invoice-settings', [InvoiceSettingsController::class, 'store']);
        Route::put('invoice-settings',  [InvoiceSettingsController::class, 'update']);

    }); // end prefix('stock')



Route::prefix('scorehub')
    ->middleware('auth:sanctum')
    ->group(function () {

    // ──────────────────────────────────────────
    //  ম্যাচ CRUD
    // ──────────────────────────────────────────
    Route::get    ('matches',      [ScoreMatchController::class, 'index']);   // GET  - সব ম্যাচ তালিকা
    Route::post   ('matches',      [ScoreMatchController::class, 'store']);   // POST - নতুন ম্যাচ (দল auto তৈরি)
    Route::get    ('matches/{id}', [ScoreMatchController::class, 'show']);    // GET  - একটা ম্যাচের পুরো ডাটা
    Route::patch  ('matches/{id}', [ScoreMatchController::class, 'update']); // PATCH- ম্যাচ আপডেট
    Route::delete ('matches/{id}', [ScoreMatchController::class, 'destroy']); // DEL  - ম্যাচ মুছো

    // ──────────────────────────────────────────
    //  রান গ্রিড
    // ──────────────────────────────────────────
    Route::patch  ('teams/{id}/grid',    [ScoreTeamController::class, 'updateGrid']);  // গ্রিড সেভ

    // ──────────────────────────────────────────
    //  অতিরিক্ত রান
    // ──────────────────────────────────────────
    Route::post   ('teams/{id}/extras',  [ScoreTeamController::class, 'addExtra']);    // যোগ
    Route::patch  ('extras/{id}/toggle', [ScoreTeamController::class, 'toggleExtra']); // কাটো/ফেরাও
    Route::delete ('extras/{id}',        [ScoreTeamController::class, 'deleteExtra']); // মুছো

    // ──────────────────────────────────────────
    //  ওভার
    // ──────────────────────────────────────────
    Route::post   ('teams/{id}/overs',  [ScoreTeamController::class, 'addOver']);    // নতুন ওভার
    Route::patch  ('overs/{id}/toggle', [ScoreTeamController::class, 'toggleOver']); // শেষ/আনশেষ

    // ──────────────────────────────────────────
    //  বলার
    // ──────────────────────────────────────────
    Route::post   ('teams/{id}/bowlers', [ScoreTeamController::class, 'addBowler']);   // যোগ
    Route::delete ('bowlers/{id}',       [ScoreTeamController::class, 'deleteBowler']); // মুছো

    // ──────────────────────────────────────────
    //  খেলোয়াড়
    // ──────────────────────────────────────────
    Route::post   ('teams/{id}/players',         [ScorePlayerController::class, 'store']);     // যোগ
    Route::delete ('players/{id}',               [ScorePlayerController::class, 'destroy']);   // মুছো
    Route::patch  ('players/{id}/toggle-out',    [ScorePlayerController::class, 'toggleOut']); // আউট
    Route::post   ('players/{id}/runs',          [ScorePlayerController::class, 'addRun']);    // রান যোগ (০ও চলবে)
    Route::delete ('players/{id}/runs/{runIdx}', [ScorePlayerController::class, 'removeRun']); // রান মুছো
});

}); // end middleware('auth:sanctum')
