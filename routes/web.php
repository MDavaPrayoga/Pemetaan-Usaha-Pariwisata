<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUsahaController;

// Rute utama: menampilkan halaman peta WebGIS publik
Route::get('/', [PetaController::class, 'index'])->name('peta.index');

// Rute API: mengambil data 1.421 titik koordinat usaha berstatus verified dalam format JSON
Route::get('/api/titik-usaha', [PetaController::class, 'dataTitik'])->name('api.titik');

// Rute API: menerima parameter eps dan minPts dari slider lalu menjalankan kalkulasi DBSCAN
Route::post('/api/simulasi-dbscan', [PetaController::class, 'simulasiKlaster'])->name('api.simulasi');

// Rute autentikasi administrator
Route::get('/login', [AuthController::class, 'tampilkanFormLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'prosesLogin'])->name('login.proses')->middleware('guest');
Route::post('/logout', [AuthController::class, 'prosesLogout'])->name('logout')->middleware('auth');

// Rute panel administrator: hanya bisa diakses oleh admin yang sudah login
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Menampilkan halaman kelola 2.809 data usaha pariwisata
    Route::get('/usaha', [AdminUsahaController::class, 'index'])->name('usaha.index');
});
