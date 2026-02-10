<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/get-dm/{id}', [App\http\controllers\ProdukController::class, 'getBawahanDM'])->name('getBawahanDM');
Route::get('/get-cc/{id}', [App\http\controllers\ProdukController::class, 'getBawahanCC'])->name('getBawahanCC');