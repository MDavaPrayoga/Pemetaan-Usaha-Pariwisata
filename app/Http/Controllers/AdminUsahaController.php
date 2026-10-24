<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsahaPariwisata;
use App\Models\Kbli;
use App\Models\Kecamatan;
use Illuminate\View\View;

class AdminUsahaController extends Controller
{
    // Menampilkan tabel kelola 2.809 data usaha pariwisata dengan filter dan pencarian
    public function index(Request $request): View
    {
        // 1. Menyiapkan query dasar dengan memuat relasi tabel KBLI dan Kecamatan
        $query = UsahaPariwisata::with(['kbli', 'kecamatan']);

        // 2. Filter pencarian berdasarkan nama usaha atau alamat
        if ($request->filled('cari')) {
            $kataKunci = $request->input('cari');
            $query->where(function ($q) use ($kataKunci) {
                $q->where('nama_usaha', 'like', "%{$kataKunci}%")
                  ->orWhere('alamat', 'like', "%{$kataKunci}%");
            });
        }

        // 3. Filter status spasial (verified / unverified)
        if ($request->filled('status_spasial') && in_array($request->status_spasial, ['verified', 'unverified'])) {
            $query->where('status_spasial', $request->status_spasial);
        }

        // 4. Filter berdasarkan kategori KBLI
        if ($request->filled('id_kbli')) {
            $query->where('id_kbli', $request->id_kbli);
        }

        // 5. Filter berdasarkan wilayah kecamatan
        if ($request->filled('id_kecamatan')) {
            $query->where('id_kecamatan', $request->id_kecamatan);
        }

        // 6. Mengambil data dengan paginasi 20 baris per halaman dan mempertahankan parameter URL
        $daftarUsaha = $query->orderBy('id_usaha', 'asc')->paginate(20)->withQueryString();

        // 7. Menghitung ringkasan statistik untuk kartu dashboard atas
        $totalSemua = UsahaPariwisata::count();
        $totalVerified = UsahaPariwisata::verified()->count();
        $totalUnverified = UsahaPariwisata::unverified()->count();

        // 8. Mengambil master data untuk dropdown filter
        $daftarKbli = Kbli::orderBy('judul_kbli', 'asc')->get();
        $daftarKecamatan = Kecamatan::orderBy('nama_kecamatan', 'asc')->get();

        // 9. Mengembalikan tampilan view admin usaha
        return view('admin.usaha.index', compact(
            'daftarUsaha',
            'totalSemua',
            'totalVerified',
            'totalUnverified',
            'daftarKbli',
            'daftarKecamatan'
        ));
    }
}
