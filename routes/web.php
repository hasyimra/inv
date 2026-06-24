<?php

use App\Http\Controllers\Auth\DevLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SsoCallbackController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvAdjustmentController;
use App\Http\Controllers\PhysicalCountController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockBalanceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'redirect'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/sso/callback', SsoCallbackController::class)->name('sso.callback');

Route::get('/dev-login', [DevLoginController::class, 'index'])->name('dev-login.index');
Route::post('/dev-login/{user}', [DevLoginController::class, 'login'])->name('dev-login.login');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // --- Users (sso_admin only, enforced in controller via middleware below) ---
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('role:sso_admin,admin');

    // --- Reports ---
    Route::get('reports/stock-movement-history', [ReportController::class, 'stockMovementHistory'])->name('reports.stock-movement-history');
    Route::get('reports/adjustment-count-history', [ReportController::class, 'adjustmentCountHistory'])->name('reports.adjustment-count-history');

    // --- Stock Balances (read-only) ---
    Route::get('stock-balances', [StockBalanceController::class, 'index'])->name('stock-balances.index');
    Route::get('stock-balances/{stockBalance}', [StockBalanceController::class, 'show'])->name('stock-balances.show');

    // --- Adjustments: static routes before {adjustment} ---
    Route::get('adjustments', [InvAdjustmentController::class, 'index'])->name('adjustments.index');
    Route::get('adjustments/create', [InvAdjustmentController::class, 'create'])->name('adjustments.create')->middleware('role:admin,user');
    Route::post('adjustments', [InvAdjustmentController::class, 'store'])->name('adjustments.store')->middleware('role:admin,user');
    Route::get('adjustments/{adjustment}', [InvAdjustmentController::class, 'show'])->name('adjustments.show');
    Route::delete('adjustments/{adjustment}', [InvAdjustmentController::class, 'destroy'])->name('adjustments.destroy')->middleware('role:admin,user');
    Route::post('adjustments/{adjustment}/submit', [InvAdjustmentController::class, 'submit'])->name('adjustments.submit')->middleware('role:admin,user');
    Route::post('adjustments/{adjustment}/approve', [InvAdjustmentController::class, 'approve'])->name('adjustments.approve')->middleware('role:admin,approval');
    Route::post('adjustments/{adjustment}/reject', [InvAdjustmentController::class, 'reject'])->name('adjustments.reject')->middleware('role:admin,approval');

    // --- Physical Counts: static routes before {physicalCount} ---
    Route::get('physical-counts', [PhysicalCountController::class, 'index'])->name('physical-counts.index');
    Route::get('physical-counts/create', [PhysicalCountController::class, 'create'])->name('physical-counts.create')->middleware('role:admin,user');
    Route::post('physical-counts', [PhysicalCountController::class, 'store'])->name('physical-counts.store')->middleware('role:admin,user');
    Route::get('physical-counts/{physicalCount}/count-sheet', [PhysicalCountController::class, 'countSheet'])->name('physical-counts.count-sheet');
    Route::get('physical-counts/{physicalCount}', [PhysicalCountController::class, 'show'])->name('physical-counts.show');
    Route::get('physical-counts/{physicalCount}/edit', [PhysicalCountController::class, 'edit'])->name('physical-counts.edit')->middleware('role:admin,user');
    Route::put('physical-counts/{physicalCount}', [PhysicalCountController::class, 'update'])->name('physical-counts.update')->middleware('role:admin,user');
    Route::delete('physical-counts/{physicalCount}', [PhysicalCountController::class, 'destroy'])->name('physical-counts.destroy')->middleware('role:admin,user');
    Route::post('physical-counts/{physicalCount}/submit', [PhysicalCountController::class, 'submit'])->name('physical-counts.submit')->middleware('role:admin,user');
    Route::post('physical-counts/{physicalCount}/approve', [PhysicalCountController::class, 'approve'])->name('physical-counts.approve')->middleware('role:admin,approval');
    Route::post('physical-counts/{physicalCount}/reject', [PhysicalCountController::class, 'reject'])->name('physical-counts.reject')->middleware('role:admin,approval');
});
