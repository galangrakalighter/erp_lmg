<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/get-dm/{id}', [App\Http\Controllers\ProdukController::class, 'getBawahanDM'])->name('getBawahanDM');
Route::get('/get-cc/{id}', [App\Http\Controllers\ProdukController::class, 'getBawahanCC'])->name('getBawahanCC');
Route::get('/dashboard-bawahan/{id}', [App\Http\Controllers\HCOverviewController::class, 'getDataDashboardBawahan'])->name('getDataDashboardBawahan');
Route::get('/get-tasks-by-date', [App\Http\Controllers\HCOverviewController::class, 'getTasksByDate'])->name('getTaskByDate');
Route::post('/hc/update-schedule', [App\Http\Controllers\HCDailyController::class, 'updateSchedule'])->name('hc.updateSchedule');
Route::post('/hc/update-status', [App\Http\Controllers\HCDailyController::class, 'updateStatus'])->name('hc.updateStatus');
Route::post('hc/cancel-remaining', [App\Http\Controllers\HCDailyController::class, 'cancelRemaining'])->name('hc.cancelRemaining');
Route::delete('/hc/delete-task/{id}', [App\Http\Controllers\HCDailyController::class, 'destroy']);
Route::post('/updateStatusChecked', [App\Http\Controllers\selectionController::class, 'updateStatus'])->name('selection.updateStatus');
Route::get('/client-pics/{id}/{userId}', [App\Http\Controllers\HCDailyController::class, 'getPic']);
Route::get('/hc-daily/get-data/{id}', [App\Http\Controllers\HCDailyController::class, 'getTasksById']);
Route::get('users/json', [App\Http\Controllers\UserController::class, 'getUsersJson'])->name('users.json');
Route::get('/produk/detail/{id}/{id_user}/{folderName}', [App\Http\Controllers\ProdukController::class, 'getDetail']);
Route::get('/manajemen-konten/{produkId}/{platform}', [App\Http\Controllers\ManajemenKontenController::class, 'getApiData'])->name('manajemen_konten.api_data');
