<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsahaPariwisata;
use App\Models\Kbli;
use App\Models\Kecamatan;
use App\Models\KonfigurasiKlaster;
use App\Models\AnggotaKlaster;
use App\Services\DbscanService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PetaController extends Controller
{
    // Menyimpan instance DbscanService
    protected DbscanService $dbscanService;

    // Dependency injection DbscanService
    public function __construct(DbscanService $dbscanService)
    {
        $this->dbscanService = $dbscanService;
    }

    /**
     * Halaman 2 Publik: Peta Sebaran Tempat Usaha Pariwisata Kota Palembang.
     * Menyajikan eksplorasi 1.421 titik usaha OSS-RBA dan peta tematik kepadatan 18 kecamatan.
     */
    public function sebaran(Request $request): View
    {
        $daftarKbli = Kbli::orderBy('judul_kbli', 'asc')->get();
        $daftarKecamatan = Kecamatan::withCount(['usahaPariwisata as total_verified' => function ($q) {
            $q->verified();
        }])->orderBy('nama_kecamatan', 'asc')->get();

        $totalUsahaVerified = UsahaPariwisata::verified()->count();
        $totalUsahaBuka = UsahaPariwisata::verified()->buka()->count();
        $totalUsahaTutupPermanen = UsahaPariwisata::verified()->tutupPermanen()->count();
        $totalUsahaTutupSementara = UsahaPariwisata::verified()->tutupSementara()->count();
        $totalUsahaTutup = $totalUsahaTutupPermanen + $totalUsahaTutupSementara;

        // Hitung statistik lengkap per kecamatan untuk peta tematik choropleth
        $statistikKecamatan = Kecamatan::with(['usahaPariwisata' => function($q) {
            $q->verified()->with('kbli');
        }])->get()->map(function ($kec) use ($totalUsahaVerified) {
            $verified = $kec->usahaPariwisata;
            $total = $verified->count();
            $buka = $verified->where('status_operasional', 'Buka')->count();
            $tutupPermanen = $verified->where('status_operasional', 'Tutup Permanen')->count();
            $tutupSementara = $verified->where('status_operasional', 'Tutup Sementara')->count();
            
            // Komposisi sektor usaha
            $sektorCounts = [];
            foreach ($verified as $item) {
                $judulKbli = $item->kbli->judul_kbli ?? 'Lainnya';
                $sektorCounts[$judulKbli] = ($sektorCounts[$judulKbli] ?? 0) + 1;
            }
            arsort($sektorCounts);

            return [
                'id_kecamatan' => $kec->id_kecamatan,
                'nama_kecamatan' => $kec->nama_kecamatan,
                'total' => $total,
                'persentase' => $totalUsahaVerified > 0 ? round(($total / $totalUsahaVerified) * 100, 1) : 0,
                'buka' => $buka,
                'tutup_permanen' => $tutupPermanen,
                'tutup_sementara' => $tutupSementara,
                'tutup' => $tutupPermanen + $tutupSementara,
                'sektor' => $sektorCounts,
            ];
        })->keyBy('id_kecamatan');

        // Parameter pencarian awal dari URL
        $initialQuery = (string) ($request->input('q') ?: $request->input('cari', ''));
        $initialKecamatan = (string) $request->input('kecamatan', '');
        $initialKbli = (string) $request->input('kbli', '');
        $initialStatus = (string) $request->input('status', 'all');

        return view('peta.sebaran', compact(
            'daftarKbli',
            'daftarKecamatan',
            'totalUsahaVerified',
            'totalUsahaBuka',
            'totalUsahaTutupPermanen',
            'totalUsahaTutupSementara',
            'totalUsahaTutup',
            'statistikKecamatan',
            'initialQuery',
            'initialKecamatan',
            'initialKbli',
            'initialStatus'
        ));
    }

    /**
     * Halaman 3 Publik: Pusat Usaha Wisata (Simulasi Zonasi Spasial DBSCAN).
     */
    public function zonasi(): View
    {
        $daftarKbli = Kbli::orderBy('judul_kbli', 'asc')->get();
        $daftarKecamatan = Kecamatan::orderBy('nama_kecamatan', 'asc')->get();

        $konfigurasiAktif = KonfigurasiKlaster::where('is_active', true)
            ->with(['anggotaKlaster.usahaPariwisata.kbli', 'anggotaKlaster.usahaPariwisata.kecamatan'])
            ->first();

        $totalUsahaVerified = UsahaPariwisata::verified()->count();
        $totalUsahaBuka = UsahaPariwisata::verified()->buka()->count();

        return view('peta', compact(
            'daftarKbli',
            'daftarKecamatan',
            'konfigurasiAktif',
            'totalUsahaVerified',
            'totalUsahaBuka'
        ));
    }

    /**
     * Alias kompatibilitas rute index.
     */
    public function index(Request $request): View
    {
        return $this->sebaran($request);
    }

    /**
     * Endpoint API untuk mengambil titik-titik usaha terverifikasi geospasial
     * Diperkaya dengan seluruh atribut OSS dan Status Operasional Faktual.
     */
    public function dataTitik(Request $request): JsonResponse
    {
        $query = UsahaPariwisata::verified()->with(['kbli', 'kecamatan']);

        // Filter status operasional: 'buka', 'tutup_permanen', 'tutup_sementara', atau 'all'
        $statusOp = $request->input('status_operasional', 'all');
        if ($statusOp === 'buka') {
            $query->buka();
        } elseif ($statusOp === 'tutup_permanen') {
            $query->tutupPermanen();
        } elseif ($statusOp === 'tutup_sementara') {
            $query->tutupSementara();
        }

        // Filter KBLI
        if ($request->filled('id_kbli')) {
            $query->where('id_kbli', $request->id_kbli);
        }

        // Filter Kecamatan
        if ($request->filled('id_kecamatan')) {
            $query->where('id_kecamatan', $request->id_kecamatan);
        }

        // Filter Pencarian Teks
        $keyword = $request->input('cari') ?: $request->input('q');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_proyek', 'LIKE', "%{$keyword}%")
                  ->orWhere('nama_perusahaan', 'LIKE', "%{$keyword}%")
                  ->orWhere('nib', 'LIKE', "%{$keyword}%")
                  ->orWhere('alamat', 'LIKE', "%{$keyword}%");
            });
        }

        $titik = $query->get()->map(function ($item) {
            return [
                'id_usaha' => $item->id_usaha,
                'nib' => $item->nib,
                'nama_perusahaan' => $item->nama_perusahaan,
                'nama_proyek' => $item->nama_proyek,
                'nama_usaha' => $item->nama_usaha,
                'skala_usaha' => $item->skala_usaha ?: 'Tidak Tercatat',
                'alamat' => $item->alamat ?: '-',
                'kelurahan' => $item->kelurahan ?: '-',
                'latitude' => (float) $item->latitude,
                'longitude' => (float) $item->longitude,
                'id_kbli' => $item->id_kbli,
                'kode_kbli' => $item->kbli->kode_kbli ?? '-',
                'judul_kbli' => $item->kbli->judul_kbli ?? '-',
                'id_kecamatan' => $item->id_kecamatan,
                'nama_kecamatan' => $item->kecamatan->nama_kecamatan ?? '-',
                'status_operasional' => $item->status_operasional ?? 'Buka',
                'status_spasial' => $item->status_spasial,
            ];
        });

        return response()->json([
            'status' => 'success',
            'total' => $titik->count(),
            'data' => $titik,
        ]);
    }

    /**
     * Endpoint API simulasi komputasi DBSCAN secara real-time.
     * Menerima parameter dalam satuan meter (atau km), cakupan status operasional,
     * serta mengembalikan daftar klaster, poligon Convex Hull, dan Silhouette Score.
     */
    public function simulasiKlaster(Request $request): JsonResponse
    {
        $request->validate([
            'eps' => 'nullable|numeric|min:10|max:10000',
            'eps_km' => 'nullable|numeric|min:0.01|max:10.0',
            'min_pts' => 'required|integer|min:2|max:50',
            'cakupan_data' => 'nullable|string|in:buka,semua,buka_saja,semua_data',
            'id_kbli' => 'nullable|integer',
            'id_kecamatan' => 'nullable|integer',
        ]);

        // Standarisasi parameter Epsilon: prioritaskan input meter
        if ($request->filled('eps')) {
            $epsMeter = (float) $request->input('eps');
            $epsKm = $epsMeter / 1000.0;
        } elseif ($request->filled('eps_km')) {
            $epsKm = (float) $request->input('eps_km');
            $epsMeter = round($epsKm * 1000.0, 1);
        } else {
            $epsMeter = 300.0; // Default realistis Palembang: 300 meter
            $epsKm = 0.3;
        }

        $minPts = (int) $request->input('min_pts', 4);

        // Pengaturan cakupan basis data
        $cakupan = $request->input('cakupan_data', 'buka');
        $query = UsahaPariwisata::verified()->with(['kbli', 'kecamatan']);

        if (in_array($cakupan, ['buka', 'buka_saja'])) {
            $query->buka(); // Default: hanya mengelompokkan 1.281 usaha yang aktif beroperasi
            $cakupanLabel = 'buka_saja';
        } else {
            $cakupanLabel = 'semua_data';
        }

        if ($request->filled('id_kbli')) {
            $query->where('id_kbli', $request->id_kbli);
        }

        if ($request->filled('id_kecamatan')) {
            $query->where('id_kecamatan', $request->id_kecamatan);
        }

        $dataset = $query->get()->map(function ($item) {
            return [
                'id_usaha' => $item->id_usaha,
                'nib' => $item->nib,
                'nama_perusahaan' => $item->nama_perusahaan,
                'nama_proyek' => $item->nama_proyek,
                'nama_usaha' => $item->nama_usaha,
                'skala_usaha' => $item->skala_usaha,
                'alamat' => $item->alamat,
                'kelurahan' => $item->kelurahan,
                'latitude' => (float) $item->latitude,
                'longitude' => (float) $item->longitude,
                'kode_kbli' => $item->kbli->kode_kbli ?? '-',
                'judul_kbli' => $item->kbli->judul_kbli ?? '-',
                'nama_kecamatan' => $item->kecamatan->nama_kecamatan ?? '-',
                'status_operasional' => $item->status_operasional ?? 'Buka',
            ];
        })->toArray();

        // Eksekusi engine DBSCAN
        $hasil = $this->dbscanService->run($dataset, $epsKm, $minPts, $epsMeter);
        $hasil['cakupan_data'] = $cakupanLabel;

        return response()->json($hasil);
    }

    /**
     * Wewenang Khusus Administrator: Mengunci konfigurasi parameter klaster optimal ke basis data
     * agar menjadi standar acuan resmi kawasan pariwisata Kota Palembang.
     */
    public function kunciKonfigurasi(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Wajib login sebagai administrator.',
            ], 403);
        }

        $request->validate([
            'parameter_eps' => 'required|numeric|min:10|max:10000',
            'parameter_minpts' => 'required|integer|min:2|max:50',
            'cakupan_data' => 'nullable|string|in:buka_saja,semua_data,buka,semua',
            'nama_konfigurasi' => 'nullable|string|max:150',
        ]);

        $epsMeter = (float) $request->parameter_eps;
        $epsKm = $epsMeter / 1000.0;
        $minPts = (int) $request->parameter_minpts;
        $cakupan = in_array($request->cakupan_data, ['semua', 'semua_data']) ? 'semua_data' : 'buka_saja';

        // Ambil dataset yang sesuai
        $query = UsahaPariwisata::verified()->with(['kbli', 'kecamatan']);
        if ($cakupan === 'buka_saja') {
            $query->buka();
        }

        $dataset = $query->get()->map(function ($item) {
            return [
                'id_usaha' => $item->id_usaha,
                'nama_usaha' => $item->nama_usaha,
                'latitude' => (float) $item->latitude,
                'longitude' => (float) $item->longitude,
                'judul_kbli' => $item->kbli->judul_kbli ?? '-',
            ];
        })->toArray();

        // Jalankan kalkulasi resmi
        $hasil = $this->dbscanService->run($dataset, $epsKm, $minPts, $epsMeter);

        $userId = Auth::id() ?? 1;
        $namaKonfig = $request->nama_konfigurasi ?: ('Zonasi Resmi Dinas Pariwisata (Eps: ' . (int)$epsMeter . 'm, MinPts: ' . $minPts . ')');

        DB::beginTransaction();
        try {
            // Nonaktifkan konfigurasi aktif sebelumnya
            KonfigurasiKlaster::where('is_active', true)->update(['is_active' => false]);

            // Simpan konfigurasi baru
            $konfigurasi = KonfigurasiKlaster::create([
                'id_user' => $userId,
                'nama_konfigurasi' => $namaKonfig,
                'parameter_eps' => $epsMeter,
                'parameter_minpts' => $minPts,
                'cakupan_data' => $cakupan,
                'total_cluster' => $hasil['total_clusters'],
                'total_noise' => $hasil['total_noise'],
                'silhouette_score' => $hasil['silhouette_score'],
                'execution_time_ms' => $hasil['execution_time_ms'],
                'is_active' => true,
            ]);

            // Simpan keanggotaan klaster secara bulk
            $batchAnggota = [];
            foreach ($hasil['points'] as $p) {
                $batchAnggota[] = [
                    'id_konfigurasi' => $konfigurasi->id_konfigurasi,
                    'id_usaha' => $p['id_usaha'],
                    'cluster_label' => $p['cluster_id'],
                    'tipe_titik' => in_array($p['tipe_titik'], ['core', 'border', 'noise']) ? $p['tipe_titik'] : 'noise',
                ];
            }

            foreach (array_chunk($batchAnggota, 500) as $chunk) {
                AnggotaKlaster::insert($chunk);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi sentra resmi dinas berhasil dikunci dan diaktifkan.',
                'data' => [
                    'id_konfigurasi' => $konfigurasi->id_konfigurasi,
                    'nama_konfigurasi' => $konfigurasi->nama_konfigurasi,
                    'parameter_eps' => $epsMeter,
                    'parameter_minpts' => $minPts,
                    'total_cluster' => $hasil['total_clusters'],
                    'total_noise' => $hasil['total_noise'],
                    'silhouette_score' => $hasil['silhouette_score'],
                    'silhouette_label' => $hasil['silhouette_label'],
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunci konfigurasi klaster: ' . $e->getMessage(),
            ], 500);
        }
    }
}
