@extends('layouts.app')

@section('title', 'Kelola Data Usaha Pariwisata - Panel Admin')

@section('content')
<!-- Header Panel Administrator dengan identitas warna Maroon Ampera -->
<header class="bg-[#8B1E2D] text-white shadow-md z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-white/10 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-bold leading-tight">Panel Administrator WebGIS</h1>
                <p class="text-xs text-white/80">Kelola Data Perizinan & Pengayaan Titik Spasial Usaha Pariwisata</p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Tautan kembali ke Peta Publik -->
            <a href="{{ route('peta.index') }}" target="_blank" class="hidden sm:inline-flex items-center space-x-1.5 bg-white/10 hover:bg-white/20 border border-white/20 px-3 py-1.5 rounded-md text-xs font-medium transition duration-150">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Lihat Peta Publik</span>
            </a>

            <!-- Tombol Logout -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center space-x-1.5 bg-red-950/40 hover:bg-red-950/60 border border-white/20 px-3 py-1.5 rounded-md text-xs font-medium transition duration-150">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</header>

<!-- Area Konten Utama Dashboard Admin -->
<div class="flex-1 bg-slate-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Baris 1: Tiga Kartu Ringkasan Status Spasial -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- Kartu Total Keseluruhan Usaha -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Data Usaha</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1 font-mono">{{ number_format($totalSemua, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Sumber: DPMPTSP Palembang</p>
                </div>
                <div class="p-3 bg-slate-100 rounded-lg text-slate-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>

            <!-- Kartu Usaha Terverifikasi Koordinatnya -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Koordinat Terverifikasi</p>
                    <p class="text-2xl font-bold text-emerald-700 mt-1 font-mono">{{ number_format($totalVerified, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-emerald-600/80 mt-0.5">Aktif tampil di peta publik</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Kartu Usaha Belum Terverifikasi (Perlu Dilengkapi) -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Belum Ada Titik Koordinat</p>
                    <p class="text-2xl font-bold text-amber-700 mt-1 font-mono">{{ number_format($totalUnverified, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-amber-600/80 mt-0.5">Memerlukan pengkayaan admin</p>
                </div>
                <div class="p-3 bg-amber-50 rounded-lg text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>

        </div>

        <!-- Baris 2: Panel Filter dan Pencarian Cepat -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <form action="{{ route('admin.usaha.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                
                <!-- Input Pencarian Nama Usaha -->
                <div class="lg:col-span-2">
                    <label for="cari" class="block text-xs font-semibold text-slate-600 mb-1">Cari Nama Usaha / Alamat</label>
                    <input 
                        type="text" 
                        id="cari" 
                        name="cari" 
                        value="{{ request('cari') }}" 
                        placeholder="Contoh: Hotel Duta, Rumah Makan, Kafe..." 
                        class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-[#8B1E2D] focus:ring-1 focus:ring-[#8B1E2D]"
                    >
                </div>

                <!-- Filter Status Spasial -->
                <div>
                    <label for="status_spasial" class="block text-xs font-semibold text-slate-600 mb-1">Status Koordinat</label>
                    <select id="status_spasial" name="status_spasial" class="w-full text-xs rounded-lg border border-slate-300 px-2.5 py-2 outline-none focus:border-[#8B1E2D] focus:ring-1 focus:ring-[#8B1E2D]">
                        <option value="">Semua Status</option>
                        <option value="verified" {{ request('status_spasial') === 'verified' ? 'selected' : '' }}>Terverifikasi (Ada Titik)</option>
                        <option value="unverified" {{ request('status_spasial') === 'unverified' ? 'selected' : '' }}>Belum Terverifikasi (Kosong)</option>
                    </select>
                </div>

                <!-- Filter Kategori KBLI -->
                <div>
                    <label for="id_kbli" class="block text-xs font-semibold text-slate-600 mb-1">Kategori KBLI</label>
                    <select id="id_kbli" name="id_kbli" class="w-full text-xs rounded-lg border border-slate-300 px-2.5 py-2 outline-none focus:border-[#8B1E2D] focus:ring-1 focus:ring-[#8B1E2D]">
                        <option value="">Semua Kategori</option>
                        @foreach($daftarKbli as $k)
                            <option value="{{ $k->id_kbli }}" {{ request('id_kbli') == $k->id_kbli ? 'selected' : '' }}>{{ $k->judul_kbli }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Kecamatan & Tombol Aksi -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 bg-[#8B1E2D] hover:bg-[#741D29] text-white text-xs font-semibold py-2 px-3 rounded-lg transition duration-150">
                        Filter
                    </button>
                    <a href="{{ route('admin.usaha.index') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-medium py-2 px-3 rounded-lg transition duration-150">
                        Reset
                    </a>
                </div>

            </form>
        </div>

        <!-- Baris 3: Tabel Data Usaha Pariwisata -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Daftar Usaha Pariwisata</h2>
                <span class="text-xs text-slate-500">Menampilkan {{ $daftarUsaha->firstItem() ?? 0 }} - {{ $daftarUsaha->lastItem() ?? 0 }} dari {{ number_format($daftarUsaha->total(), 0, ',', '.') }} data</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold">
                            <th class="py-3 px-3 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama Usaha</th>
                            <th class="py-3 px-3">Kategori KBLI</th>
                            <th class="py-3 px-3">Kecamatan</th>
                            <th class="py-3 px-4">Alamat</th>
                            <th class="py-3 px-3">Koordinat (Lat, Lng)</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($daftarUsaha as $usaha)
                            <tr class="hover:bg-slate-50/80 transition duration-100">
                                <td class="py-3 px-3 text-center font-mono text-slate-400">
                                    {{ ($daftarUsaha->currentPage() - 1) * $daftarUsaha->perPage() + $loop->iteration }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-900">
                                    {{ $usaha->nama_usaha }}
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    <span class="inline-block px-2 py-0.5 bg-slate-100 rounded text-[11px] font-medium">
                                        {{ $usaha->kbli->judul_kbli ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $usaha->kecamatan->nama_kecamatan ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 max-w-xs truncate" title="{{ $usaha->alamat }}">
                                    {{ $usaha->alamat ?: '-' }}
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                    @if($usaha->latitude && $usaha->longitude)
                                        {{ number_format($usaha->latitude, 6) }}, {{ number_format($usaha->longitude, 6) }}
                                    @else
                                        <span class="text-slate-400 italic">Belum ada titik</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($usaha->status_spasial === 'verified')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                            Terverifikasi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">
                                            Belum Terverifikasi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    @if($usaha->status_spasial === 'unverified')
                                        <button type="button" class="inline-flex items-center space-x-1 px-2.5 py-1 bg-[#8B1E2D] hover:bg-[#741D29] text-white text-[11px] font-medium rounded transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>Lengkapi Titik</span>
                                        </button>
                                    @else
                                        <button type="button" class="inline-flex items-center space-x-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            <span>Ubah Titik</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    Tidak ada data usaha yang sesuai dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tautan Paginasi Halaman -->
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $daftarUsaha->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
