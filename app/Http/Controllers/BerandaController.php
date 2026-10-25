<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsahaPariwisata;
use App\Models\Kecamatan;
use App\Models\Kbli;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class BerandaController extends Controller
{
    /**
     * Menampilkan halaman beranda publik dengan ringkasan statistik dan grafik interaktif.
     */
    public function index(): View
    {
        // 1. Metrik Kunci Geospasial
        $totalVerified = UsahaPariwisata::verified()->count();
        $totalBuka = UsahaPariwisata::verified()->buka()->count();
        $totalTutupPermanen = UsahaPariwisata::verified()->tutupPermanen()->count();
        $totalTutupSementara = UsahaPariwisata::verified()->tutupSementara()->count();
        $totalTutup = $totalTutupPermanen + $totalTutupSementara;

        // Persentase kelangsungan usaha (survival rate)
        $persenBuka = $totalVerified > 0 ? round(($totalBuka / $totalVerified) * 100, 1) : 0;

        // 2. Data Sebaran 18 Kecamatan Kota Palembang (untuk Grafik Batang)
        $kecamatanList = Kecamatan::withCount(['usahaPariwisata as total_usaha' => function ($query) {
            $query->verified();
        }])
        ->orderBy('total_usaha', 'desc')
        ->get();

        $kecamatanTerpadat = $kecamatanList->first();

        $chartKecamatanLabels = $kecamatanList->pluck('nama_kecamatan')->toArray();
        $chartKecamatanData = $kecamatanList->pluck('total_usaha')->toArray();

        // 3. Komposisi Sektor Usaha Pariwisata (untuk Grafik Donat)
        // Dikelompokkan ke dalam kategori strategis pariwisata Palembang
        $sektorKuliner = UsahaPariwisata::verified()
            ->whereHas('kbli', function ($q) {
                $q->where('judul_kbli', 'like', '%Restoran%')
                  ->orWhere('judul_kbli', 'like', '%Kafe%')
                  ->orWhere('judul_kbli', 'like', '%Rumah Minum%')
                  ->orWhere('judul_kbli', 'like', '%Kedai%')
                  ->orWhere('judul_kbli', 'like', '%Makanan%')
                  ->orWhere('judul_kbli', 'like', '%Jasaboga%')
                  ->orWhere('judul_kbli', 'like', '%Bar%');
            })->count();

        $sektorPerjalanan = UsahaPariwisata::verified()
            ->whereHas('kbli', function ($q) {
                $q->where('judul_kbli', 'like', '%Perjalanan%')
                  ->orWhere('judul_kbli', 'like', '%Wisata%')
                  ->orWhere('judul_kbli', 'like', '%Umroh%');
            })->count();

        $sektorAkomodasi = UsahaPariwisata::verified()
            ->whereHas('kbli', function ($q) {
                $q->where('judul_kbli', 'like', '%Hotel%')
                  ->orWhere('judul_kbli', 'like', '%Penginapan%')
                  ->orWhere('judul_kbli', 'like', '%Wisma%')
                  ->orWhere('judul_kbli', 'like', '%Losmen%')
                  ->orWhere('judul_kbli', 'like', '%Motel%');
            })->count();

        $sektorLainnya = max(0, $totalVerified - ($sektorKuliner + $sektorPerjalanan + $sektorAkomodasi));

        $chartSektor = [
            'labels' => ['Kuliner (Makanan & Minuman)', 'Biro & Jasa Perjalanan Wisata', 'Hotel & Akomodasi', 'Hiburan, Rekreasi & Lainnya'],
            'data' => [$sektorKuliner, $sektorPerjalanan, $sektorAkomodasi, $sektorLainnya],
        ];

        return view('beranda', compact(
            'totalVerified',
            'totalBuka',
            'totalTutup',
            'totalTutupPermanen',
            'totalTutupSementara',
            'persenBuka',
            'kecamatanTerpadat',
            'chartKecamatanLabels',
            'chartKecamatanData',
            'chartSektor'
        ));
    }
}
