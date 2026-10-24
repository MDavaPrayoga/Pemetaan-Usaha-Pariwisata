<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsahaPariwisata;
use App\Models\Kbli;
use App\Models\Kecamatan;
use App\Models\KonfigurasiKlaster;
use App\Services\DbscanService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PetaController extends Controller
{
    // Menyimpan instance DbscanService agar bisa dipanggil di semua method controller ini
    protected DbscanService $dbscanService;

    // Dependency injection: memasukkan DbscanService secara otomatis saat controller dibuat
    public function __construct(DbscanService $dbscanService)
    {
        $this->dbscanService = $dbscanService;
    }

    // Method untuk menampilkan halaman utama peta publik
    public function index(): View
    {
        // Mengambil semua kategori KBLI dan diurutkan abjad untuk pilihan filter
        $daftarKbli = Kbli::orderBy('judul_kbli', 'asc')->get();

        // Mengambil seluruh 18 nama kecamatan untuk pilihan filter wilayah
        $daftarKecamatan = Kecamatan::orderBy('nama_kecamatan', 'asc')->get();

        // Mencari konfigurasi klaster resmi yang statusnya aktif untuk ditampilkan di awal
        $konfigurasiAktif = KonfigurasiKlaster::where('is_active', true)
            ->with(['anggotaKlaster.usahaPariwisata.kbli', 'anggotaKlaster.usahaPariwisata.kecamatan'])
            ->first();

        // Menghitung total data usaha yang sudah terverifikasi koordinatnya (1.421 titik)
        $totalUsahaVerified = UsahaPariwisata::verified()->count();

        // Mengirim data yang sudah diambil ke file view peta.blade.php
        return view('peta', compact(
            'daftarKbli',
            'daftarKecamatan',
            'konfigurasiAktif',
            'totalUsahaVerified'
        ));
    }

    // Method API untuk menyuplai titik usaha dalam format JSON ke peta Leaflet
    public function dataTitik(Request $request): JsonResponse
    {
        // Menyiapkan query untuk mengambil usaha yang koordinatnya valid beserta relasi tabelnya
        $query = UsahaPariwisata::verified()->with(['kbli', 'kecamatan']);

        // Jika user memilih filter kategori KBLI tertentu, saring datanya
        if ($request->filled('id_kbli')) {
            $query->where('id_kbli', $request->id_kbli);
        }

        // Jika user memilih filter kecamatan tertentu, saring datanya
        if ($request->filled('id_kecamatan')) {
            $query->where('id_kecamatan', $request->id_kecamatan);
        }

        // Mengubah hasil query database menjadi susunan array bersih untuk marker peta
        $titik = $query->get()->map(function ($item) {
            return [
                'id_usaha' => $item->id_usaha,
                'nama_usaha' => $item->nama_usaha,
                'alamat' => $item->alamat,
                'latitude' => $item->latitude,
                'longitude' => $item->longitude,
                'id_kbli' => $item->id_kbli,
                'kode_kbli' => $item->kbli->kode_kbli ?? '-',
                'judul_kbli' => $item->kbli->judul_kbli ?? '-',
                'id_kecamatan' => $item->id_kecamatan,
                'nama_kecamatan' => $item->kecamatan->nama_kecamatan ?? '-',
            ];
        });

        // Mengembalikan data dalam bentuk respons JSON standar
        return response()->json([
            'status' => 'success',
            'total' => $titik->count(),
            'data' => $titik,
        ]);
    }

    // Method API untuk memproses simulasi kalkulasi DBSCAN saat user menggeser slider
    public function simulasiKlaster(Request $request): JsonResponse
    {
        // Memvalidasi input agar nilai eps dan minPts tidak kosong dan berada pada batas aman
        $request->validate([
            'eps' => 'required|numeric|min:0.05|max:10.0',
            'min_pts' => 'required|integer|min:2|max:50',
            'id_kbli' => 'nullable|integer',
            'id_kecamatan' => 'nullable|integer',
        ]);

        // Mengambil nilai parameter eps (dalam kilometer), jika kosong gunakan default 0.5 km
        $eps = (float) $request->input('eps', 0.5);

        // Mengambil nilai parameter minimum points, jika kosong gunakan default 5 titik
        $minPts = (int) $request->input('min_pts', 5);

        // Menyiapkan query data usaha yang terverifikasi
        $query = UsahaPariwisata::verified()->with(['kbli', 'kecamatan']);

        // Menerapkan filter KBLI jika dipilih pada form simulasi
        if ($request->filled('id_kbli')) {
            $query->where('id_kbli', $request->id_kbli);
        }

        // Menerapkan filter kecamatan jika dipilih pada form simulasi
        if ($request->filled('id_kecamatan')) {
            $query->where('id_kecamatan', $request->id_kecamatan);
        }

        // Mengubah data hasil filter menjadi array murni agar siap dihitung oleh DbscanService
        $dataset = $query->get()->map(function ($item) {
            return [
                'id_usaha' => $item->id_usaha,
                'nama_usaha' => $item->nama_usaha,
                'alamat' => $item->alamat,
                'latitude' => $item->latitude,
                'longitude' => $item->longitude,
                'kode_kbli' => $item->kbli->kode_kbli ?? '-',
                'judul_kbli' => $item->kbli->judul_kbli ?? '-',
                'nama_kecamatan' => $item->kecamatan->nama_kecamatan ?? '-',
            ];
        })->toArray();

        // Menjalankan proses klasterisasi DBSCAN dengan data dan parameter yang diterima
        $hasil = $this->dbscanService->run($dataset, $eps, $minPts);

        // Mengembalikan hasil klasterisasi langsung ke antarmuka peta tanpa mengubah database
        return response()->json($hasil);
    }
}
