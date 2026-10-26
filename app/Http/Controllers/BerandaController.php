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

        // 4. Tren Pertumbuhan Izin Usaha Pariwisata Tahunan (Grafik Garis / Area Chart)
        $trenTahunan = DB::table('usaha_pariwisata')
            ->where('status_spasial', 'verified')
            ->selectRaw("RIGHT(TRIM(tanggal_pengajuan), 4) as tahun, count(*) as total")
            ->whereNotNull('tanggal_pengajuan')
            ->where('tanggal_pengajuan', '!=', '')
            ->groupBy('tahun')
            ->orderBy('tahun', 'asc')
            ->get();

        $chartTahunLabels = $trenTahunan->pluck('tahun')->toArray();
        $chartTahunData = $trenTahunan->pluck('total')->toArray();

        // 5. Data Awal Tabel Ringkas Tempat Usaha (Paginasi 6 Baris)
        $tabelUsaha = UsahaPariwisata::verified()->buka()
            ->with(['kbli', 'kecamatan'])
            ->orderBy('nama_proyek', 'asc')
            ->paginate(6);

        $daftarKecamatan = Kecamatan::orderBy('nama_kecamatan', 'asc')->get();

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
            'chartSektor',
            'chartTahunLabels',
            'chartTahunData',
            'tabelUsaha',
            'daftarKecamatan'
        ));
    }

    /**
     * Endpoint API pencarian cepat tempat usaha di Beranda secara dinamis dengan paginasi
     */
    public function cariUsaha(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = UsahaPariwisata::verified()->buka()->with(['kbli', 'kecamatan']);

        // Filter kata kunci pencarian (nama proyek / nama perusahaan / alamat)
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_proyek', 'like', "%{$q}%")
                    ->orWhere('nama_perusahaan', 'like', "%{$q}%")
                    ->orWhere('alamat', 'like', "%{$q}%");
            });
        }

        // Filter kecamatan berdasarkan ID
        if ($request->filled('id_kecamatan')) {
            $query->where('id_kecamatan', $request->input('id_kecamatan'));
        }

        $perPage = 6;
        $total = $query->count();
        $currentPage = max(1, (int) $request->input('page', 1));
        $lastPage = max(1, (int) ceil($total / $perPage));
        if ($currentPage > $lastPage) {
            $currentPage = $lastPage;
        }

        $hasil = $query->orderBy('nama_proyek', 'asc')
            ->skip(($currentPage - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($u, $idx) use ($currentPage, $perPage) {
                return [
                    'no' => (($currentPage - 1) * $perPage) + $idx + 1,
                    'id_usaha' => $u->id_usaha,
                    'nama_usaha' => $u->nama_usaha,
                    'nama_perusahaan' => $u->nama_perusahaan ?: $u->nama_proyek,
                    'kbli' => $u->kbli?->judul_kbli ?? 'Pariwisata',
                    'alamat' => $u->alamat ?: ('Kecamatan ' . ($u->kecamatan?->nama_kecamatan ?? 'Palembang')),
                    'kecamatan' => $u->kecamatan?->nama_kecamatan ?? '-',
                    'status_operasional' => $u->status_operasional,
                ];
            });

        $from = $total > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $to = min($currentPage * $perPage, $total);

        return response()->json([
            'success' => true,
            'data' => $hasil,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
