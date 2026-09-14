<?php

use Illuminate\Http\Request;
use App\Http\Controllers\CRMController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/get-dm/{id}', [App\Http\Controllers\ProdukController::class, 'getBawahanDM'])->name('getBawahanDM');
Route::get('/get-cc/{id}', [App\Http\Controllers\ProdukController::class, 'getBawahanCC'])->name('getBawahanCC');
Route::get('/dashboard-bawahan/{id}', [App\Http\Controllers\HCOverviewController::class, 'getDataDashboardBawahan'])->name('getDataDashboardBawahan');
Route::get('/get-tasks-by-date', [App\Http\Controllers\HCOverviewController::class, 'getTasksByDate'])->name('getTaskByDate');
Route::middleware(['web', 'auth'])->prefix('hc')->group(function () {
    Route::get('/daily-work', [App\Http\Controllers\HCDailyController::class, 'dailyWorkData'])->name('hc_daily.data');
    Route::post('/daily-work/tasks', [App\Http\Controllers\HCDailyController::class, 'storeTaskApi']);
    Route::put('/daily-work/tasks/{task}', [App\Http\Controllers\HCDailyController::class, 'updateTaskApi']);
    Route::delete('/daily-work/tasks/{task}', [App\Http\Controllers\HCDailyController::class, 'deleteTaskApi']);
    Route::post('/update-schedule', [App\Http\Controllers\HCDailyController::class, 'updateSchedule'])->name('hc.updateSchedule');
    Route::post('/update-status', [App\Http\Controllers\HCDailyController::class, 'updateStatus'])->name('hc.updateStatus');
    Route::post('/cancel-remaining', [App\Http\Controllers\HCDailyController::class, 'cancelRemaining'])->name('hc.cancelRemaining');
    Route::delete('/delete-task/{id}', [App\Http\Controllers\HCDailyController::class, 'destroy']);
});
Route::post('/updateStatusChecked', [App\Http\Controllers\selectionController::class, 'updateStatus'])->name('selection.updateStatus');
Route::get('/client-pics/{id}/{userId}', [App\Http\Controllers\HCDailyController::class, 'getPic']);
Route::get('/hc-daily/get-data/{id}', [App\Http\Controllers\HCDailyController::class, 'getTasksById']);
Route::get('users/json', [App\Http\Controllers\UserController::class, 'getUsersJson'])->name('users.json');
Route::get('/produk/detail/{id}/{id_user}/{folderName}', [App\Http\Controllers\ProdukController::class, 'getDetail']);
Route::get('/manajemen-konten/{produkId}/{platform}', [App\Http\Controllers\ManajemenKontenController::class, 'getApiData'])->name('manajemen_konten.api_data');
Route::get('kpi-data/{id}/{id_user}', [App\Http\Controllers\HCOverviewController::class, 'getKPIData'])->name('kpi.data');
Route::get('kpi-data-daily/{id}/{id_user}', [App\Http\Controllers\HCOverviewController::class, 'getKPIDataDaily'])->name('kpi.data-daily');
Route::get('get-users/{id}/{id_user}', [App\Http\Controllers\HCOverviewController::class, 'getUsers'])->name('kpi.getUser');
Route::get('/get-data-division/{user}/{division}', [App\Http\Controllers\ProdukController::class, 'getDataDivision'])->name('client.getDataDivision');
Route::get('/get-data-perusahaan', [App\Http\Controllers\PerusahaanController::class, 'getDataPerusahaan'])->name('perusahaan.getDataPerusahaan');
Route::post('/produk/{divisi}', [App\Http\Controllers\ProdukController::class, 'tambahDataClient'])->name('client.tambahDataClient');
Route::PUT('/produk/{id}', [App\Http\Controllers\ProdukController::class, 'updateDataClient'])->name('client.updateDataClient');
Route::DELETE('/produk/{id}', [App\Http\Controllers\ProdukController::class, 'hapusDataClient'])->name('client.hapusDataClient');
Route::get('/produk-get/{id}', [App\Http\Controllers\ProdukController::class, 'getProduk']);
Route::get('/invoices/{id}', [App\Http\Controllers\InvoiceController::class, 'getInvoiceData'])->name('api.invoices.data');
Route::get('/get-user-divisions', [App\Http\Controllers\UserController::class, 'getUserDivision']);
Route::get('/get-data-payroll', [App\Http\Controllers\PayrollController::class, 'getDataPayroll']);
Route::get('audit-trails', [App\Http\Controllers\DashboardController::class, 'getDataAudit'])->name('api.audit.index');
// Route::get('/whatsapp/history/{leadId}', [App\Http\Controllers\CRMController::class, 'getHistory']);
Route::prefix('crm')->group(function () {
    Route::get('/ai',[CRMController::class, 'aiData'])->name('crm.api.ai');
    Route::get('/human',[CRMController::class, 'humanData'])->name('crm.api.human');
    Route::get('/meeting',[CRMController::class, 'meetingData'])->name('crm.api.meeting');
    Route::post('/{type}', [CRMController::class,'store'])->whereIn('type', ['ai', 'human']);
    Route::put('/{type}/{id}', [CRMController::class,'update'])->whereIn('type', ['ai', 'human']);
    Route::delete('/{type}/{id}', [CRMController::class,'destroy'])->whereIn('type', ['ai', 'human']);
    Route::get('/history/{leadId}', [CRMController::class,'getHistory']);
    Route::get('/human/history-media',[CRMController::class,'humanHistoryMedia'])->name('crm.api.human.history-media');
    Route::get('/human/history/{id}',[CRMController::class,'humanHistory'])->name('crm.api.human.history');
    Route::patch('/human/{id}/checklist',[CRMController::class,'updateHumanChecklist'])->name('crm.api.human.checklist');
    Route::patch('/human/{id}/follow-up',[CRMController::class,'incrementHumanFollowUp'])->name('crm.api.human.follow-up');
});
