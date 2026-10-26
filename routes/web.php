<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUsahaController;

// Halaman 1 Publik: Beranda & Statistik Pariwisata
Route::get('/', [BerandaController::class, 'index'])->name('beranda');

// Halaman 2 Publik: Peta Sebaran Usaha
Route::get('/peta', [PetaController::class, 'sebaran'])->name('peta.sebaran');

// Halaman 3 Publik: Pusat Usaha Wisata (Simulasi Zonasi Spasial)
Route::get('/zonasi', [PetaController::class, 'zonasi'])->name('peta.zonasi');

// Alias kompatibilitas rute peta lama
Route::get('/peta-legacy', [PetaController::class, 'index'])->name('peta.index');

// Rute API: mengambil data 1.421 titik koordinat usaha berstatus verified dalam format JSON
Route::get('/api/titik-usaha', [PetaController::class, 'dataTitik'])->name('api.titik');

// Rute API: menerima parameter eps dan minPts dari slider lalu menjalankan kalkulasi DBSCAN
Route::post('/api/simulasi-dbscan', [PetaController::class, 'simulasiKlaster'])->name('api.simulasi');

// Rute API: pencarian cepat tempat usaha di halaman beranda
Route::get('/api/pencarian-beranda', [BerandaController::class, 'cariUsaha'])->name('api.beranda.cari');

// Rute autentikasi administrator
Route::get('/login', [AuthController::class, 'tampilkanFormLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'prosesLogin'])->name('login.proses')->middleware('guest');
Route::post('/logout', [AuthController::class, 'prosesLogout'])->name('logout')->middleware('auth');

// Rute panel administrator: hanya bisa diakses oleh admin yang sudah login
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Menampilkan halaman kelola 2.809 data usaha pariwisata
    Route::get('/usaha', [AdminUsahaController::class, 'index'])->name('usaha.index');
    
    // Kunci parameter konfigurasi sentra resmi dinas ke database
    Route::post('/kunci-konfigurasi', [PetaController::class, 'kunciKonfigurasi'])->name('kunci-konfigurasi');
});

