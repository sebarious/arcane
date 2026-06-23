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
use App\Http\Controllers\Sell\SubmissionCreateController;
use App\Http\Controllers\Sell\SubmissionStoreController;
use App\Http\Controllers\Sell\SubmissionThankYouController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Seller\BatchRequestController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SellerApplication\CreateSellerApplicationController;
use App\Http\Controllers\SellerApplication\StoreSellerApplicationController;
use App\Http\Controllers\SellerApplication\SellerApplicationThankYouController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Debug\QrSheetPreviewController;

Route::middleware(['web', 'auth'])
  ->get('/debug/qr-sheet/{batch}', QrSheetPreviewController::class)
  ->name('debug.qr-sheet');

Route::get('/', HomeController::class)->name('home');

Route::middleware(['web', 'auth'])->get('/dashboard', function () {
    return request()->user()->hasRole('admin') ? redirect('/admin') : redirect()->route('seller.dashboard');
})->name('dashboard');

Route::middleware(['web', 'auth'])  // tighten with an 'admin' gate later
  ->get('/admin/batches/{batch}/qr-sheet', BatchQrSheetController::class)
  ->name('batches.qr-sheet');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->name('password.update');
});

Route::get('/apply', CreateSellerApplicationController::class)->name('seller-applications.create');
Route::post('/apply', StoreSellerApplicationController::class)->name('seller-applications.store');
Route::get('/apply/thanks', SellerApplicationThankYouController::class)->name('seller-applications.thankyou');
Route::get('/sell', SubmissionCreateController::class)->name('sell.create');
Route::post('/sell', SubmissionStoreController::class)->name('sell.store');
Route::get('/sell/thanks/{reference}', SubmissionThankYouController::class)->name('sell.thankyou');

Route::get('/image/{path}', [ImageController::class, 'show'])
  ->where('path', '.*')
  ->name('image.show')
  ->middleware('signed');

Route::middleware(['web', 'auth', 'role:seller'])
  ->prefix('seller')
  ->name('seller.')
  ->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/batches', [BatchesController::class, 'index'])->name('batches.index');
    Route::get('/batches/{batch}', [BatchesController::class, 'show'])->name('batches.show');
    Route::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');
    Route::get('/request-batch', [BatchRequestController::class, 'create'])->name('batches.request');
    Route::post('/request-batch', [BatchRequestController::class, 'store'])->name('batches.request.store');
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