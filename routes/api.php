<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── General Controllers ──────────────────────────────────────
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
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\SettingController;

// ── Brickkiln Controllers ─────────────────────────────────────
use App\Http\Controllers\Api\BrickkilnExpenseController;
use App\Http\Controllers\Api\BrickkilnInventoryController;
use App\Http\Controllers\Api\BrickkilnReportController;
use App\Http\Controllers\Api\BrickkilnSalaryController;
use App\Http\Controllers\Api\BrickkilnSaleController;
use App\Http\Controllers\Api\BrickkilnsCustomerController;
use App\Http\Controllers\Api\BrickkilnsDashboardController;
use App\Http\Controllers\Api\BrickkilnsEmployeeController;
use App\Http\Controllers\Api\BrickkilnsRawMaterialController;
use App\Http\Controllers\Api\BrickkilnSupplierController;
use App\Http\Controllers\Api\BrickkilnsWorkerController;
use App\Http\Controllers\Api\BrickkilnTransportController;
use App\Http\Controllers\Api\BrickProductionController;

// ── Samiti Controllers ───────────────────────────────────────
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

// ── ScoreHub Controllers ─────────────────────────────────────
use App\Http\Controllers\Api\ScoreHistoryController;
use App\Http\Controllers\Api\ScoreMatchController;
use App\Http\Controllers\Api\ScorePlayerController;
use App\Http\Controllers\Api\ScoreTeamController;

// ── Stock Controllers ────────────────────────────────────────
use App\Http\Controllers\Stock\StockProductController;
use App\Http\Controllers\Stock\StockPartyController;
use App\Http\Controllers\Stock\SaleInvoiceController;
use App\Http\Controllers\Stock\PurchaseInvoiceController;
use App\Http\Controllers\Stock\StockAdjustmentController;
use App\Http\Controllers\Stock\StockPaymentController;
use App\Http\Controllers\Stock\Stockexpensecontroller;
use App\Http\Controllers\Stock\SaleReturnController;
use App\Http\Controllers\Stock\Purchasereturncontroller;
use App\Http\Controllers\Stock\Stockreportcontroller;
use App\Http\Controllers\Stock\StockDashboardController;
use App\Http\Controllers\Stock\InvoiceSettingsController;

// ── Tailor Controllers ───────────────────────────────────────
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DealerController;
use App\Http\Controllers\Api\TailordashboardController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\TailorReportController;
use App\Http\Controllers\Api\TailorSalaryController;
use App\Http\Controllers\Api\SalaryHistoryController;
use App\Http\Controllers\Api\ScoreTournamentController;
use App\Http\Controllers\Api\ScoreBallController;

// ── Pharmacy Controllers ─────────────────────────────────────
use App\Http\Controllers\Api\Pharmacy\PharmacyDashboardController;
use App\Http\Controllers\Api\Pharmacy\PharmacyMedicineController;
use App\Http\Controllers\Api\Pharmacy\PharmacyCategoryController;
use App\Http\Controllers\Api\Pharmacy\PharmacySupplierController;
use App\Http\Controllers\Api\Pharmacy\PharmacyCustomerController;
use App\Http\Controllers\Api\Pharmacy\PharmacyPurchaseController;
use App\Http\Controllers\Api\Pharmacy\PharmacySaleController;
use App\Http\Controllers\Api\Pharmacy\PharmacyPrescriptionController;
use App\Http\Controllers\Api\Pharmacy\PharmacyEmployeeController;
use App\Http\Controllers\Api\Pharmacy\PharmacyExpenseController;
use App\Http\Controllers\Api\Pharmacy\PharmacyReturnController;




// ── Nursery Controllers ──────────────────────────────────────
use App\Http\Controllers\Nursery\NurseryDashboardController;
use App\Http\Controllers\Nursery\NurseryPlantCategoryController;
use App\Http\Controllers\Nursery\Nurseryplantcontroller;
use App\Http\Controllers\Nursery\Nurserysuppliercontroller;
use App\Http\Controllers\Nursery\NurseryCustomerController;
use App\Http\Controllers\Nursery\Nurserypurchasecontroller;
use App\Http\Controllers\Nursery\Nurserysalecontroller;
use App\Http\Controllers\Nursery\Nurseryemployeecontroller;
use App\Http\Controllers\Nursery\Nurserygardenareacontroller;
use App\Http\Controllers\Nursery\Nurseryplantcarecontroller;
use App\Http\Controllers\Nursery\Nurseryfertilizercontroller;
use App\Http\Controllers\Nursery\Nurseryexpensecontroller;
use App\Http\Controllers\Nursery\Nurseryordercontroller;
use App\Http\Controllers\Nursery\Nurserydeliverycontroller;
use App\Http\Controllers\Nursery\NurseryReportController;


// ════════════════════════════════════════════════════════════
// PUBLIC ROUTES — Token লাগবে না
// ════════════════════════════════════════════════════════════

// App Settings
Route::get('setting', [SettingController::class, 'index']);

// Auth — Login, Register, Google
Route::prefix('auth')->group(function () {
    Route::post('register',        [AuthController::class, 'register']);
    Route::post('login',           [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('google/callback', [AuthController::class, 'googleCallback']);
    Route::get('google/redirect',  [AuthController::class, 'googleRedirect']);
});

// Forgot Password OTP Flow
Route::post('forgot-password', [ForgotPasswordController::class, 'sendOtp']);
Route::post('verify-otp',      [ForgotPasswordController::class, 'verifyOtp']);
Route::post('reset-password',  [ForgotPasswordController::class, 'resetPassword']);


// ════════════════════════════════════════════════════════════
// PROTECTED ROUTES — auth:sanctum required
// ════════════════════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {

    // ════════════════════════════════════════════════════════
    // AUTH
    // ════════════════════════════════════════════════════════
    Route::prefix('auth')->group(function () {
        Route::get('me',          [AuthController::class, 'me']);
        Route::post('logout',     [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
    });

    // ════════════════════════════════════════════════════════
    // GENERAL
    // ════════════════════════════════════════════════════════

    // Slider
    Route::get('slider', [SliderController::class, 'index']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Finance Transactions
    Route::apiResource('financemanages', FinancemanageController::class);

    // Finance History
    Route::prefix('finance')->group(function () {
        Route::get('summary',          [FinanceHistoryController::class, 'summary']);
        Route::get('history',          [FinanceHistoryController::class, 'history']);
        Route::get('monthly',          [FinanceHistoryController::class, 'monthly']);
        Route::get('daily',            [FinanceHistoryController::class, 'daily']);
        Route::get('category-summary', [FinanceHistoryController::class, 'categorySummary']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('summary',             [DashboardController::class, 'summary']);
        Route::get('recent-transactions', [DashboardController::class, 'recentTransactions']);
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('monthly', [ReportController::class, 'monthly']);
        Route::get('annual',  [ReportController::class, 'annual']);
        Route::get('filter',  [ReportController::class, 'filter']);
        Route::get('graph',   [ReportController::class, 'graph']);
    });

    // Budgets
    Route::apiResource('budgets', BudgetController::class)->only(['index', 'store', 'destroy']);

    // Savings
    Route::apiResource('savings', SavingController::class)->except(['show']);

    // Wallets
    Route::apiResource('wallets', WalletController::class)->except(['show']);

    // Debt Records — ⚠️ static 'settle' BEFORE apiResource
    Route::patch('debt-records/{id}/settle', [DebtRecordController::class, 'settle']);
    Route::apiResource('debt-records', DebtRecordController::class)->except(['show']);

    // Tasks — ⚠️ static 'toggle-done' BEFORE apiResource
    Route::patch('tasks/{id}/toggle-done', [TaskController::class, 'toggleDone']);
    Route::apiResource('tasks', TaskController::class)->except(['show']);

    // Ad-Free
    Route::prefix('ad-free')->group(function () {
        Route::get('status',        [AdFreeController::class, 'status']);
        Route::post('activate',     [AdFreeController::class, 'activate']);
        Route::delete('deactivate', [AdFreeController::class, 'deactivate']);
    });

    // Currency
    Route::get('currencies',         [CurrencyController::class, 'index']);
    Route::post('currencies/select', [CurrencyController::class, 'select']);

    // Multi Account
    Route::prefix('accounts')->group(function () {
        Route::get('/',       [AccountController::class, 'index']);
        Route::post('/',      [AccountController::class, 'store']);
        Route::post('switch', [AccountController::class, 'switchAccount']);
        Route::delete('{id}', [AccountController::class, 'destroy']);
    });

    // PIN Security
    Route::prefix('pin')->group(function () {
        Route::get('status',  [PinController::class, 'status']);
        Route::post('set',    [PinController::class, 'setPin']);
        Route::post('verify', [PinController::class, 'verify']);
        Route::post('toggle', [PinController::class, 'toggle']);
        Route::delete('/',    [PinController::class, 'removePin']);
    });

    // Report Settings
    Route::get('report-settings',  [ReportSettingController::class, 'index']);
    Route::post('report-settings', [ReportSettingController::class, 'store']);

    // Theme Settings
    Route::prefix('settings')->group(function () {
        Route::get('theme',  [ThemeSettingController::class, 'index']);
        Route::post('theme', [ThemeSettingController::class, 'store']);
    });

    // Profile
    Route::get('profile',  [ProfileController::class, 'show']);
    Route::post('profile', [ProfileController::class, 'update']);
    Route::post('profile/change-password', [ProfileController::class, 'changePassword']);

    // Ad Settings
    Route::prefix('adsetting')->group(function () {
        Route::get('/',           [AdsettingController::class, 'index']);
        Route::get('type/{type}', [AdsettingController::class, 'getByType']);
        Route::get('{id}',        [AdsettingController::class, 'show']);
    });


    // ════════════════════════════════════════════════════════
    // SAMITI — /api/samiti/...
    // ════════════════════════════════════════════════════════
    Route::prefix('samiti')->group(function () {

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
        // Route::get('savings',         [SamitiSavingController::class, 'index']);
        // Route::post('savings',        [SamitiSavingController::class, 'store']);
        // Route::delete('savings/{id}', [SamitiSavingController::class, 'destroy']);
        Route::get('savings',          [SamitiSavingController::class, 'index']);
        Route::post('savings',         [SamitiSavingController::class, 'store']);
        Route::put('savings/{id}',     [SamitiSavingController::class, 'update']);   // ✅ নতুন
        Route::delete('savings/{id}',  [SamitiSavingController::class, 'destroy']);

        // Collections — ⚠️ static 'collect-all' BEFORE dynamic {id}
        Route::get('collections',               [SamitiCollectionController::class, 'index']);
        Route::post('collections/collect-all',  [SamitiCollectionController::class, 'collectAll']);
        Route::patch('collections/{id}/toggle', [SamitiCollectionController::class, 'toggle']);

        Route::get('loans',            [SamitiLoanController::class, 'index']);
        Route::post('loans',           [SamitiLoanController::class, 'store']);
        Route::put('loans/{id}',       [SamitiLoanController::class, 'update']);   // ✅ নতুন
        Route::patch('loans/{id}/pay', [SamitiLoanController::class, 'makePayment']);
        Route::delete('loans/{id}',    [SamitiLoanController::class, 'destroy']);

        // Fund
        Route::get('fund',         [SamitiFundController::class, 'index']);
        Route::post('fund',        [SamitiFundController::class, 'store']);
        Route::put('fund/{id}',    [SamitiFundController::class, 'update']);
        Route::delete('fund/{id}', [SamitiFundController::class, 'destroy']);


        Route::get('expenses',         [SamitiExpenseController::class, 'index']);
        Route::post('expenses',        [SamitiExpenseController::class, 'store']);
        Route::put('expenses/{id}',    [SamitiExpenseController::class, 'update']);   // ✅ নতুন
        Route::delete('expenses/{id}', [SamitiExpenseController::class, 'destroy']);

        // Fines

        Route::get('fines',           [SamitiFineController::class, 'index']);
        Route::post('fines',          [SamitiFineController::class, 'store']);
        Route::put('fines/{id}',      [SamitiFineController::class, 'update']);        // ✅ নতুন
        Route::patch('fines/{id}/toggle', [SamitiFineController::class, 'toggle']);
        Route::delete('fines/{id}',   [SamitiFineController::class, 'destroy']);

        // Dividends — ⚠️ static routes BEFORE dynamic {id}
        Route::get('dividends',                 [SamitiDividendController::class, 'index']);
        Route::post('dividends/calculate',      [SamitiDividendController::class, 'calculate']);
        Route::post('dividends/distribute-all', [SamitiDividendController::class, 'distributeAll']);
        Route::patch('dividends/{id}/toggle',   [SamitiDividendController::class, 'toggle']);

        // Meetings
        Route::get('meetings',       [SamitiMeetingController::class, 'index']);
        Route::post('meetings',      [SamitiMeetingController::class, 'store']);
        Route::put('meetings/{id}',  [SamitiMeetingController::class, 'update']);   // ✅ নতুন
        Route::delete('meetings/{id}', [SamitiMeetingController::class, 'destroy']); // ✅ নতুন

        // Attendance
        Route::get('attendance',               [SamitiAttendanceController::class, 'index']);
        Route::patch('attendance/{id}/toggle', [SamitiAttendanceController::class, 'toggle']);

        // Notices — ⚠️ static 'mark-all-read' BEFORE dynamic {id}
        Route::get('notices',                [SamitiNoticeController::class, 'index']);
        Route::post('notices',               [SamitiNoticeController::class, 'store']);
        Route::post('notices/mark-all-read', [SamitiNoticeController::class, 'markAllRead']);
        Route::patch('notices/{id}/read',    [SamitiNoticeController::class, 'markRead']);
        Route::delete('notices/{id}',        [SamitiNoticeController::class, 'destroy']);

    }); // end samiti


    // ════════════════════════════════════════════════════════
    // STOCK — /api/stock/...
    // ════════════════════════════════════════════════════════
    Route::prefix('stock')->group(function () {

        // Dashboard
        Route::get('dashboard', [StockDashboardController::class, 'index']);

        // // Products — ⚠️ static routes BEFORE apiResource
        // Route::get('products/categories', [StockProductController::class, 'categories']);
        // Route::get('products/low-stock',  [StockProductController::class, 'lowStock']);
        // Route::apiResource('products',    StockProductController::class);

        Route::get('products/categories', [StockProductController::class, 'categories']);
        Route::get('products/low-stock',  [StockProductController::class, 'lowStock']);

        // ★ FIX: apiResource এর বদলে explicit routes
        // {product} এর বদলে {id} ব্যবহার করছি — Laravel আর Model Binding করবে না
        Route::get('products',           [StockProductController::class, 'index']);
        Route::post('products',          [StockProductController::class, 'store']);
        Route::get('products/{id}',      [StockProductController::class, 'show']);
        Route::put('products/{id}',      [StockProductController::class, 'update']);
        Route::patch('products/{id}',    [StockProductController::class, 'update']);
        Route::delete('products/{id}',   [StockProductController::class, 'destroy']);


        // Parties — ⚠️ ledger BEFORE apiResource
        Route::get('parties/{id}/ledger', [StockPartyController::class, 'ledger']);
        Route::apiResource('parties',     StockPartyController::class);

        // Sales — ⚠️ next-number BEFORE apiResource
        // Route::get('sales/next-number', [SaleInvoiceController::class, 'nextNumber']);
        // Route::apiResource('sales',     SaleInvoiceController::class);
       // stock/sales routes — next-number অবশ্যই {id} এর আগে
        Route::get('sales/next-number',  [SaleInvoiceController::class, 'nextNumber']);
        Route::get('sales',              [SaleInvoiceController::class, 'index']);
        Route::post('sales',             [SaleInvoiceController::class, 'store']);
        Route::get('sales/{id}',         [SaleInvoiceController::class, 'show']);
        Route::put('sales/{id}',         [SaleInvoiceController::class, 'update']);
        Route::patch('sales/{id}',       [SaleInvoiceController::class, 'update']);
        Route::delete('sales/{id}',      [SaleInvoiceController::class, 'destroy']);


        // Purchases — ⚠️ next-number BEFORE apiResource
        // Route::get('purchases/next-number', [PurchaseInvoiceController::class, 'nextNumber']);
        // Route::apiResource('purchases',     PurchaseInvoiceController::class);
        // ✅ এটা দাও
        Route::get('purchases/next-number',  [PurchaseInvoiceController::class, 'nextNumber']);
        Route::get('purchases',              [PurchaseInvoiceController::class, 'index']);
        Route::post('purchases',             [PurchaseInvoiceController::class, 'store']);
        Route::get('purchases/{id}',         [PurchaseInvoiceController::class, 'show']);
        Route::put('purchases/{id}',         [PurchaseInvoiceController::class, 'update']);
        Route::patch('purchases/{id}',       [PurchaseInvoiceController::class, 'update']);
        Route::delete('purchases/{id}',      [PurchaseInvoiceController::class, 'destroy']);

        // Adjustments
        // Route::get('adjustments',         [StockAdjustmentController::class, 'index']);
        // Route::post('adjustments',        [StockAdjustmentController::class, 'store']);
        // Route::delete('adjustments/{id}', [StockAdjustmentController::class, 'destroy']);

        Route::get    ('adjustments',         [StockAdjustmentController::class, 'index']);   // list
        Route::post   ('adjustments',         [StockAdjustmentController::class, 'store']);   // create
        Route::put    ('adjustments/{id}',    [StockAdjustmentController::class, 'update']);  // ✅ edit
        Route::delete ('adjustments/{id}',    [StockAdjustmentController::class, 'destroy']); // delete


        // Payments ─────────────────────────────────────────
        Route::get    ('payments',         [StockPaymentController::class, 'index']);   // সব payment list
        Route::post   ('payments',         [StockPaymentController::class, 'store']);   // নতুন payment তৈরি
        Route::put    ('payments/{id}',    [StockPaymentController::class, 'update']);  // ✅ payment edit
        Route::delete ('payments/{id}',    [StockPaymentController::class, 'destroy']); // payment delete

        // Expenses
        Route::apiResource('expenses', StockExpenseController::class)->except(['show']);

        // Sale Returns
        Route::get('sale-returns',          [SaleReturnController::class, 'index']);
        Route::post('sale-returns',         [SaleReturnController::class, 'store']);
        Route::put('sale-returns/{id}',     [SaleReturnController::class, 'update']);
        Route::patch('sale-returns/{id}',   [SaleReturnController::class, 'update']);
        Route::delete('sale-returns/{id}',  [SaleReturnController::class, 'destroy']);

        // Purchase Returns
        // Route::get('purchase-returns',         [PurchaseReturnController::class, 'index']);
        // Route::post('purchase-returns',        [PurchaseReturnController::class, 'store']);
        // Route::delete('purchase-returns/{id}', [PurchaseReturnController::class, 'destroy']);
        Route::get('purchase-returns',          [PurchaseReturnController::class, 'index']);
        Route::post('purchase-returns',         [PurchaseReturnController::class, 'store']);
        Route::put('purchase-returns/{id}',     [PurchaseReturnController::class, 'update']);
        Route::patch('purchase-returns/{id}',   [PurchaseReturnController::class, 'update']);
        Route::delete('purchase-returns/{id}',  [PurchaseReturnController::class, 'destroy']);

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

    }); // end stock


    // ════════════════════════════════════════════════════════
    // SCOREHUB — /api/scorehub/...
    // ════════════════════════════════════════════════════════
    Route::prefix('scorehub')->group(function () {

        // Matches
        Route::get('matches',         [ScoreMatchController::class, 'index']);
        Route::post('matches',        [ScoreMatchController::class, 'store']);
        Route::get('matches/{id}',    [ScoreMatchController::class, 'show']);
        Route::patch('matches/{id}',  [ScoreMatchController::class, 'update']);
        Route::delete('matches/{id}', [ScoreMatchController::class, 'destroy']);

        // Run Grid
        Route::patch('teams/{id}/grid', [ScoreTeamController::class, 'updateGrid']);

        // Extras
        Route::post('teams/{id}/extras',   [ScoreTeamController::class, 'addExtra']);
        Route::patch('extras/{id}/toggle', [ScoreTeamController::class, 'toggleExtra']);
        Route::delete('extras/{id}',       [ScoreTeamController::class, 'deleteExtra']);

        // Overs
        Route::post('teams/{id}/overs',   [ScoreTeamController::class, 'addOver']);
        Route::patch('overs/{id}/toggle', [ScoreTeamController::class, 'toggleOver']);

        // Bowlers
        Route::post('teams/{id}/bowlers', [ScoreTeamController::class, 'addBowler']);
        Route::delete('bowlers/{id}',     [ScoreTeamController::class, 'deleteBowler']);

        // Players
        Route::post('teams/{id}/players',           [ScorePlayerController::class, 'store']);
        Route::delete('players/{id}',               [ScorePlayerController::class, 'destroy']);
        Route::patch('players/{id}/toggle-out',     [ScorePlayerController::class, 'toggleOut']);
        Route::post('players/{id}/runs',            [ScorePlayerController::class, 'addRun']);
        Route::delete('players/{id}/runs/{runIdx}', [ScorePlayerController::class, 'removeRun']);

        // History
        Route::get('history',         [ScoreHistoryController::class, 'index']);
        Route::get('history/{id}',    [ScoreHistoryController::class, 'show']);
        Route::delete('history/{id}', [ScoreHistoryController::class, 'destroy']);


        Route::get('tournaments',                 [ScoreTournamentController::class, 'index']);
        Route::post('tournaments',                [ScoreTournamentController::class, 'store']);
        Route::get('tournaments/{id}',            [ScoreTournamentController::class, 'show']);
        Route::patch('tournaments/{id}',          [ScoreTournamentController::class, 'update']);
        Route::delete('tournaments/{id}',         [ScoreTournamentController::class, 'destroy']);

        // পয়েন্ট টেবিল + অ্যানালিসিস
        Route::get('tournaments/{id}/points-table', [ScoreTournamentController::class, 'pointsTable']);
        Route::get('tournaments/{id}/analysis',     [ScoreTournamentController::class, 'analysis']);

        // টুর্নামেন্ট দল
        Route::post('tournaments/{id}/teams',     [ScoreTournamentController::class, 'addTeam']);
        Route::patch('tournament-teams/{id}',     [ScoreTournamentController::class, 'updateTeam']);
        Route::delete('tournament-teams/{id}',    [ScoreTournamentController::class, 'removeTeam']);

        // শিডিউল (Fixtures)
        Route::post('tournaments/{id}/fixtures',  [ScoreTournamentController::class, 'addFixture']);
        Route::patch('fixtures/{id}',             [ScoreTournamentController::class, 'updateFixture']);
        Route::delete('fixtures/{id}',            [ScoreTournamentController::class, 'destroyFixture']);

        // ── বল-বল ট্র্যাকিং ────────────────────────────────────
        Route::get('teams/{id}/balls',            [ScoreBallController::class, 'index']);
        Route::post('teams/{id}/balls',           [ScoreBallController::class, 'store']);
        Route::get('teams/{id}/balls/summary',    [ScoreBallController::class, 'summary']);
        Route::patch('balls/{id}/toggle-cut',     [ScoreBallController::class, 'toggleCut']);
        Route::delete('balls/{id}',               [ScoreBallController::class, 'destroy']);


    }); // end scorehub


    // ════════════════════════════════════════════════════════
    // TAILOR — /api/tailor/...
    // ════════════════════════════════════════════════════════
    Route::prefix('tailor')->group(function () {

        // Dashboard
        Route::get('dashboard',         [TailordashboardController::class, 'index']);
        Route::get('dashboard/summary', [TailordashboardController::class, 'summary']);

        // Customers — ⚠️ static 'orders' BEFORE apiResource
        Route::get('customers/{id}/orders', [CustomerController::class, 'orders']);
        Route::apiResource('customers', CustomerController::class);

        // Orders — ⚠️ static routes BEFORE apiResource
        Route::patch('orders/{id}/status',       [OrderController::class, 'updateStatus']);
        Route::patch('orders/{id}/measurements', [OrderController::class, 'updateMeasurements']);
        Route::apiResource('orders', OrderController::class);

        // Employees
        Route::apiResource('employees', EmployeeController::class);

        // Inventory — ⚠️ static routes BEFORE apiResource
        Route::get('inventory/low-stock/list', [InventoryController::class, 'lowStock']);
        Route::patch('inventory/{id}/stock',   [InventoryController::class, 'updateStock']);
        Route::apiResource('inventory',        InventoryController::class);

        // Payments — ⚠️ static 'due/orders' BEFORE apiResource
        Route::get('payments/due/orders', [PaymentController::class, 'dueOrders']);
        Route::apiResource('payments',    PaymentController::class);

        // Dealers
        Route::apiResource('dealers', DealerController::class);

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('summary',   [TailorReportController::class, 'summary']);
            Route::get('sales',     [TailorReportController::class, 'sales']);
            Route::get('orders',    [TailorReportController::class, 'orders']);
            Route::get('customers', [TailorReportController::class, 'customers']);
            Route::get('monthly',   [TailorReportController::class, 'monthly']);
        });

        // Invoice
        Route::get('invoice/{orderId}', [InvoiceController::class, 'show']);

        // Salary — ⚠️ static routes BEFORE dynamic {employeeId}
        Route::post('salary/pay',                 [TailorSalaryController::class, 'pay']);
        Route::get('salary/all',                  [TailorSalaryController::class, 'all']);
        Route::get('salary/history/{employeeId}', [TailorSalaryController::class, 'history']);

        // Salary History
        Route::get('salary-history/all',          [SalaryHistoryController::class, 'allHistory']);
        Route::get('salary-history/{employeeId}', [SalaryHistoryController::class, 'employeeHistory']);

    }); // end tailor


    // ════════════════════════════════════════════════════════
    // BRICKKILNS — /api/brickkilns/...
    // ════════════════════════════════════════════════════════
    Route::prefix('brickkilns')->group(function () {

        // Dashboard
        Route::get('brickkilnsdashboard', [BrickkilnsDashboardController::class, 'index']);

        // Workers
        Route::apiResource('workers', BrickkilnsWorkerController::class);

        // Employees
        Route::apiResource('employees', BrickkilnsEmployeeController::class);

        // Brick Productions
        Route::apiResource('productions', BrickProductionController::class);

        // Raw Materials — ⚠️ custom routes BEFORE apiResource
        Route::post('raw-materials/{rawMaterial}/purchase', [BrickkilnsRawMaterialController::class, 'purchase']);
        Route::patch('raw-materials/{rawMaterial}/use',     [BrickkilnsRawMaterialController::class, 'use']);
        Route::apiResource('raw-materials', BrickkilnsRawMaterialController::class);

        // Customers
        Route::patch('customers/{customer}/collect', [BrickkilnsCustomerController::class, 'collectDue']);
        Route::apiResource('customers', BrickkilnsCustomerController::class);

        // Suppliers
        Route::apiResource('suppliers', BrickkilnSupplierController::class);

        // Sales
        Route::patch('sales/{sale}/pay', [BrickkilnSaleController::class, 'pay']);
        Route::apiResource('sales', BrickkilnSaleController::class);

        // Inventory — ⚠️ static 'sync' BEFORE dynamic {inventory}
        Route::get('inventory/sync',        [BrickkilnInventoryController::class, 'sync']);
        Route::get('inventory',             [BrickkilnInventoryController::class, 'index']);
        Route::put('inventory/{inventory}', [BrickkilnInventoryController::class, 'update']);
        Route::delete('inventory/{inventory}', [BrickkilnInventoryController::class, 'destroy']);

        // Expenses — ⚠️ static 'expense-categories' BEFORE apiResource
        Route::get('expense-categories', [BrickkilnExpenseController::class, 'categories']);
        Route::apiResource('expenses', BrickkilnExpenseController::class)->except(['show']);

        // Salaries — ⚠️ static 'generate' BEFORE apiResource
        Route::post('salaries/generate',      [BrickkilnSalaryController::class, 'generate']);
        Route::patch('salaries/{salary}/pay', [BrickkilnSalaryController::class, 'pay']);
        Route::apiResource('salaries', BrickkilnSalaryController::class)->except(['show']);

        // Transports
        Route::patch('transports/{transport}/status', [BrickkilnTransportController::class, 'updateStatus']);
        Route::apiResource('transports', BrickkilnTransportController::class);

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('daily-production', [BrickkilnReportController::class, 'dailyProduction']);
            Route::get('monthly-sales',    [BrickkilnReportController::class, 'monthlySales']);
            Route::get('profit-loss',      [BrickkilnReportController::class, 'profitLoss']);
            Route::get('stock-summary',    [BrickkilnReportController::class, 'stockSummary']);
        });

    }); // end brickkilns


    // ════════════════════════════════════════════════════════
    // PHARMACY — /api/pharmacy/...
    // ════════════════════════════════════════════════════════
    Route::prefix('pharmacy')->group(function () {

        // Dashboard
        Route::get('pharmacydashboard', [PharmacyDashboardController::class, 'index']);

        // Medicines — ⚠️ static routes BEFORE apiResource
        Route::get('medicines/expiry',    [PharmacyMedicineController::class, 'expiry']);
        Route::get('medicines/low-stock', [PharmacyMedicineController::class, 'lowStock']);
        Route::apiResource('medicines', PharmacyMedicineController::class);

        // Categories
        Route::apiResource('pharmacycategories', PharmacyCategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Suppliers
        Route::apiResource('pharmacysuppliers', PharmacySupplierController::class);

        // Customers
        Route::apiResource('pharmacycustomers', PharmacyCustomerController::class);

        // Purchases
        Route::apiResource('pharmacypurchases', PharmacyPurchaseController::class);

        // Sales
        Route::apiResource('sales', PharmacySaleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

        // Prescriptions
        Route::apiResource('pharmacyprescriptions', PharmacyPrescriptionController::class)->only(['index', 'store', 'update', 'destroy']);

        // Employees
        Route::apiResource('pharmacyemployees', PharmacyEmployeeController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Expenses
        Route::apiResource('pharmacyexpenses', PharmacyExpenseController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Returns
        Route::apiResource('pharmacyreturns', PharmacyReturnController::class)
            ->only(['index', 'store', 'update', 'destroy']);

    }); // end pharmacy


    // ════════════════════════════════════════════════════════
    // NURSERY — /api/nursery/...
    // ════════════════════════════════════════════════════════
Route::prefix('nursery')->group(function () {

    Route::get('nurserydashboard', [NurseryDashboardController::class, 'index']);

    Route::get('plant-categories',         [NurseryPlantCategoryController::class, 'index']);
    Route::post('plant-categories',        [NurseryPlantCategoryController::class, 'store']);
    Route::get('plant-categories/{id}',    [NurseryPlantCategoryController::class, 'show']);
    Route::put('plant-categories/{id}',    [NurseryPlantCategoryController::class, 'update']);
    Route::delete('plant-categories/{id}', [NurseryPlantCategoryController::class, 'destroy']);

    Route::get('plants/low-stock',  [NurseryPlantController::class, 'lowStock']);
    Route::get('plants',            [NurseryPlantController::class, 'index']);
    Route::post('plants',           [NurseryPlantController::class, 'store']);
    Route::get('plants/{id}',       [NurseryPlantController::class, 'show']);
    Route::put('plants/{id}',       [NurseryPlantController::class, 'update']);
    Route::delete('plants/{id}',    [NurseryPlantController::class, 'destroy']);

    Route::get('suppliers',         [NurserySupplierController::class, 'index']);
    Route::post('suppliers',        [NurserySupplierController::class, 'store']);
    Route::get('suppliers/{id}',    [NurserySupplierController::class, 'show']);
    Route::put('suppliers/{id}',    [NurserySupplierController::class, 'update']);
    Route::delete('suppliers/{id}', [NurserySupplierController::class, 'destroy']);

    Route::get('customers',         [NurseryCustomerController::class, 'index']);
    Route::post('customers',        [NurseryCustomerController::class, 'store']);
    Route::get('customers/{id}',    [NurseryCustomerController::class, 'show']);
    Route::put('customers/{id}',    [NurseryCustomerController::class, 'update']);
    Route::delete('customers/{id}', [NurseryCustomerController::class, 'destroy']);

    Route::get('purchases',         [NurseryPurchaseController::class, 'index']);
    Route::post('purchases',        [NurseryPurchaseController::class, 'store']);
    Route::get('purchases/{id}',    [NurseryPurchaseController::class, 'show']);
    Route::put('purchases/{id}',    [NurseryPurchaseController::class, 'update']);
    Route::delete('purchases/{id}', [NurseryPurchaseController::class, 'destroy']);

    Route::get('sales',             [NurserySaleController::class, 'index']);
    Route::post('sales',            [NurserySaleController::class, 'store']);
    Route::get('sales/{id}',        [NurserySaleController::class, 'show']);
    Route::put('sales/{id}',        [NurserySaleController::class, 'update']);
    Route::delete('sales/{id}',     [NurserySaleController::class, 'destroy']);

    Route::get('orders',            [NurseryOrderController::class, 'index']);
    Route::post('orders',           [NurseryOrderController::class, 'store']);
    Route::get('orders/{id}',       [NurseryOrderController::class, 'show']);
    Route::put('orders/{id}',       [NurseryOrderController::class, 'update']);
    Route::delete('orders/{id}',    [NurseryOrderController::class, 'destroy']);

    Route::get('deliveries',        [NurseryDeliveryController::class, 'index']);
    Route::post('deliveries',       [NurseryDeliveryController::class, 'store']);
    Route::get('deliveries/{id}',   [NurseryDeliveryController::class, 'show']);
    Route::put('deliveries/{id}',   [NurseryDeliveryController::class, 'update']);
    Route::delete('deliveries/{id}',[NurseryDeliveryController::class, 'destroy']);

    Route::get('employees',         [NurseryEmployeeController::class, 'index']);
    Route::post('employees',        [NurseryEmployeeController::class, 'store']);
    Route::get('employees/{id}',    [NurseryEmployeeController::class, 'show']);
    Route::put('employees/{id}',    [NurseryEmployeeController::class, 'update']);
    Route::delete('employees/{id}', [NurseryEmployeeController::class, 'destroy']);

    Route::get('garden-areas',        [NurseryGardenAreaController::class, 'index']);
    Route::post('garden-areas',       [NurseryGardenAreaController::class, 'store']);
    Route::get('garden-areas/{id}',   [NurseryGardenAreaController::class, 'show']);
    Route::put('garden-areas/{id}',   [NurseryGardenAreaController::class, 'update']);
    Route::delete('garden-areas/{id}',[NurseryGardenAreaController::class, 'destroy']);

    Route::get('plant-care',        [NurseryPlantCareController::class, 'index']);
    Route::post('plant-care',       [NurseryPlantCareController::class, 'store']);
    Route::get('plant-care/{id}',   [NurseryPlantCareController::class, 'show']);
    Route::put('plant-care/{id}',   [NurseryPlantCareController::class, 'update']);
    Route::delete('plant-care/{id}',[NurseryPlantCareController::class, 'destroy']);

    Route::get('fertilizers',        [NurseryFertilizerController::class, 'index']);
    Route::post('fertilizers',       [NurseryFertilizerController::class, 'store']);
    Route::get('fertilizers/{id}',   [NurseryFertilizerController::class, 'show']);
    Route::put('fertilizers/{id}',   [NurseryFertilizerController::class, 'update']);
    Route::delete('fertilizers/{id}',[NurseryFertilizerController::class, 'destroy']);

    Route::get('expenses',        [NurseryExpenseController::class, 'index']);
    Route::post('expenses',       [NurseryExpenseController::class, 'store']);
    Route::get('expenses/{id}',   [NurseryExpenseController::class, 'show']);
    Route::put('expenses/{id}',   [NurseryExpenseController::class, 'update']);
    Route::delete('expenses/{id}',[NurseryExpenseController::class, 'destroy']);

    Route::get('reports/daily',       [NurseryReportController::class, 'daily']);
    Route::get('reports/monthly',     [NurseryReportController::class, 'monthly']);
    Route::get('reports/annual',      [NurseryReportController::class, 'annual']);
    Route::get('reports/profit-loss', [NurseryReportController::class, 'profitLoss']);
    Route::get('reports/stock',       [NurseryReportController::class, 'stock']);

}); // end nursery

}); // end auth:sanctum
