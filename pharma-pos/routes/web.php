<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Kasir\PosController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Default route
Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes (tanpa auth middleware)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Routes dengan autentifikasi
Route::middleware(['auth'])->group(function () {
    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS Routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/cari-obat', [PosController::class, 'cariObat'])->name('cari');
        Route::post('/proses', [PosController::class, 'prosesPenjualan'])->name('proses');
        Route::get('/cetak/{id}', [PosController::class, 'cetakStruk'])->name('cetak');
    });

    // Route untuk kasir
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/kasir/dashboard', [DashboardController::class, 'kasir'])->name('kasir.dashboard');
    });

    // Master Data Routes (commented out - controllers not created yet)
    // Route::resource('obat', \App\Http\Controllers\Admin\ObatController::class);
    // Route::resource('kategori', \App\Http\Controllers\Admin\KategoriController::class);
    // Route::resource('supplier', \App\Http\Controllers\Admin\SupplierController::class);
    // Route::resource('pelanggan', \App\Http\Controllers\Admin\PelangganController::class);
    
    // Pembelian Routes
    // Route::resource('pembelian', \App\Http\Controllers\Admin\PembelianController::class);
    
    // Laporan Routes
    // Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
    // Route::get('/laporan/laba-rugi', [LaporanController::class, 'labaRugi'])->name('laporan.laba-rugi');
    
    // User Management (Admin only)
    // Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
});
