<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/get-dm/{id}', [App\Http\Controllers\ProdukController::class, 'getBawahanDM'])->name('getBawahanDM');
Route::get('/get-cc/{id}', [App\Http\Controllers\ProdukController::class, 'getBawahanCC'])->name('getBawahanCC');

Route::post('/hc/update-schedule', [App\Http\Controllers\HCDailyController::class, 'updateSchedule'])->name('hc.updateSchedule');
Route::post('/hc/update-status', [App\Http\Controllers\HCDailyController::class, 'updateStatus'])->name('hc.updateStatus');
Route::post('hc/cancel-remaining', [App\Http\Controllers\HCDailyController::class, 'cancelRemaining'])->name('hc.cancelRemaining');
Route::delete('/hc/delete-task/{id}', [App\Http\Controllers\HCDailyController::class, 'destroy']);