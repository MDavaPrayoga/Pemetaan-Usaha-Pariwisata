@extends('layouts.app')

@section('title', 'Beranda & Statistik Pariwisata — WebGIS Kota Palembang')

@section('content')
<!-- Section 1: Hero Banner Sinematik dengan Background Foto & Dark Ampera Overlay -->
<section id="hero-banner" 
         class="relative bg-cover bg-center text-white py-20 lg:py-28 overflow-hidden" 
         data-bg="{{ asset('images/mencoba.png') }}">
    
    <!-- Lapisan Gelap (Overlay) Gradasi agar foto Ampera tetap artistik dan teks putih terbaca sangat kontras -->
    <div class="absolute inset-0 bg-linear-to-r from-slate-950/95 via-slate-950/85 to-[#741D29]/45 backdrop-blur-[0.5px]"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl">
            <!-- Institutional Badge -->
            <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md border border-white/20 px-3.5 py-1.5 rounded-full text-xs font-semibold text-white/95 mb-6 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Sistem Informasi Geografis Resmi Kota Palembang</span>
            </div>

            <!-- Main Title -->
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight mb-4 text-white drop-shadow-md">
                Pemetaan & Klasterisasi Usaha Pariwisata
            </h1>

            <!-- Subtitle -->
            <p class="text-base sm:text-lg text-white/90 leading-relaxed mb-8 drop-shadow-sm font-normal">
                Eksplorasi geospasial terhadap <strong>{{ number_format($totalVerified, 0, ',', '.') }}</strong> tempat usaha pariwisata Kota Palembang berbasis perizinan OSS-RBA dan audit operasional Google Maps menggunakan kecerdasan buatan spasial <strong>Algoritma DBSCAN</strong>.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                <a href="{{ route('peta.sebaran') }}" 
                   class="inline-flex items-center space-x-2.5 bg-white text-[#8B1E2D] hover:bg-slate-100 font-bold px-6 py-3.5 rounded-xl shadow-lg hover:shadow-xl transition duration-200 text-sm">
                    <svg class="w-5 h-5 text-[#8B1E2D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Jelajahi Peta Sebaran ({{ number_format($totalVerified, 0, ',', '.') }})</span>
                </a>

                <a href="{{ route('peta.zonasi') }}" 
                   class="inline-flex items-center space-x-2.5 bg-black/40 hover:bg-black/60 border border-white/30 text-white font-semibold px-6 py-3.5 rounded-xl backdrop-blur-md transition duration-200 text-sm">
                    <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Temukan Pusat Usaha Wisata</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: 4 KPI Cards (Ringkasan Metrik Faktual) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- Card 1: Total Usaha Terverifikasi -->
        <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 flex flex-col justify-between hover:shadow-lg transition duration-200">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Terpetakan</span>
                <div class="p-2.5 rounded-xl bg-slate-100 text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ number_format($totalVerified, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Usaha pariwisata berkoordinat presisi</p>
            </div>
        </div>

        <!-- Card 2: Usaha Aktif / Buka -->
        <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 flex flex-col justify-between hover:shadow-lg transition duration-200">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Aktif Beroperasi</span>
                <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-emerald-700 tracking-tight flex items-baseline space-x-2">
                    <span>{{ number_format($totalBuka, 0, ',', '.') }}</span>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">{{ $persenBuka }}%</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Status operasional buka di Google Maps</p>
            </div>
        </div>

        <!-- Card 3: Usaha Tutup -->
        <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 flex flex-col justify-between hover:shadow-lg transition duration-200">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-600">Tutup / Non-Aktif</span>
                <div class="p-2.5 rounded-xl bg-rose-50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-rose-700 tracking-tight">
                    {{ number_format($totalTutup, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ $totalTutupPermanen }} Permanen • {{ $totalTutupSementara }} Sementara</p>
            </div>
        </div>

        <!-- Card 4: Kecamatan Terpadat -->
        <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 flex flex-col justify-between hover:shadow-lg transition duration-200">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700">Wilayah Terpadat</span>
                <div class="p-2.5 rounded-xl bg-amber-50 text-amber-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-900 tracking-tight truncate" title="{{ $kecamatanTerpadat->nama_kecamatan ?? '-' }}">
                    {{ $kecamatanTerpadat->nama_kecamatan ?? '-' }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Konsentrasi {{ number_format($kecamatanTerpadat->total_usaha ?? 0, 0, ',', '.') }} tempat usaha</p>
            </div>
        </div>

    </div>
</section>

<!-- Section 3: 3 Grafik Analitis Interaktif (Kecamatan, Sektor & Tren Tahunan) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Statistik & Analisis Data Pariwisata</h2>
        <p class="text-sm text-slate-600 mt-1">Eksplorasi kuantitatif tempat usaha pariwisata berdasarkan wilayah, bidang usaha KBLI, dan tren waktu.</p>
    </div>

    <!-- Baris 1: Tren Pertumbuhan Izin Tahunan (7 Kolom) & Komposisi Sektor Usaha (5 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
        
        <!-- Grafik 1 (Kiri Atas): Tren Pertumbuhan Izin Usaha Pariwisata Tahunan -->
        <div class="lg:col-span-7 bg-white rounded-2xl p-6 lg:p-7 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-base font-bold text-slate-900">Tren Pertumbuhan Izin Usaha Tahunan</h3>
                            <span class="text-xs bg-emerald-50 text-emerald-700 font-semibold px-2.5 py-0.5 rounded-full border border-emerald-200">2021 – 2026</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Dinamika penerbitan Nomor Induk Berusaha (NIB) sektor pariwisata pada sistem OSS-RBA.</p>
                    </div>
                    <div class="text-left sm:text-right shrink-0">
                        <span class="text-xs font-medium text-slate-500">Puncak Izin</span>
                        <div class="text-sm font-bold text-cluster-1">Tahun 2022 (420 Izin)</div>
                    </div>
                </div>

                <div class="relative h-72 w-full mt-4">
                    <canvas id="chartTahun" 
                            data-labels='{!! json_encode($chartTahunLabels) !!}' 
                            data-values='{!! json_encode($chartTahunData) !!}'></canvas>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-slate-500">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-cluster-1 shrink-0"></span>
                    <span><strong>2021:</strong> Perdana OSS-RBA</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span><strong>2022:</strong> Lonjakan izin pasca-pandemi</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                    <span><strong>2023–2026:</strong> Pertumbuhan stabil</span>
                </div>
            </div>
        </div>

        <!-- Grafik 2 (Kanan Atas): Komposisi Sektor Usaha Pariwisata -->
        <div class="lg:col-span-5 bg-white rounded-2xl p-6 lg:p-7 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-base font-bold text-slate-900">Komposisi Sektor Usaha</h3>
                    <span class="text-xs bg-ampera-50 text-cluster-1 px-2.5 py-1 rounded-md font-medium">Berdasarkan KBLI</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">Pembagian proporsi 4 kelompok bidang usaha pariwisata Kota Palembang.</p>
                
                <div class="relative h-60 w-full flex items-center justify-center">
                    <canvas id="chartSektor" 
                            data-labels='{!! json_encode($chartSektor['labels']) !!}' 
                            data-values='{!! json_encode($chartSektor['data']) !!}'></canvas>
                </div>

                <!-- Custom Sector Legend / Summary -->
                <div class="mt-4 space-y-2">
                    @php
                        $bgColors = ['bg-[#8B1E2D]', 'bg-[#1E40AF]', 'bg-[#047857]', 'bg-[#64748B]'];
                    @endphp
                    @foreach($chartSektor['labels'] as $index => $label)
                        <div class="flex items-center justify-between text-xs py-1.5 px-3 rounded-lg bg-slate-50">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $bgColors[$index] ?? 'bg-slate-500' }}"></span>
                                <span class="text-slate-700 font-medium">{{ $label }}</span>
                            </div>
                            <span class="font-bold text-slate-900">{{ number_format($chartSektor['data'][$index], 0, ',', '.') }} Usaha</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500 text-center">
                Didominasi sektor <strong>Penyediaan Makanan & Minuman (Kuliner 73,4%)</strong>
            </div>
        </div>

    </div>

    <!-- Baris 2 (Bawah): Peringkat Kepadatan 18 Kecamatan (Lebar Penuh) -->
    <div class="bg-white rounded-2xl p-6 lg:p-8 shadow-sm border border-slate-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
            <div>
                <div class="flex items-center space-x-2">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Peringkat Kepadatan Usaha di 18 Kecamatan</h3>
                    <span class="text-xs bg-slate-100 text-slate-700 font-semibold px-2.5 py-0.5 rounded-full border border-slate-200">Katalog Faktual</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Distribusi sebaran tempat usaha pariwisata yang aktif dan berizin di seluruh kecamatan Kota Palembang.</p>
            </div>
            <div class="text-left sm:text-right shrink-0">
                <span class="text-xs font-medium text-slate-500">Wilayah Terbanyak</span>
                <div class="text-sm font-bold text-cluster-1">{{ $kecamatanTerpadat->nama_kecamatan ?? '-' }} ({{ number_format($kecamatanTerpadat->total_usaha ?? 0, 0, ',', '.') }} Usaha)</div>
            </div>
        </div>
        
        <div class="relative h-110 sm:h-120 w-full mt-4">
            <canvas id="chartKecamatan" 
                    data-labels='{!! json_encode($chartKecamatanLabels) !!}' 
                    data-values='{!! json_encode($chartKecamatanData) !!}'></canvas>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
            <span>Sumber: Dinas Penanaman Modal & Pelayanan Terpadu Satu Pintu (DPMPTSP) Kota Palembang</span>
            <div class="flex items-center space-x-4">
                <span>Total: <strong>18 Kecamatan</strong></span>
                <span>Konsentrasi Terendah: <strong>Sematang Borang (2 Usaha)</strong></span>
            </div>
        </div>
    </div>
</section>

<!-- Section 4: Direktori & Eksplorasi Cepat Tempat Usaha (Tabel Ringkas dengan Paginasi) -->
<section class="bg-white border-y border-slate-200 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Direktori -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <div class="inline-flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-cluster-1 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Direktori Terpadu Publik</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Daftar Tempat Usaha Pariwisata</h2>
                <p class="text-sm text-slate-600 mt-1">Katalog ringkas tempat usaha pariwisata terverifikasi faktual di 18 kecamatan Kota Palembang.</p>
            </div>

            <!-- Filter & Search Bar Sederhana -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                <div class="relative min-w-55">
                    <input type="text" 
                           id="filter-search-input" 
                           placeholder="Cari nama usaha..." 
                           class="w-full pl-9 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-cluster-1 focus:border-cluster-1 outline-none transition duration-150">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <select id="filter-kecamatan-select" 
                        class="px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-cluster-1 focus:border-cluster-1 outline-none transition duration-150 text-slate-700">
                    <option value="">Semua Kecamatan (18 Kecamatan)</option>
                    @foreach($daftarKecamatan as $kec)
                        <option value="{{ $kec->id_kecamatan }}">{{ $kec->nama_kecamatan }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Tabel Ringkas Tempat Usaha Nyata -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider text-[11px] font-bold">
                            <th scope="col" class="py-3.5 px-4 w-12 text-center">No</th>
                            <th scope="col" class="py-3.5 px-4">Nama Tempat Usaha</th>
                            <th scope="col" class="py-3.5 px-4">Bidang Usaha (KBLI)</th>
                            <th scope="col" class="py-3.5 px-4">Kecamatan & Alamat</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Status</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-usaha-body" class="divide-y divide-slate-100">
                        @forelse($tabelUsaha as $index => $usaha)
                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                <td class="py-3.5 px-4 text-center font-semibold text-slate-400">
                                    {{ $tabelUsaha->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 leading-tight">{{ $usaha->nama_usaha }}</div>
                                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5 truncate max-w-xs">{{ $usaha->nama_perusahaan ?: $usaha->nama_proyek }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block px-2.5 py-1 bg-slate-100 text-slate-700 font-medium rounded-lg text-[11px] truncate max-w-50" title="{{ $usaha->kbli?->judul_kbli }}">
                                        {{ $usaha->kbli?->judul_kbli ?? 'Pariwisata' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800">{{ $usaha->kecamatan?->nama_kecamatan ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-sm mt-0.5">{{ $usaha->alamat }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Buka</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('peta.sebaran') }}?q={{ urlencode($usaha->nama_usaha) }}" 
                                       class="inline-flex items-center space-x-1.5 bg-cluster-1/10 hover:bg-cluster-1 text-cluster-1 hover:text-white px-3 py-1.5 rounded-lg font-bold text-xs transition duration-150">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Peta</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-500">
                                    Tidak ada data tempat usaha ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Paginasi Nomor Halaman -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Menampilkan <span id="pag-from" class="font-bold text-slate-800">{{ $tabelUsaha->firstItem() ?? 0 }}</span>–<span id="pag-to" class="font-bold text-slate-800">{{ $tabelUsaha->lastItem() ?? 0 }}</span> dari <span id="pag-total" class="font-bold text-slate-800">{{ number_format($tabelUsaha->total(), 0, ',', '.') }}</span> tempat usaha
                </div>

                <!-- Kontrol Navigasi Halaman -->
                <div id="pagination-controls" 
                     data-current="{{ $tabelUsaha->currentPage() }}" 
                     data-last="{{ $tabelUsaha->lastPage() }}" 
                     class="flex items-center space-x-1 text-xs">
                    <!-- Tombol Halaman digenerate secara dinamis oleh JavaScript -->
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Bawah: Telusuri Seluruh Usaha -->
        <div class="mt-8 text-center">
            <a href="{{ route('peta.sebaran') }}" 
               class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold px-6 py-3 rounded-xl transition duration-150 text-xs sm:text-sm">
                <span>Telusuri Seluruh {{ number_format($totalVerified, 0, ',', '.') }} Tempat Usaha di Peta Sebaran</span>
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

    </div>
</section>

<!-- Section 5: Call to Action (Ajakan Eksplorasi Peta Zonasi) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-linear-to-r from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-8 sm:p-12 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="max-w-xl space-y-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-400">Eksplorasi Spasial Cerdas</span>
            <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Siap Menemukan Pusat Usaha Wisata?</h3>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                Gunakan panel interaktif kami untuk menyimulasikan radius jarak tetangga dan melihat bagaimana algoritma DBSCAN mengelompokkan kawasan secara otomatis.
            </p>
        </div>
        <div class="shrink-0">
            <a href="{{ route('peta.zonasi') }}" 
               class="inline-flex items-center space-x-2.5 bg-cluster-1 hover:bg-ampera-900 text-white font-bold px-7 py-4 rounded-xl shadow-lg transition duration-200 text-sm">
                <span>Buka Pusat Usaha Wisata</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 0. Inisialisasi gambar latar hero banner
    const heroBanner = document.getElementById('hero-banner');
    if (heroBanner && heroBanner.dataset.bg) {
        heroBanner.style.backgroundImage = `url('${heroBanner.dataset.bg}')`;
    }

    // 1. Data untuk Grafik Batang Kepadatan Kecamatan
    const ctxKecamatan = document.getElementById('chartKecamatan');
    if (ctxKecamatan) {
        const labelsKecamatan = JSON.parse(ctxKecamatan.dataset.labels || '[]');
        const dataKecamatan = JSON.parse(ctxKecamatan.dataset.values || '[]');

        new Chart(ctxKecamatan, {
            type: 'bar',
            data: {
                labels: labelsKecamatan,
                datasets: [{
                    label: 'Jumlah Usaha Terverifikasi',
                    data: dataKecamatan,
                    backgroundColor: 'rgba(139, 30, 45, 0.85)',
                    hoverBackgroundColor: '#8B1E2D',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 8,
                        right: 16
                    }
                },
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, family: 'Plus Jakarta Sans', weight: 'bold' },
                        bodyFont: { size: 12, family: 'Plus Jakarta Sans' },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x + ' Tempat Usaha';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 11 },
                            color: '#64748b'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' },
                            color: '#334155'
                        }
                    }
                }
            }
        });
    }

    // 2. Data untuk Grafik Donat Sektor Usaha
    const ctxSektor = document.getElementById('chartSektor');
    if (ctxSektor) {
        const labelsSektor = JSON.parse(ctxSektor.dataset.labels || '[]');
        const dataSektor = JSON.parse(ctxSektor.dataset.values || '[]');

        new Chart(ctxSektor, {
            type: 'doughnut',
            data: {
                labels: labelsSektor,
                datasets: [{
                    data: dataSektor,
                    backgroundColor: [
                        '#8B1E2D', // Kuliner
                        '#1E40AF', // Jasa Perjalanan
                        '#047857', // Akomodasi/Hotel
                        '#64748B'  // Lainnya
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, family: 'Plus Jakarta Sans', weight: 'bold' },
                        bodyFont: { size: 12, family: 'Plus Jakarta Sans' },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ${value} Usaha (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // 3. Data untuk Grafik Garis Tren Pertumbuhan Tahunan
    const ctxTahun = document.getElementById('chartTahun');
    if (ctxTahun) {
        const labelsTahun = JSON.parse(ctxTahun.dataset.labels || '[]');
        const dataTahun = JSON.parse(ctxTahun.dataset.values || '[]');

        new Chart(ctxTahun, {
            type: 'line',
            data: {
                labels: labelsTahun,
                datasets: [{
                    label: 'Izin Usaha Terbit (NIB)',
                    data: dataTahun,
                    borderColor: '#8B1E2D',
                    backgroundColor: 'rgba(139, 30, 45, 0.12)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointBackgroundColor: '#8B1E2D',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, family: 'Plus Jakarta Sans', weight: 'bold' },
                        bodyFont: { size: 12, family: 'Plus Jakarta Sans' },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' Usaha Terdaftar (NIB)';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: '#f8fafc'
                        },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
                            color: '#475569'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 11 },
                            color: '#64748b'
                        }
                    }
                }
            }
        });
    }

    // 4. Interaksi Pencarian, Filter & Paginasi Tabel Ringkas Tempat Usaha
    const searchInput = document.getElementById('filter-search-input');
    const kecamatanSelect = document.getElementById('filter-kecamatan-select');
    const tableBody = document.getElementById('tabel-usaha-body');
    const pagControls = document.getElementById('pagination-controls');
    const pagFrom = document.getElementById('pag-from');
    const pagTo = document.getElementById('pag-to');
    const pagTotal = document.getElementById('pag-total');
    let debounceTimer = null;
    let stateCurrentPage = 1;

    async function loadTableData(page = 1) {
        stateCurrentPage = page;
        const query = (searchInput?.value || '').trim();
        const selectedKec = (kecamatanSelect?.value || '').trim();

        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-400">
                        <div class="inline-flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <span class="text-xs">Memuat data tempat usaha...</span>
                        </div>
                    </td>
                </tr>
            `;
        }

        try {
            const params = new URLSearchParams();
            if (query) params.append('q', query);
            if (selectedKec) params.append('id_kecamatan', selectedKec);
            params.append('page', page);

            const res = await fetch(`{{ route('api.beranda.cari') }}?${params.toString()}`);
            const json = await res.json();

            if (json.success && tableBody) {
                if (json.data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <div class="text-xs font-bold text-slate-800">Tidak ada tempat usaha ditemukan</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Coba gunakan kata kunci pencarian lain atau pilih kecamatan yang berbeda.</div>
                            </td>
                        </tr>
                    `;
                } else {
                    tableBody.innerHTML = json.data.map(u => `
                        <tr class="hover:bg-slate-50/80 transition duration-150">
                            <td class="py-3.5 px-4 text-center font-semibold text-slate-400">${u.no}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 leading-tight">${u.nama_usaha}</div>
                                <div class="text-[11px] text-slate-400 leading-tight mt-0.5 truncate max-w-xs">${u.nama_perusahaan}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-1 bg-slate-100 text-slate-700 font-medium rounded-lg text-[11px] truncate max-w-50" title="${u.kbli}">
                                    ${u.kbli}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">${u.kecamatan}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-sm mt-0.5">${u.alamat}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Buka</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('peta.sebaran') }}?q=${encodeURIComponent(u.nama_usaha)}" 
                                   class="inline-flex items-center space-x-1.5 bg-cluster-1/10 hover:bg-cluster-1 text-cluster-1 hover:text-white px-3 py-1.5 rounded-lg font-bold text-xs transition duration-150">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Peta</span>
                                </a>
                            </td>
                        </tr>
                    `).join('');
                }

                // Update info paginasi
                if (pagFrom) pagFrom.textContent = json.from;
                if (pagTo) pagTo.textContent = json.to;
                if (pagTotal) pagTotal.textContent = Number(json.total).toLocaleString('id-ID');

                // Render tombol paginasi
                renderPagination(json.current_page, json.last_page);
            }
        } catch (err) {
            console.error('Gagal mengambil data tabel:', err);
        }
    }

    function renderPagination(current, last) {
        if (!pagControls) return;
        if (last <= 1) {
            pagControls.innerHTML = '';
            return;
        }

        let html = '';

        // Tombol Sebelumnya
        const prevDisabled = current <= 1;
        html += `
            <button type="button" 
                    data-page="${current - 1}" 
                    class="btn-page px-2.5 py-1.5 rounded-lg border border-slate-200 ${prevDisabled ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-white text-slate-700 hover:bg-slate-100 cursor-pointer'} transition duration-150" 
                    ${prevDisabled ? 'disabled' : ''}>
                &larr;
            </button>
        `;

        // Kalkulasi daftar nomor halaman
        const pages = [];
        if (last <= 7) {
            for (let i = 1; i <= last; i++) pages.push(i);
        } else {
            pages.push(1);
            if (current > 3) pages.push('...');
            const start = Math.max(2, current - 1);
            const end = Math.min(last - 1, current + 1);
            for (let i = start; i <= end; i++) pages.push(i);
            if (current < last - 2) pages.push('...');
            pages.push(last);
        }

        // Render nomor halaman
        pages.forEach(p => {
            if (p === '...') {
                html += `<span class="px-2 py-1 text-slate-400 font-bold">...</span>`;
            } else {
                const isActive = p === current;
                html += `
                    <button type="button" 
                            data-page="${p}" 
                            class="btn-page px-3 py-1.5 rounded-lg font-bold transition duration-150 cursor-pointer ${isActive ? 'bg-cluster-1 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100'}">
                        ${p}
                    </button>
                `;
            }
        });

        // Tombol Selanjutnya
        const nextDisabled = current >= last;
        html += `
            <button type="button" 
                    data-page="${current + 1}" 
                    class="btn-page px-2.5 py-1.5 rounded-lg border border-slate-200 ${nextDisabled ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-white text-slate-700 hover:bg-slate-100 cursor-pointer'} transition duration-150" 
                    ${nextDisabled ? 'disabled' : ''}>
                &rarr;
            </button>
        `;

        pagControls.innerHTML = html;

        // Pasang event listener ke seluruh tombol paginasi
        pagControls.querySelectorAll('.btn-page:not([disabled])').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetPage = parseInt(this.getAttribute('data-page'), 10);
                if (targetPage && targetPage !== current) {
                    loadTableData(targetPage);
                }
            });
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadTableData(1), 250);
        });
    }

    if (kecamatanSelect) {
        kecamatanSelect.addEventListener('change', () => loadTableData(1));
    }

    // Inisialisasi tombol paginasi untuk data pertama dari server (tanpa curly braces blade di script)
    const initCurrent = parseInt(pagControls ? (pagControls.getAttribute('data-current') || '1') : '1', 10);
    const initLast = parseInt(pagControls ? (pagControls.getAttribute('data-last') || '1') : '1', 10);
    renderPagination(initCurrent, initLast);
});
</script>
@endsection
