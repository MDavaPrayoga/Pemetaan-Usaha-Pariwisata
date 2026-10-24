<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PetaController;

// Rute utama: memanggil method index untuk menampilkan halaman peta webgis
Route::get('/', [PetaController::class, 'index'])->name('peta.index');

// Rute API: mengambil data 1.421 titik koordinat usaha berstatus verified dalam format JSON
Route::get('/api/titik-usaha', [PetaController::class, 'dataTitik'])->name('api.titik');

// Rute API: menerima parameter eps dan minPts dari slider lalu menjalankan kalkulasi DBSCAN
Route::post('/api/simulasi-dbscan', [PetaController::class, 'simulasiKlaster'])->name('api.simulasi');
