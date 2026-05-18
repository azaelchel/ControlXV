<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CompanionController;
use App\Http\Controllers\ConfirmedTableController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\PublicGuestReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemTransferController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/revision-invitados/{guest}/{token}', [PublicGuestReviewController::class, 'show'])->name('guest-review.show');
Route::put('/revision-invitados/{guest}/{token}', [PublicGuestReviewController::class, 'update'])->name('guest-review.update');
Route::post('/revision-invitados/{guest}/{token}/decline', [PublicGuestReviewController::class, 'decline'])->name('guest-review.decline');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/guests/import', [GuestController::class, 'import'])->name('guests.import');
    Route::get('/guests/export', [GuestController::class, 'export'])->name('guests.export');
    Route::patch('/guests/{guest}/quick-update', [GuestController::class, 'quickUpdate'])->name('guests.quick-update');
    Route::patch('/guests/{guest}/status', [GuestController::class, 'updateStatus'])->name('guests.status');
    Route::post('/guests/{guest}/public-link', [GuestController::class, 'generatePublicLink'])->name('guests.public-link');
    Route::resource('guests', GuestController::class)->except('show');
    Route::get('/companions', [CompanionController::class, 'index'])->name('companions.index');
    Route::get('/companions/create', [CompanionController::class, 'create'])->name('companions.create');
    Route::post('/companions', [CompanionController::class, 'store'])->name('companions.store');
    Route::get('/companions/{companion}/edit', [CompanionController::class, 'edit'])->name('companions.edit');
    Route::put('/companions/{companion}', [CompanionController::class, 'update'])->name('companions.update');
    Route::delete('/companions/{companion}', [CompanionController::class, 'destroy'])->name('companions.destroy');
    Route::get('/companions/export', [CompanionController::class, 'export'])->name('companions.export');
    Route::get('/confirmed-tables', [ConfirmedTableController::class, 'index'])->name('tables.index');
    Route::get('/catalogs', [CatalogController::class, 'index'])->name('catalogs.index');
    Route::post('/catalogs', [CatalogController::class, 'store'])->name('catalogs.store');
    Route::put('/catalogs/{catalog}', [CatalogController::class, 'update'])->name('catalogs.update');
    Route::delete('/catalogs/{catalog}', [CatalogController::class, 'destroy'])->name('catalogs.destroy');
    Route::get('/system-transfer', [SystemTransferController::class, 'edit'])->name('system-transfer.edit');
    Route::post('/system-transfer/import', [SystemTransferController::class, 'import'])->name('system-transfer.import');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
