<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BatchQrSheetController;
use App\Http\Controllers\QrScanController;
use App\Http\Controllers\QrConfirmController;
use App\Http\Controllers\Storefront\StoreIndexController;
use App\Http\Controllers\Storefront\StoreShowController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\BatchesController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\Seller\InvoicesController;
use App\Http\Controllers\Storefront\BatchListController;

Route::get('/', fn () => Inertia::render('Welcome'));

Route::middleware(['web', 'auth'])  // tighten with an 'admin' gate later
  ->get('/admin/batches/{batch}/qr-sheet', BatchQrSheetController::class)
  ->name('batches.qr-sheet');

Route::middleware(['web', 'auth', 'role:seller'])
  ->prefix('seller')
  ->name('seller.')
  ->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/batches', [BatchesController::class, 'index'])->name('batches.index');
    Route::get('/batches/{batch}', [BatchesController::class, 'show'])->name('batches.show');
    Route::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');
  });

Route::middleware(['web', 'auth'])->group(function () {
  Route::get('/admin/invoices/{invoice}/pdf', InvoicePdfController::class)
    ->name('invoices.pdf');
});

Route::middleware('guest')->group(function () {
  Route::get('/login', [LoginController::class, 'show'])->name('login');
  Route::post('/login', [LoginController::class, 'store']);
});
Route::get('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/q/{token}', QrScanController::class)->name('qr.scan');
Route::post('/q/{token}/confirm', QrConfirmController::class)->name('qr.confirm');

Route::get('/stores', StoreIndexController::class)->name('stores.index');
Route::get('/{store:slug}', StoreShowController::class)->name('stores.show');
Route::get('/{store:slug}/{batch}', BatchListController::class)->name('store.lists.show');