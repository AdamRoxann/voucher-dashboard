<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

// Route::middleware('guest')->group(function () {
//     Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
//     Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
// });

// Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

// Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
// Route::resource('claims', ClaimController::class);
// Route::patch('/claims/{claim}/transfer-status', [ClaimController::class, 'updateTransferStatus'])->name('claims.transfer-status');

// Route::middleware('auth')->group(function () {
    
//     Route::get('/exports/claims.xlsx', [ClaimController::class, 'export'])->name('claims.export');
//     Route::resource('stores', StoreController::class)->except(['show']);
    

//     Route::prefix('admin')->name('admin.')->middleware(EnsureUserIsAdmin::class)->group(function () {
//         Route::get('/', [AdminController::class, 'index'])->name('index');
//         Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
//         Route::post('/stores', [AdminController::class, 'addStore'])->name('stores.store');
//         Route::patch('/stores/{store}/toggle', [AdminController::class, 'toggleStore'])->name('stores.toggle');
//         Route::patch('/claims/{claim}/transfer', [AdminController::class, 'updateTransfer'])->name('claims.transfer');
//     });
// });

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Public Dashboard and Claim Submission
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/claims/create', [ClaimController::class, 'create'])
    ->name('claims.create');

Route::post('/claims', [ClaimController::class, 'store'])
    ->name('claims.store');

/*
|--------------------------------------------------------------------------
| Admin-Only Claim and Store Management
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/exports/claim-proofs.zip', [ClaimController::class, 'downloadProofs'])
    ->name('claims.proofs.download');
    // Claim details, editing, updating and deletion.
    // Public create/store routes remain outside this group.
    Route::resource('claims', ClaimController::class)
        ->except(['index', 'create', 'store']);

    // Change transfer status directly from the dashboard.
    Route::patch('/claims/{claim}/transfer-status', [
        ClaimController::class,
        'updateTransferStatus',
    ])->name('claims.transfer-status');

    // Export filtered claim records.
    Route::get('/exports/claims.xlsx', [
        ClaimController::class,
        'export',
    ])->name('claims.export');

    // Store management.
    Route::resource('stores', StoreController::class)
        ->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Admin Settings and Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', [AdminController::class, 'index'])
                ->name('index');

            Route::put('/settings', [
                AdminController::class,
                'updateSettings',
            ])->name('settings.update');

            Route::post('/stores', [
                AdminController::class,
                'addStore',
            ])->name('stores.store');

            Route::patch('/stores/{store}/toggle', [
                AdminController::class,
                'toggleStore',
            ])->name('stores.toggle');

            Route::patch('/claims/{claim}/transfer', [
                AdminController::class,
                'updateTransfer',
            ])->name('claims.transfer');
        });
});

