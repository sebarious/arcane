<?php

use App\Http\Controllers\Api\BatchPacksController;
use App\Http\Controllers\Api\PackSaleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:store-api-minute', 'store.api', 'store.api.daily-limit'])->prefix('v1')->name('api.')->group(function () {
    Route::get('/batches/{reference}', [BatchPacksController::class, 'show'])->name('batches.show');
    Route::post('/batches/{reference}/packs/{pack}/sold', [PackSaleController::class, 'store'])->name('packs.sold');
});
