<?php

use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\Api\MarketingAnalyticsController;
use App\Http\Controllers\BarangClientController;
use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\brandingController;
use App\Http\Controllers\ClientWorkspacePlaceholderController;
use App\Http\Controllers\CRMController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HCDailyController;
use App\Http\Controllers\HCOverviewController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ManajemenKontenController;
use App\Http\Controllers\marketResearchController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\selectionController;
use App\Http\Controllers\SosialCreatorController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureOAuthAccountIsUnlocked;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\ProtectMarketingMcpResponse;
use App\Http\Middleware\RequireMarketingMcpToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CheckToken;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

$marketingMcpRoutes = function (): void {
    Route::get('/clients', [MarketingAnalyticsController::class, 'clients'])->name('clients');
    Route::get('/division-client-summary', [MarketingAnalyticsController::class, 'divisionClientSummary'])
        ->name('division-client-summary');
    Route::get('/clients/{client}/data-availability', [MarketingAnalyticsController::class, 'dataAvailability'])
        ->whereNumber('client')->name('data-availability');
    Route::get('/clients/{client}/performance-summary', [MarketingAnalyticsController::class, 'performanceSummary'])
        ->whereNumber('client')->name('performance-summary');
    Route::get('/clients/{client}/content-plan', [MarketingAnalyticsController::class, 'contentPlan'])
        ->whereNumber('client')->name('content-plan');
    Route::get('/clients/{client}/content-mix', [MarketingAnalyticsController::class, 'contentMix'])
        ->whereNumber('client')->name('content-mix');
    Route::get('/clients/{client}/performance-trend', [MarketingAnalyticsController::class, 'performanceTrend'])
        ->whereNumber('client')->name('performance-trend');
    Route::get('/clients/{client}/top-content', [MarketingAnalyticsController::class, 'topContent'])
        ->whereNumber('client')->name('top-content');
    Route::get('/clients/{client}/paid-performance', [MarketingAnalyticsController::class, 'paidPerformance'])
        ->whereNumber('client')->name('paid-performance');
    Route::get('/clients/{client}/market-research', [MarketingAnalyticsController::class, 'marketResearch'])
        ->whereNumber('client')->name('market-research');
    Route::get('/clients/{client}/brand-guidelines', [MarketingAnalyticsController::class, 'brandGuidelines'])
        ->whereNumber('client')->name('brand-guidelines');
    Route::get('/clients/{client}/content-calendar', [MarketingAnalyticsController::class, 'contentCalendar'])
        ->whereNumber('client')->name('content-calendar');
    Route::get('/clients/{client}/tasks', [MarketingAnalyticsController::class, 'tasks'])
        ->whereNumber('client')->name('tasks');
    Route::get('/clients/{client}/approvals', [MarketingAnalyticsController::class, 'approvals'])
        ->whereNumber('client')->name('approvals');
    Route::get('/clients/{client}/assets', [MarketingAnalyticsController::class, 'assets'])
        ->whereNumber('client')->name('assets');
    Route::get('/clients/{client}/campaigns', [MarketingAnalyticsController::class, 'campaigns'])
        ->whereNumber('client')->name('campaigns');
    Route::get('/clients/{client}/performance-metrics', [MarketingAnalyticsController::class, 'performanceMetrics'])
        ->whereNumber('client')->name('performance-metrics');
    Route::get('/clients/{client}/period-comparison', [MarketingAnalyticsController::class, 'periodComparison'])
        ->whereNumber('client')->name('period-comparison');
    Route::get('/clients/{client}/channel-analysis', [MarketingAnalyticsController::class, 'channelAnalysis'])
        ->whereNumber('client')->name('channel-analysis');
    Route::get('/clients/{client}/search', [MarketingAnalyticsController::class, 'search'])
        ->whereNumber('client')->name('search');
};

Route::middleware([ProtectMarketingMcpResponse::class, 'auth:sanctum', RequireMarketingMcpToken::class, 'throttle:60,1'])
    ->prefix('marketing-mcp')
    ->name('api.marketing-mcp.')
    ->group($marketingMcpRoutes);

Route::middleware([
    ProtectMarketingMcpResponse::class,
    'auth:oauth-api',
    CheckToken::using('marketing:read'),
    EnsureOAuthAccountIsUnlocked::class,
    'throttle:60,1',
])->prefix('oauth-marketing-mcp')->name('api.oauth-marketing-mcp.')->group($marketingMcpRoutes);

Route::middleware(['web', 'auth'])->get(
    '/selectable-users',
    [UserController::class, 'selectableUsers'],
)->name('api.selectable-users');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/get-dm/{id}', [ProdukController::class, 'getBawahanDM'])->name('getBawahanDM');
    Route::get('/get-cc/{id}', [ProdukController::class, 'getBawahanCC'])->name('getBawahanCC');
    Route::get('/dashboard-bawahan/{id}', [HCOverviewController::class, 'getDataDashboardBawahan'])->name('getDataDashboardBawahan');
    Route::get('/get-tasks-by-date', [HCOverviewController::class, 'getTasksByDate'])->name('getTaskByDate');
});
Route::middleware(['web', 'auth'])->prefix('hc')->group(function () {
    Route::get('/daily-work', [HCDailyController::class, 'dailyWorkData'])->name('hc_daily.data');
    Route::get('/daily-work/schedule-preview', [HCDailyController::class, 'schedulePreview']);
    Route::post('/daily-work/tasks', [HCDailyController::class, 'storeTaskApi']);
    Route::put('/daily-work/tasks/{task}', [HCDailyController::class, 'updateTaskApi']);
    Route::delete('/daily-work/tasks/{task}', [HCDailyController::class, 'deleteTaskApi']);
    Route::get('/daily-work/tasks/{task}/proof', [HCDailyController::class, 'viewCompletionProof']);
    Route::post('/update-schedule', [HCDailyController::class, 'updateSchedule'])->name('hc.updateSchedule');
    Route::post('/update-status', [HCDailyController::class, 'updateStatus'])->name('hc.updateStatus');
    Route::post('/cancel-remaining', [HCDailyController::class, 'cancelRemaining'])->name('hc.cancelRemaining');
    Route::delete('/delete-task/{id}', [HCDailyController::class, 'destroy']);
});

Route::middleware(['web', 'auth'])->prefix('mcn')->name('api.mcn.')->group(function () {
    Route::get('/projects', [BarangClientController::class, 'dataApi'])->name('projects');
    Route::get('/affiliators', [SosialCreatorController::class, 'dataApi'])->name('affiliators');
    Route::get('/blacklist', [BlacklistController::class, 'dataApi'])->name('blacklist');
});

Route::middleware(['web', 'auth', 'digital-marketing.client'])
    ->prefix('digital-marketing/workspace')
    ->name('api.digital-marketing.')
    ->group(function () {
        Route::get('/market-research/{id}/{platform}', [marketResearchController::class, 'index'])
            ->name('market-research');
        Route::get('/branding/{id}/{platform}', [brandingController::class, 'index'])
            ->name('branding');
        Route::get('/social-media-research/{id}/{platform}', [ClientWorkspacePlaceholderController::class, 'show'])
            ->name('social-media-research');
        Route::get('/advertising-research/{id}/{platform}', [ClientWorkspacePlaceholderController::class, 'show'])
            ->name('advertising-research');
        Route::get('/analisis/{produk}/{platform}', [AnalisisController::class, 'index'])
            ->name('analisis');
        Route::get('/content-plan/{produk_id}/{platform}', [ManajemenKontenController::class, 'index'])
            ->name('content-plan');
        Route::get('/explore/{id}', [ClientWorkspacePlaceholderController::class, 'show'])
            ->name('explore');
    });

Route::middleware(['web', 'auth'])
    ->prefix('business-dev/workspace')
    ->name('api.business-dev.')
    ->group(function () {
        Route::get('/market-research/{id}/{platform}', [marketResearchController::class, 'index'])->name('market-research');
        Route::get('/branding/{id}/{platform}', [brandingController::class, 'index'])->name('branding');
        Route::get('/analisis/{produk}/{platform}', [AnalisisController::class, 'index'])->name('analisis');
        Route::get('/content-plan/{produk_id}/{platform}', [ManajemenKontenController::class, 'index'])->name('content-plan');
    });

Route::middleware(['web', 'auth', 'multimedia.client'])
    ->prefix('multimedia/workspace')
    ->name('api.multimedia.')
    ->group(function () {
        Route::get('/market-research/{id}/{platform}', [marketResearchController::class, 'index'])->name('market-research');
        Route::get('/branding/{id}/{platform}', [brandingController::class, 'index'])->name('branding');
        Route::get('/social-media-research/{id}/{platform}', [ClientWorkspacePlaceholderController::class, 'show'])->name('social-media-research');
        Route::get('/advertising-research/{id}/{platform}', [ClientWorkspacePlaceholderController::class, 'show'])->name('advertising-research');
        Route::get('/analisis/{produk}/{platform}', [AnalisisController::class, 'index'])->name('analisis');
        Route::get('/content-plan/{produk_id}/{platform}', [ManajemenKontenController::class, 'index'])->name('content-plan');
        Route::get('/explore/{id}', [ClientWorkspacePlaceholderController::class, 'show'])->name('explore');
    });

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/updateStatusChecked', [selectionController::class, 'updateStatus'])->name('selection.updateStatus');
    Route::get('/client-pics/{id}/{userId}', [HCDailyController::class, 'getPic']);
    Route::get('/hc-daily/get-data/{id}', [HCDailyController::class, 'getTasksById']);
    Route::get('users/json', [UserController::class, 'getUsersJson'])
        ->middleware(EnsureUserIsAdmin::class)
        ->name('users.json');
    Route::get('/produk/detail/{id}/{id_user}/{folderName}', [ProdukController::class, 'getDetail']);
    Route::get('/manajemen-konten/{produkId}/{platform}', [ManajemenKontenController::class, 'getApiData'])
        ->name('manajemen_konten.api_data');
    Route::get('kpi-data/{id}/{id_user}', [HCOverviewController::class, 'getKPIData'])
        ->name('kpi.data');
    Route::get('kpi-data-daily/{id}/{id_user}', [HCOverviewController::class, 'getKPIDataDaily'])->name('kpi.data-daily');
    Route::get('get-users/{id}/{id_user}', [HCOverviewController::class, 'getUsers'])->name('kpi.getUser');
    Route::get('/get-data-division/{user}/{division}', [ProdukController::class, 'getDataDivision'])->name('client.getDataDivision');
    Route::get('/get-data-perusahaan', [PerusahaanController::class, 'getDataPerusahaan'])
        ->name('perusahaan.getDataPerusahaan');
    Route::post('/produk/{divisi}', [ProdukController::class, 'tambahDataClient'])->name('client.tambahDataClient');
    Route::put('/produk/{id}', [ProdukController::class, 'updateDataClient'])->name('client.updateDataClient');
    Route::delete('/produk/{id}', [ProdukController::class, 'hapusDataClient'])->name('client.hapusDataClient');
    Route::get('/produk-get/{id}', [ProdukController::class, 'getProduk']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'getInvoiceData'])->name('api.invoices.data');
    Route::get('/get-user-divisions', [UserController::class, 'getUserDivision']);
    Route::get('/get-data-payroll', [PayrollController::class, 'getDataPayroll']);
    Route::get('audit-trails', [DashboardController::class, 'getDataAudit'])->name('api.audit.index');

    Route::prefix('crm')->group(function () {
        Route::get('/ai', [CRMController::class, 'aiData'])->name('crm.api.ai');
        Route::get('/human', [CRMController::class, 'humanData'])->name('crm.api.human');
        Route::get('/meeting', [CRMController::class, 'meetingData'])->name('crm.api.meeting');
        Route::post('/{type}', [CRMController::class, 'store'])->whereIn('type', ['ai', 'human']);
        Route::put('/{type}/{id}', [CRMController::class, 'update'])->whereIn('type', ['ai', 'human']);
        Route::delete('/{type}/{id}', [CRMController::class, 'destroy'])->whereIn('type', ['ai', 'human']);
        Route::get('/history/{leadId}', [CRMController::class, 'getHistory']);
        Route::get('/human/history-media', [CRMController::class, 'humanHistoryMedia'])->name('crm.api.human.history-media');
        Route::get('/human/history/{id}', [CRMController::class, 'humanHistory'])->name('crm.api.human.history');
        Route::patch('/human/{id}/checklist', [CRMController::class, 'updateHumanChecklist'])->name('crm.api.human.checklist');
        Route::patch('/human/{id}/follow-up', [CRMController::class, 'incrementHumanFollowUp'])->name('crm.api.human.follow-up');
    });
});
