<?php

use App\Http\Controllers\Api\BatchListController;
use App\Http\Controllers\Api\BatchPacksController;
use App\Http\Controllers\Api\PackSaleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['store.api.log', 'throttle:store-api-minute', 'store.api', 'store.api.daily-limit'])->prefix('v1')->name('api.')->group(function () {
    Route::get('/batches', [BatchListController::class, 'index'])->name('batches.index');
    Route::get('/batches/{reference}', [BatchPacksController::class, 'show'])->name('batches.show');
    Route::post('/batches/{reference}/packs/{pack}/sold', [PackSaleController::class, 'store'])
        ->middleware('store.api.mark-sold')
        ->name('packs.sold');
});
