<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CompanionController;
use App\Http\Controllers\ConfirmedTableController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\MessageSendController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\PublicGuestReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SystemTransferController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->to(Auth::user()->preferredHomeUrl());
});

Route::get('/revision-invitados/{guest}/{token}', [PublicGuestReviewController::class, 'show'])->name('guest-review.show');
Route::put('/revision-invitados/{guest}/{token}', [PublicGuestReviewController::class, 'update'])->name('guest-review.update');
Route::post('/revision-invitados/{guest}/{token}/decline', [PublicGuestReviewController::class, 'decline'])->name('guest-review.decline');

Route::middleware('auth')->group(function () {
    Route::middleware('module:dashboard')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('module:guests')->group(function () {
        Route::post('/guests/import', [GuestController::class, 'import'])->name('guests.import');
        Route::get('/guests/export', [GuestController::class, 'export'])->name('guests.export');
        Route::patch('/guests/{guest}/quick-update', [GuestController::class, 'quickUpdate'])->name('guests.quick-update');
        Route::patch('/guests/{guest}/status', [GuestController::class, 'updateStatus'])->name('guests.status');
        Route::post('/guests/{guest}/public-link', [GuestController::class, 'generatePublicLink'])->name('guests.public-link');
        Route::post('/guests/{guest}/public-link/cancel', [GuestController::class, 'cancelPublicLink'])->name('guests.public-link.cancel');
        Route::resource('guests', GuestController::class)->except('show');
    });

    Route::middleware('module:companions')->group(function () {
        Route::get('/companions', [CompanionController::class, 'index'])->name('companions.index');
        Route::get('/companions/create', [CompanionController::class, 'create'])->name('companions.create');
        Route::post('/companions', [CompanionController::class, 'store'])->name('companions.store');
        Route::get('/companions/{companion}/edit', [CompanionController::class, 'edit'])->name('companions.edit');
        Route::put('/companions/{companion}', [CompanionController::class, 'update'])->name('companions.update');
        Route::delete('/companions/{companion}', [CompanionController::class, 'destroy'])->name('companions.destroy');
        Route::get('/companions/export', [CompanionController::class, 'export'])->name('companions.export');
    });

    Route::middleware('module:tables')->group(function () {
        Route::get('/confirmed-tables', [ConfirmedTableController::class, 'index'])->name('tables.index');
    });

    Route::middleware('module:catalogs')->group(function () {
        Route::get('/catalogs', [CatalogController::class, 'index'])->name('catalogs.index');
        Route::post('/catalogs', [CatalogController::class, 'store'])->name('catalogs.store');
        Route::put('/catalogs/{catalog}', [CatalogController::class, 'update'])->name('catalogs.update');
        Route::delete('/catalogs/{catalog}', [CatalogController::class, 'destroy'])->name('catalogs.destroy');
    });

    Route::middleware('module:message_templates')->group(function () {
        Route::get('/message-templates', [MessageTemplateController::class, 'index'])->name('message-templates.index');
        Route::post('/message-templates', [MessageTemplateController::class, 'store'])->name('message-templates.store');
        Route::put('/message-templates/{template}', [MessageTemplateController::class, 'update'])->name('message-templates.update');
        Route::patch('/message-templates/{template}/toggle', [MessageTemplateController::class, 'toggle'])->name('message-templates.toggle');
        Route::delete('/message-templates/{template}', [MessageTemplateController::class, 'destroy'])->name('message-templates.destroy');
    });

    Route::middleware('module:message_sends')->group(function () {
        Route::get('/message-sends', [MessageSendController::class, 'index'])->name('message-sends.index');
        Route::get('/message-sends/history', [MessageSendController::class, 'history'])->name('message-sends.history');
        Route::get('/message-sends/new', [MessageSendController::class, 'create'])->name('message-sends.create');
        Route::post('/message-sends/prepare', [MessageSendController::class, 'prepare'])->name('message-sends.prepare');
        Route::post('/message-sends/render/{guest}', [MessageSendController::class, 'renderOne'])->name('message-sends.render');
        Route::get('/message-sends/companions/{guest}', [MessageSendController::class, 'companions'])->name('message-sends.companions');
        Route::post('/message-sends', [MessageSendController::class, 'store'])->name('message-sends.store');
        Route::delete('/message-sends/{messageSend}', [MessageSendController::class, 'destroy'])->name('message-sends.destroy');
        Route::post('/message-sends/reset-opened/{publicGuestLink}', [MessageSendController::class, 'resetOpened'])->name('message-sends.reset-opened');
    });

    Route::middleware('module:system_transfer')->group(function () {
        Route::get('/system-transfer', [SystemTransferController::class, 'edit'])->name('system-transfer.edit');
        Route::post('/system-transfer/import', [SystemTransferController::class, 'import'])->name('system-transfer.import');
        Route::get('/system-transfer/contacts-csv', [SystemTransferController::class, 'exportContactsCsv'])->name('system-transfer.contacts-csv');
    });

    Route::middleware('module:settings')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });

    Route::middleware('module:users')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle', [UserManagementController::class, 'toggle'])->name('users.toggle');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
