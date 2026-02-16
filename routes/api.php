<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/get-dm/{id}', [App\http\controllers\ProdukController::class, 'getBawahanDM'])->name('getBawahanDM');
Route::get('/get-cc/{id}', [App\http\controllers\ProdukController::class, 'getBawahanCC'])->name('getBawahanCC');

Route::post('/hc/update-schedule', [App\Http\controllers\HCDailyController::class, 'updateSchedule'])->name('hc.updateSchedule');
Route::post('/hc/update-status', [App\Http\controllers\HCDailyController::class, 'updateStatus'])->name('hc.updateStatus');
Route::post('hc/cancel-remaining', [App\Http\controllers\HCDailyController::class, 'cancelRemaining'])->name('hc.cancelRemaining');
Route::delete('/hc/delete-task/{id}', [App\Http\controllers\HCDailyController::class, 'destroy']);