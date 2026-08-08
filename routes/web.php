<?php

use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BatchQrSheetController;
use App\Http\Controllers\Debug\ErrorPagePreviewController;
use App\Http\Controllers\Debug\QrSheetPreviewController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\Intake\PhoneScanController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\Pages\AffiliateProgramController;
use App\Http\Controllers\Pages\ApiDocsController;
use App\Http\Controllers\Pages\PrivacyPolicyController;
use App\Http\Controllers\Pages\TermsController;
use App\Http\Controllers\Pages\VerifiedController;
use App\Http\Controllers\QrScanController;
use App\Http\Controllers\Sell\AffiliateCodeController;
use App\Http\Controllers\Sell\SellCardSearchController;
use App\Http\Controllers\Sell\SubmissionCreateController;
use App\Http\Controllers\Sell\SubmissionStoreController;
use App\Http\Controllers\Sell\SubmissionThankYouController;
use App\Http\Controllers\Seller\ApiAccessController;
use App\Http\Controllers\Seller\BatchesController;
use App\Http\Controllers\Seller\BatchRequestController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\InvoicesController;
use App\Http\Controllers\Seller\PendingController;
use App\Http\Controllers\Seller\ProfileController;
use App\Http\Controllers\Seller\ScanStationController;
use App\Http\Controllers\Seller\WalletController;
use App\Http\Controllers\SellerApplication\CreateSellerApplicationController;
use App\Http\Controllers\SellerApplication\SellerApplicationThankYouController;
use App\Http\Controllers\SellerApplication\StoreSellerApplicationController;
use App\Http\Controllers\Storefront\BatchListController;
use App\Http\Controllers\Storefront\BatchVerifyController;
use App\Http\Controllers\Storefront\CardListIndexController;
use App\Http\Controllers\Storefront\StoreIndexController;
use App\Http\Controllers\Storefront\StoreShowController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->get('/debug/qr-sheet/{batch}', QrSheetPreviewController::class)
    ->name('debug.qr-sheet');

Route::middleware(['web', 'auth'])
    ->get('/debug/errors/{status}', ErrorPagePreviewController::class)
    ->name('debug.errors');

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

Route::get('/apply', CreateSellerApplicationController::class)->name('application.create');
Route::post('/apply', StoreSellerApplicationController::class)->name('application.store');
Route::get('/apply/thanks', SellerApplicationThankYouController::class)->name('application.thankyou');
Route::get('/sell', SubmissionCreateController::class)->name('sell.create');
Route::post('/sell', SubmissionStoreController::class)->name('sell.store');
Route::get('/sell/thanks/{reference}', SubmissionThankYouController::class)->name('sell.thankyou');
Route::get('/sell/search-cards', SellCardSearchController::class)
    ->middleware('throttle:20,1')
    ->name('sell.search-cards');
Route::get('/sell/verify-affiliate-code', AffiliateCodeController::class)
    ->middleware('throttle:20,1')
    ->name('sell.verify-affiliate-code');

Route::get('/affiliate-program', AffiliateProgramController::class)->name('pages.affiliate-program');
Route::get('/verified', VerifiedController::class)->name('pages.verified');
Route::get('/api-docs', ApiDocsController::class)->name('pages.api-docs');
Route::get('/terms', TermsController::class)->name('pages.terms');
Route::get('/privacy', PrivacyPolicyController::class)->name('pages.privacy');

Route::get('/image/{path}', [ImageController::class, 'show'])
    ->where('path', '.*')
    ->name('image.show');

Route::middleware(['web', 'auth', 'role:seller'])
    ->prefix('seller')
    ->name('seller.')
    ->group(function () {
        // Reachable even when the store isn't live yet — everything else below
        // redirects here until an admin flips Store::public_page_enabled.
        Route::get('/pending', PendingController::class)->name('pending');

        Route::middleware('store.live')->group(function () {
            Route::get('/', DashboardController::class)->name('dashboard');
            Route::get('/batches', [BatchesController::class, 'index'])->name('batches.index');
            Route::get('/batches/{batch}', [BatchesController::class, 'show'])->name('batches.show');
            Route::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');
            Route::get('/request-batch', [BatchRequestController::class, 'create'])->name('batches.request');
            Route::post('/request-batch', [BatchRequestController::class, 'store'])->name('batches.request.store');
            Route::get('/wallet', WalletController::class)->name('wallet');
            Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
            Route::post('/profile/{store}', [ProfileController::class, 'update'])->name('profile.update');
            Route::get('/api-access', [ApiAccessController::class, 'show'])->name('api-access.show');
            Route::post('/api-access/{store}', [ApiAccessController::class, 'toggle'])->name('api-access.toggle');
            Route::post('/api-access/{store}/regenerate', [ApiAccessController::class, 'regenerate'])->name('api-access.regenerate');
            Route::get('/scan', [ScanStationController::class, 'show'])->name('scan');
            Route::post('/scan', [ScanStationController::class, 'scan'])->name('scan.submit');
        });
    });

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/invoices/{invoice}/pdf', InvoicePdfController::class)
        ->name('invoices.pdf');
});

// Not role:admin-gated — while impersonating, the authenticated user IS the
// (possibly non-admin) target, and they still need to be able to end it.
// ImpersonateController::stop() checks the impersonator_id session key instead.
// (Starting impersonation happens server-side via the Filament "Impersonate"
// user action, not an HTTP route — see UserResource::impersonateAction().)
Route::middleware(['web', 'auth'])
    ->post('/admin/impersonate/stop', [ImpersonateController::class, 'stop'])
    ->name('impersonate.stop');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});
Route::get('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/q/{token}', QrScanController::class)->name('qr.scan');

Route::get('/rapid-intake-scan/{token}', [PhoneScanController::class, 'show'])
    ->name('rapid-intake.scan.show');
Route::post('/rapid-intake-scan/{token}/frame', [PhoneScanController::class, 'frame'])
    ->middleware('throttle:60,1')
    ->name('rapid-intake.scan.frame');

Route::get('/stores', StoreIndexController::class)->name('stores.index');
Route::get('/card-lists', CardListIndexController::class)->name('card-lists.index');
Route::get('/{store:slug}', StoreShowController::class)->name('stores.show');
Route::get('/{store:slug}/{batch}', BatchListController::class)->name('stores.lists.show');
Route::get('/{store:slug}/{batch}/verify', [BatchVerifyController::class, 'show'])->name('stores.lists.verify');
