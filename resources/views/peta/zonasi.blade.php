@extends('layouts.app')

@section('title', 'Pusat Usaha Wisata (Klasterisasi Spasial DBSCAN) — Kota Palembang')

@section('no-footer', true)

@section('styles')
<style>
    /* Mengunci jendela peramban agar peta mengisi 100% tinggi ruang yang tersedia */
    html, body {
        height: 100%;
        overflow: hidden;
    }

    #map-zonasi {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        height: 100%;
        background-color: #f8fafc;
        z-index: 10;
    }

    /* Kustomisasi scrollbar untuk workbench dan daftar kartu sentra */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Label Kartografis Centroid Sentra */
    .sentra-centroid-marker {
        background: transparent !important;
        border: none !important;
    }
    .sentra-centroid-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(6px);
        padding: 3px 8px;
        border-radius: 9999px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11px;
        font-weight: 700;
        color: #0f172a;
        box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.18), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        border: 1.5px solid;
        white-space: nowrap;
        pointer-events: auto;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .sentra-centroid-badge:hover {
        transform: scale(1.06);
        box-shadow: 0 8px 16px -2px rgba(0, 0, 0, 0.25);
    }

    /* Leaflet popup customization */
    .leaflet-popup-content-wrapper {
        border-radius: 14px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        padding: 0;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .leaflet-popup-content {
        margin: 0;
        line-height: 1.5;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .leaflet-popup-tip {
        background: #ffffff;
    }

    /* Slider track styling */
    input[type=range]::-webkit-slider-thumb {
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #8B1E2D;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(139, 30, 45, 0.4);
        border: 2px solid #ffffff;
        transition: transform 0.1s ease;
    }
    input[type=range]::-webkit-slider-thumb:hover {
        transform: scale(1.15);
    }
</style>
@endsection

@section('content')
<!-- Kontainer Penuh Halaman Zonasi -->
<div class="relative w-full h-[calc(100vh-64px)] overflow-hidden bg-slate-900 select-none">

    <!-- 1. Canvas Peta Utama Leaflet -->
    <div id="map-zonasi" class="w-full h-full"></div>

    <!-- 2. Sub-Navbar / Bilah Kerja Atas (Toolbar Spasial) -->
    <div class="absolute top-3 left-3 right-3 sm:left-4 sm:right-4 z-20 pointer-events-none">
        <div class="flex flex-wrap items-center justify-between gap-2 bg-white/95 backdrop-blur-md px-3.5 py-2 rounded-xl shadow-lg border border-slate-200/90 pointer-events-auto">
            
            <!-- Kiri: Tombol Toggle Workbench & Title -->
            <div class="flex items-center space-x-2.5">
                <button id="btn-toggle-workbench" type="button" 
                        class="inline-flex items-center space-x-2 bg-slate-900 hover:bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition duration-150">
                    <svg class="w-4 h-4 text-ampera-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span id="label-toggle-workbench">Pengaturan Zonasi</span>
                </button>

                <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>

                <!-- Parameter Ringkas Badge -->
                <div id="badge-parameter-singkat" class="hidden sm:inline-flex items-center space-x-1.5 bg-ampera-50 text-ampera-900 border border-ampera-200/80 px-2.5 py-1 rounded-lg text-xs font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-ampera-600 animate-pulse"></span>
                    <span>&epsilon;: <strong id="bar-val-eps" class="font-mono">300m</strong></span>
                    <span class="text-ampera-300">•</span>
                    <span>MinPts: <strong id="bar-val-minpts" class="font-mono">4</strong></span>
                </div>
            </div>

            <!-- Tengah: Status Acuan Resmi vs Simulasi Kustom -->
            <div class="hidden lg:flex items-center space-x-2">
                @if($konfigurasiAktif)
                    <div id="badge-status-acuan" class="inline-flex items-center space-x-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1 rounded-full text-xs font-semibold">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span id="teks-status-acuan">Standar Acuan Dinas Aktif ({{ $konfigurasiAktif->total_cluster }} Sentra)</span>
                    </div>
                @else
                    <div id="badge-status-acuan" class="inline-flex items-center space-x-1.5 bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1 rounded-full text-xs font-semibold">
                        <span id="teks-status-acuan">Simulasi Eksploratif DBSCAN</span>
                    </div>
                @endif
            </div>

            <!-- Kanan: Aksi Cepat & Navigasi -->
            <div class="flex items-center space-x-2">
                <!-- Tombol Buka/Tutup Hasil & Sentra -->
                <button id="btn-toggle-dock" type="button" 
                        class="inline-flex items-center space-x-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition duration-150">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span>Sentra (<span id="bar-total-sentra" class="font-bold font-mono">0</span>)</span>
                </button>

                <!-- Tombol Pusatkan Palembang -->
                <button id="btn-pusatkan-peta" type="button" title="Pusatkan Peta ke Kota Palembang"
                        class="inline-flex items-center space-x-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition duration-150">
                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="hidden sm:inline">Pusatkan</span>
                </button>

                <!-- Wewenang Eksklusif Pimpinan: Tombol Kunci Acuan Resmi -->
                @if(Auth::check() && Auth::user()->isPimpinan())
                    <button id="btn-buka-modal-kunci" type="button" 
                            class="inline-flex items-center space-x-1.5 bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition duration-150">
                        <svg class="w-4 h-4 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Kunci Acuan Resmi</span>
                    </button>
                @endif
            </div>

        </div>
    </div>

    <!-- 3. Floating Workbench (Panel Kontrol Parameter DBSCAN) - Kiri Atas -->
    <aside id="floating-workbench" 
           class="absolute top-16 left-3 sm:left-4 z-20 w-[92%] sm:w-96 max-h-[calc(100vh-90px)] flex flex-col bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl border border-slate-200/90 overflow-hidden transition-all duration-300">
        
        <!-- Header Panel Workbench -->
        <div class="px-4 py-3 bg-gradient-to-r from-ampera-800 to-ampera-900 text-white flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2.5">
                <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Parameter DBSCAN</h2>
                    <p class="text-[10px] text-white/80">Penentuan Sentra Berbasis Kerapatan</p>
                </div>
            </div>

            <!-- Tombol Tutup/Minimize Panel -->
            <button id="btn-minimize-workbench" type="button" title="Sembunyikan Panel"
                    class="p-1 rounded-lg hover:bg-white/10 text-white/80 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Isi Panel Scrollable -->
        <div class="p-4 space-y-4 overflow-y-auto custom-scrollbar flex-1 text-slate-700">

            <!-- Opsi 1: Cakupan Basis Data -->
            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1.5">Cakupan Data Usaha</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="cakupan_data" value="buka" checked class="peer sr-only">
                        <div class="text-center p-2 rounded-xl border border-slate-200 peer-checked:border-ampera-700 peer-checked:bg-ampera-50/70 peer-checked:text-ampera-900 transition duration-150">
                            <span class="block text-xs font-bold">Usaha Buka</span>
                            <span class="block text-[10px] text-slate-500 font-mono">1.281 Titik Aktif</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="cakupan_data" value="semua" class="peer sr-only">
                        <div class="text-center p-2 rounded-xl border border-slate-200 peer-checked:border-ampera-700 peer-checked:bg-ampera-50/70 peer-checked:text-ampera-900 transition duration-150">
                            <span class="block text-xs font-bold">Semua Usaha</span>
                            <span class="block text-[10px] text-slate-500 font-mono">1.421 Total OSS</span>
                        </div>
                    </label>
                </div>
                <p class="text-[10px] text-slate-400 mt-1 leading-normal">
                    *Rekomendasi Dinas: Gunakan <strong>Usaha Buka</strong> untuk memetakan sentra pariwisata yang riil beroperasi.
                </p>
            </div>

            <!-- Opsi 2: Filter Sektor KBLI -->
            <div>
                <label for="filter-kbli" class="block text-xs font-bold text-slate-800 mb-1">Filter Sektor (KBLI)</label>
                <select id="filter-kbli" class="w-full text-xs rounded-xl border border-slate-200 bg-white px-3 py-2 focus:border-ampera-700 focus:ring-1 focus:ring-ampera-700 outline-none transition">
                    <option value="">Semua Kategori Usaha (12 Sektor)</option>
                    @foreach($daftarKbli as $k)
                        <option value="{{ $k->id_kbli }}">{{ $k->judul_kbli }} ({{ $k->kode_kbli }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Opsi 3: Filter Wilayah Kecamatan -->
            <div>
                <label for="filter-kecamatan" class="block text-xs font-bold text-slate-800 mb-1">Filter Kecamatan</label>
                <select id="filter-kecamatan" class="w-full text-xs rounded-xl border border-slate-200 bg-white px-3 py-2 focus:border-ampera-700 focus:ring-1 focus:ring-ampera-700 outline-none transition">
                    <option value="">Semua Wilayah (18 Kecamatan Kota Palembang)</option>
                    @foreach($daftarKecamatan as $kc)
                        <option value="{{ $kc->id_kecamatan }}">{{ $kc->nama_kecamatan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="h-px bg-slate-100"></div>

            <!-- Parameter 1: Radius Jarak Epsilon (eps) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="input-eps" class="text-xs font-bold text-slate-800 flex items-center space-x-1">
                        <span>Radius Jarak (Epsilon / &epsilon;)</span>
                    </label>
                    <span id="label-eps" class="text-xs font-mono font-bold text-ampera-800 bg-ampera-50 border border-ampera-200 px-2.5 py-0.5 rounded-lg shadow-2xs">
                        {{ $konfigurasiAktif->parameter_eps ?? 300 }} meter
                    </span>
                </div>

                <input id="input-eps" type="range" min="100" max="1000" step="50" value="{{ $konfigurasiAktif->parameter_eps ?? 300 }}" 
                       class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer">

                <div class="flex justify-between text-[10px] text-slate-400 mt-1 font-mono">
                    <span>100m</span>
                    <span>500m</span>
                    <span>1.000m</span>
                </div>

                <!-- Preset Chips Epsilon -->
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-[10px] text-slate-400 font-medium">Preset:</span>
                    <button type="button" class="btn-preset-eps text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" data-val="200">200m</button>
                    <button type="button" class="btn-preset-eps text-[10px] font-mono px-2 py-0.5 rounded bg-ampera-50 hover:bg-ampera-100 text-ampera-800 font-bold border border-ampera-200 transition" data-val="300">300m (Resmi)</button>
                    <button type="button" class="btn-preset-eps text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" data-val="500">500m</button>
                    <button type="button" class="btn-preset-eps text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" data-val="750">750m</button>
                </div>
            </div>

            <!-- Parameter 2: Densitas Minimum Titik (MinPts) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="input-minpts" class="text-xs font-bold text-slate-800 flex items-center space-x-1">
                        <span>Kerapatan Minimal (MinPts)</span>
                    </label>
                    <span id="label-minpts" class="text-xs font-mono font-bold text-ampera-800 bg-ampera-50 border border-ampera-200 px-2.5 py-0.5 rounded-lg shadow-2xs">
                        {{ $konfigurasiAktif->parameter_minpts ?? 4 }} usaha
                    </span>
                </div>

                <input id="input-minpts" type="range" min="2" max="20" step="1" value="{{ $konfigurasiAktif->parameter_minpts ?? 4 }}" 
                       class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer">

                <div class="flex justify-between text-[10px] text-slate-400 mt-1 font-mono">
                    <span>2 titik</span>
                    <span>10 titik</span>
                    <span>20 titik</span>
                </div>

                <!-- Preset Chips MinPts -->
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-[10px] text-slate-400 font-medium">Preset:</span>
                    <button type="button" class="btn-preset-minpts text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" data-val="3">3</button>
                    <button type="button" class="btn-preset-minpts text-[10px] font-mono px-2 py-0.5 rounded bg-ampera-50 hover:bg-ampera-100 text-ampera-800 font-bold border border-ampera-200 transition" data-val="4">4 (Resmi)</button>
                    <button type="button" class="btn-preset-minpts text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" data-val="6">6</button>
                    <button type="button" class="btn-preset-minpts text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" data-val="10">10</button>
                </div>
            </div>

            <!-- Tombol Eksekusi Simulasi -->
            <div class="pt-2 flex flex-col space-y-2">
                <button id="btn-jalankan-simulasi" type="button" 
                        class="w-full bg-ampera-800 hover:bg-ampera-900 active:scale-[0.99] text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow-md transition duration-150 flex items-center justify-center space-x-2">
                    <svg id="icon-simulasi-play" class="w-4 h-4 text-ampera-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg id="icon-simulasi-loading" class="animate-spin w-4 h-4 text-white hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="text-tombol-simulasi">Temukan Sentra Usaha Wisata</span>
                </button>

                <button id="btn-reset-workbench" type="button" 
                        class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2 px-3 rounded-xl text-xs transition duration-150 flex items-center justify-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reset ke Acuan Resmi</span>
                </button>
            </div>

            <!-- Kotak Catatan Teori Algoritma DBSCAN -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-[11px] text-slate-600 leading-relaxed space-y-1">
                <div class="font-bold text-slate-800 flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5 text-ampera-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Formula Spasial Haversine</span>
                </div>
                <p>
                    DBSCAN menghitung jarak kelengkungan bumi (Haversine Metric) dalam satuan meter antar koordinat latitude dan longitude usaha. Titik diklasifikasikan menjadi:
                </p>
                <div class="pt-1 space-y-0.5">
                    <div class="flex items-center space-x-1.5"><span class="w-2 h-2 rounded-full bg-ampera-700"></span><span><strong>Core Point:</strong> Kerapatan &ge; MinPts dalam radius &epsilon;</span></div>
                    <div class="flex items-center space-x-1.5"><span class="w-2 h-2 rounded-full border border-ampera-700 bg-white"></span><span><strong>Border Point:</strong> Berada di pinggiran radius core</span></div>
                    <div class="flex items-center space-x-1.5"><span class="w-2 h-2 rounded-full bg-slate-400"></span><span><strong>Noise / Pencilan:</strong> Usaha mandiri di luar sentra</span></div>
                </div>
            </div>

        </div>

    </aside>

    <!-- 4. Floating Launcher (Muncul saat Workbench Di-minimize) -->
    <button id="floating-launcher-workbench" type="button" 
            class="hidden absolute top-16 left-4 z-20 bg-white/95 backdrop-blur-md shadow-xl border border-slate-200 text-slate-800 px-3.5 py-2 rounded-xl text-xs font-bold hover:bg-slate-50 transition duration-150 flex items-center space-x-2">
        <svg class="w-4 h-4 text-ampera-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
        </svg>
        <span>Buka Parameter Zonasi</span>
    </button>

    <!-- 5. Floating Bottom Dock (Hasil Analisis DBSCAN, Silhouette & Daftar Sentra) -->
    <div id="floating-bottom-dock" 
         class="absolute bottom-3 left-3 right-3 sm:left-1/2 sm:-translate-x-1/2 sm:w-[94%] sm:max-w-5xl z-20 bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl border border-slate-200/90 transition-all duration-300 overflow-hidden">
        
        <!-- Bar Header Dock (Metrik Utama & Silhouette) -->
        <div class="px-4 py-3 flex flex-wrap items-center justify-between gap-3 border-b border-slate-100">
            
            <!-- Kiri: Statistik Numerik -->
            <div class="flex items-center flex-wrap gap-3 sm:gap-6 text-xs">
                <!-- Total Sentra -->
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-ampera-100 text-ampera-800 flex items-center justify-center font-bold text-xs">
                        <span id="dock-count-sentra">0</span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Sentra Terdeteksi</span>
                        <span class="text-xs font-bold text-slate-800"><span id="dock-txt-sentra">0 Kawasan</span></span>
                    </div>
                </div>

                <!-- Usaha Terkelompok -->
                <div class="border-l border-slate-200 pl-3 sm:pl-4">
                    <span class="block text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Usaha Berkelompok</span>
                    <span class="text-xs font-bold text-emerald-700 font-mono">
                        <span id="dock-count-clustered">0</span> 
                        <span id="dock-pct-clustered" class="text-[10px] text-slate-500 font-normal">(0%)</span>
                    </span>
                </div>

                <!-- Noise (Usaha Mandiri) -->
                <div class="border-l border-slate-200 pl-3 sm:pl-4">
                    <span class="block text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Usaha Mandiri (Noise)</span>
                    <span class="text-xs font-bold text-slate-600 font-mono">
                        <span id="dock-count-noise">0</span> 
                        <span id="dock-pct-noise" class="text-[10px] text-slate-400 font-normal">(0%)</span>
                    </span>
                </div>

                <!-- Waktu Eksekusi -->
                <div class="hidden md:block border-l border-slate-200 pl-4">
                    <span class="block text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Waktu Hitung</span>
                    <span id="dock-execution-time" class="text-xs font-bold text-slate-800 font-mono">0 ms</span>
                </div>
            </div>

            <!-- Kanan: Silhouette Coefficient & Toggle Drawer -->
            <div class="flex items-center space-x-3">
                <!-- Silhouette Badge Card -->
                <div class="flex items-center space-x-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl">
                    <div>
                        <div class="flex items-center space-x-1.5">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Silhouette:</span>
                            <span id="dock-silhouette-val" class="text-xs font-mono font-bold text-slate-900">-</span>
                        </div>
                        <span id="dock-silhouette-label" class="block text-[10px] font-semibold text-slate-500 truncate max-w-[150px] sm:max-w-[200px]">
                            Menunggu evaluasi...
                        </span>
                    </div>
                </div>

                <!-- Tombol Collapse / Expand Drawer Sentra -->
                <button id="btn-toggle-drawer-sentra" type="button" title="Buka/Tutup Daftar Sentra"
                        class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    <svg id="icon-chevron-drawer" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

        </div>

        <!-- Drawer Konten: Slider Kartu Sentra Wisata -->
        <div id="drawer-sentra-content" class="p-3 bg-slate-50/60 border-t border-slate-100">
            <div class="flex items-center justify-between mb-2 px-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Daftar Kawasan Sentra Pariwisata</span>
                <span class="text-[10px] text-slate-400">Klik kartu untuk memperbesar dan fokus ke poligon sentra</span>
            </div>

            <!-- Kontainer Kartu Horizontal Scrollable -->
            <div id="kontainer-kartu-sentra" class="flex gap-3 overflow-x-auto pb-1.5 pt-0.5 custom-scrollbar">
                <!-- Placeholder / diisi secara dinamis oleh JavaScript -->
                <div class="text-xs text-slate-400 italic py-2 px-3">Memuat sentra pariwisata...</div>
            </div>
        </div>

    </div>

    <!-- 6. Floating Map Legend (Kanan Bawah Peta) -->
    <div id="floating-map-legend" 
         class="hidden sm:block absolute bottom-24 right-4 z-20 bg-white/95 backdrop-blur-md shadow-xl rounded-xl border border-slate-200/90 p-3 text-xs w-60">
        <div class="flex items-center justify-between mb-2 pb-1 border-b border-slate-100">
            <span class="font-bold text-slate-800 text-[11px] uppercase tracking-wider">Legenda Spasial</span>
            <span class="text-[10px] text-slate-400 font-mono">DBSCAN</span>
        </div>
        <div class="space-y-1.5 text-slate-600 text-[11px]">
            <div class="flex items-center space-x-2">
                <span class="w-4 h-3 rounded border border-ampera-700 bg-ampera-700/20 inline-block"></span>
                <span>Poligon Convex Hull Sentra</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-ampera-800 border-2 border-white shadow-xs inline-block"></span>
                <span>Titik Inti (Core Point)</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-ampera-600/70 border border-white inline-block"></span>
                <span>Titik Batas (Border Point)</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-500 border border-white inline-block"></span>
                <span>Usaha Mandiri (Pencilan / Noise)</span>
            </div>
        </div>
    </div>

    <!-- 7. Modal Konfirmasi Kunci Konfigurasi Acuan Resmi (Khusus Pimpinan) -->
    @if(Auth::check() && Auth::user()->isPimpinan())
    <div id="modal-kunci-konfigurasi" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden transition-all transform">
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-5 py-4 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold">Kunci Acuan Resmi Dinas</h3>
                        <p class="text-xs text-amber-100">Wewenang Eksklusif Pimpinan Dinas</p>
                    </div>
                </div>
                <button id="btn-tutup-modal-kunci" type="button" class="text-white/80 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="form-kunci-konfigurasi" class="p-5 space-y-4 text-xs text-slate-700">
                <p class="leading-relaxed">
                    Anda akan menetapkan hasil simulasi saat ini sebagai <strong>Standar Acuan Resmi Kawasan Pariwisata Kota Palembang</strong>. Seluruh laporan eksekutif dan peta publik akan merujuk pada parameter ini.
                </p>

                <!-- Ringkasan Nilai Parameter -->
                <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-3 grid grid-cols-2 gap-2 text-[11px]">
                    <div>
                        <span class="text-slate-500 block">Radius Epsilon (&epsilon;):</span>
                        <strong id="modal-val-eps" class="text-slate-900 font-mono text-xs">300 meter</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Kerapatan MinPts:</span>
                        <strong id="modal-val-minpts" class="text-slate-900 font-mono text-xs">4 titik</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Sentra Dihasilkan:</span>
                        <strong id="modal-val-sentra" class="text-slate-900 font-mono text-xs">- Sentra</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Skor Silhouette:</span>
                        <strong id="modal-val-silhouette" class="text-slate-900 font-mono text-xs">-</strong>
                    </div>
                </div>

                <div>
                    <label for="input-nama-konfigurasi" class="block font-bold text-slate-800 mb-1">Nama / Label Konfigurasi Resmi</label>
                    <input type="text" id="input-nama-konfigurasi" required
                           value="Sentra Resmi Pariwisata Palembang 2026 (Standar Acuan Dinas)"
                           class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
                </div>

                <div class="pt-2 flex items-center justify-end space-x-2">
                    <button type="button" id="btn-batal-kunci" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-kunci" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold shadow-sm transition flex items-center space-x-1.5">
                        <svg id="icon-submit-kunci-spinner" class="animate-spin w-4 h-4 text-white hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="text-submit-kunci">Konfirmasi & Kunci Resmi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
    /**
     * Engine Visualisasi Geospasial Zonasi Pusat Usaha Wisata Kota Palembang
     * Algoritma: DBSCAN (Density-Based Spatial Clustering of Applications with Noise)
     * Metrik Jarak: Haversine Geodesic Distance
     * Delineasi Batas: Monotone Chain Convex Hull Polygons
     * Evaluasi Validitas: Silhouette Coefficient
     */

    // Palet warna resmi 10 klaster DBSCAN sesuai dokumen desain
    const PALET_KLASTER = [
        '#8B1E2D', // Klaster 1: Ampera Crimson
        '#1E40AF', // Klaster 2: Royal Blue
        '#047857', // Klaster 3: Musi Emerald
        '#B45309', // Klaster 4: Sriwijaya Amber
        '#6D28D9', // Klaster 5: Regal Violet
        '#0E7490', // Klaster 6: Steel Cyan
        '#BE185D', // Klaster 7: Dark Pink
        '#4D7C0F', // Klaster 8: Lime Green
        '#C2410C', // Klaster 9: Orange Rust
        '#4338CA', // Klaster 10: Indigo
    ];
    const WARNA_NOISE = '#64748B';

    // Status State Aplikasi
    let map = null;
    let layerConvexHulls = null;
    let layerCentroids = null;
    let layerCoreMarkers = null;
    let layerBorderMarkers = null;
    let layerNoiseMarkers = null;

    let dataHasilTerakhir = null;
    let poligonSentraMap = {}; // cluster_id => L.polygon

    // Inisialisasi Peta Leaflet
    function inisialisasiPetaZonasi() {
        if (!window.L) {
            console.error('Leaflet belum dimuat.');
            return;
        }

        // 1. Instance Peta Berpusat di Kota Palembang
        map = L.map('map-zonasi', {
            center: [-2.983596, 104.7541449],
            zoom: 12,
            zoomControl: false // Kita posisikan custom atau gunakan default kanan
        });

        L.control.zoom({ position: 'topright' }).addTo(map);

        // 2. Base Tile Layer OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors | DPMPTSP Kota Palembang'
        }).addTo(map);

        // 3. Layer Groups terstruktur
        layerConvexHulls = L.layerGroup().addTo(map);
        layerCentroids = L.layerGroup().addTo(map);
        layerNoiseMarkers = L.layerGroup().addTo(map);
        layerBorderMarkers = L.layerGroup().addTo(map);
        layerCoreMarkers = L.layerGroup().addTo(map);

        setTimeout(() => {
            map.invalidateSize();
        }, 150);

        // 4. Langsung jalankan kalkulasi awal dengan parameter acuan resmi
        jalankanSimulasiDbscan();
    }

    // Eksekusi Peta saat DOM siap
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        inisialisasiPetaZonasi();
    } else {
        window.addEventListener('DOMContentLoaded', inisialisasiPetaZonasi);
    }

    // Pendaftaran Event Handlers UI
    document.addEventListener('DOMContentLoaded', function () {
        
        // 1. Slider Epsilon Live Update
        const sliderEps = document.getElementById('input-eps');
        const labelEps = document.getElementById('label-eps');
        const barValEps = document.getElementById('bar-val-eps');
        if (sliderEps && labelEps) {
            sliderEps.addEventListener('input', function () {
                const meter = parseInt(this.value);
                labelEps.textContent = `${meter} meter`;
                if (barValEps) barValEps.textContent = `${meter}m`;
            });
        }

        // Preset Chips Epsilon
        document.querySelectorAll('.btn-preset-eps').forEach(btn => {
            btn.addEventListener('click', function () {
                const val = this.getAttribute('data-val');
                sliderEps.value = val;
                labelEps.textContent = `${val} meter`;
                if (barValEps) barValEps.textContent = `${val}m`;
            });
        });

        // 2. Slider MinPts Live Update
        const sliderMinPts = document.getElementById('input-minpts');
        const labelMinPts = document.getElementById('label-minpts');
        const barValMinPts = document.getElementById('bar-val-minpts');
        if (sliderMinPts && labelMinPts) {
            sliderMinPts.addEventListener('input', function () {
                const pts = parseInt(this.value);
                labelMinPts.textContent = `${pts} usaha`;
                if (barValMinPts) barValMinPts.textContent = `${pts}`;
            });
        }

        // Preset Chips MinPts
        document.querySelectorAll('.btn-preset-minpts').forEach(btn => {
            btn.addEventListener('click', function () {
                const val = this.getAttribute('data-val');
                sliderMinPts.value = val;
                labelMinPts.textContent = `${val} usaha`;
                if (barValMinPts) barValMinPts.textContent = `${val}`;
            });
        });

        // 3. Tombol Eksekusi Simulasi
        const btnSimulasi = document.getElementById('btn-jalankan-simulasi');
        if (btnSimulasi) {
            btnSimulasi.addEventListener('click', function () {
                jalankanSimulasiDbscan();
            });
        }

        // 4. Tombol Reset Workbench ke Acuan Resmi
        const btnReset = document.getElementById('btn-reset-workbench');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                sliderEps.value = 300;
                sliderMinPts.value = 4;
                labelEps.textContent = '300 meter';
                labelMinPts.textContent = '4 usaha';
                if (barValEps) barValEps.textContent = '300m';
                if (barValMinPts) barValMinPts.textContent = '4';

                document.getElementById('filter-kbli').value = '';
                document.getElementById('filter-kecamatan').value = '';

                const radBuka = document.querySelector('input[name="cakupan_data"][value="buka"]');
                if (radBuka) radBuka.checked = true;

                jalankanSimulasiDbscan();
            });
        }

        // 5. Toggle Workbench (Kiri Atas)
        const workbench = document.getElementById('floating-workbench');
        const btnToggleWb = document.getElementById('btn-toggle-workbench');
        const btnMinWb = document.getElementById('btn-minimize-workbench');
        const launcherWb = document.getElementById('floating-launcher-workbench');

        function toggleWorkbench(show) {
            if (show) {
                workbench.classList.remove('hidden');
                launcherWb.classList.add('hidden');
            } else {
                workbench.classList.add('hidden');
                launcherWb.classList.remove('hidden');
            }
        }

        if (btnToggleWb) {
            btnToggleWb.addEventListener('click', () => {
                const isHidden = workbench.classList.contains('hidden');
                toggleWorkbench(isHidden);
            });
        }
        if (btnMinWb) {
            btnMinWb.addEventListener('click', () => toggleWorkbench(false));
        }
        if (launcherWb) {
            launcherWb.addEventListener('click', () => toggleWorkbench(true));
        }

        // 6. Toggle Dock Hasil Sentra (Bawah)
        const dock = document.getElementById('floating-bottom-dock');
        const btnToggleDock = document.getElementById('btn-toggle-dock');
        const btnToggleDrawer = document.getElementById('btn-toggle-drawer-sentra');
        const drawerContent = document.getElementById('drawer-sentra-content');
        const iconChevron = document.getElementById('icon-chevron-drawer');

        if (btnToggleDrawer && drawerContent) {
            btnToggleDrawer.addEventListener('click', () => {
                drawerContent.classList.toggle('hidden');
                iconChevron.classList.toggle('rotate-180');
            });
        }

        if (btnToggleDock && dock) {
            btnToggleDock.addEventListener('click', () => {
                dock.classList.toggle('hidden');
            });
        }

        // 7. Tombol Pusatkan Palembang
        const btnPusatkan = document.getElementById('btn-pusatkan-peta');
        if (btnPusatkan) {
            btnPusatkan.addEventListener('click', () => {
                if (map) {
                    map.setView([-2.983596, 104.7541449], 12, { animate: true });
                }
            });
        }

        // 8. Logika Modal Kunci Konfigurasi Pimpinan
        inisialisasiModalPimpinan();
    });

    /**
     * Memanggil Endpoint API /api/simulasi-dbscan dan merender poligon serta titik klaster
     */
    function jalankanSimulasiDbscan() {
        const btnSimulasi = document.getElementById('btn-jalankan-simulasi');
        const iconPlay = document.getElementById('icon-simulasi-play');
        const iconLoading = document.getElementById('icon-simulasi-loading');
        const textTombol = document.getElementById('text-tombol-simulasi');

        // Mengambil nilai parameter dari UI
        const epsMeter = parseFloat(document.getElementById('input-eps').value);
        const minPts = parseInt(document.getElementById('input-minpts').value);
        const idKbli = document.getElementById('filter-kbli').value;
        const idKecamatan = document.getElementById('filter-kecamatan').value;
        const cakupanEl = document.querySelector('input[name="cakupan_data"]:checked');
        const cakupan = cakupanEl ? cakupanEl.value : 'buka';

        // Tampilkan state loading
        if (btnSimulasi) btnSimulasi.disabled = true;
        if (iconPlay) iconPlay.classList.add('hidden');
        if (iconLoading) iconLoading.classList.remove('hidden');
        if (textTombol) textTombol.textContent = 'Mengkalkulasi DBSCAN...';

        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        fetch('/api/simulasi-dbscan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                eps: epsMeter,
                min_pts: minPts,
                cakupan_data: cakupan,
                id_kbli: idKbli || null,
                id_kecamatan: idKecamatan || null
            })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Gagal memproses kalkulasi spasial di server (Status: ' + res.status + ')');
            }
            return res.json();
        })
        .then(res => {
            // Pulihkan tombol
            if (btnSimulasi) btnSimulasi.disabled = false;
            if (iconPlay) iconPlay.classList.remove('hidden');
            if (iconLoading) iconLoading.classList.add('hidden');
            if (textTombol) textTombol.textContent = 'Temukan Sentra Usaha Wisata';

            if (!res.success) {
                alert('Peringatan: ' + (res.message || 'Gagal menjalankan klasterisasi.'));
                return;
            }

            dataHasilTerakhir = res;
            renderHasilKlasterisasi(res);
        })
        .catch(err => {
            if (btnSimulasi) btnSimulasi.disabled = false;
            if (iconPlay) iconPlay.classList.remove('hidden');
            if (iconLoading) iconLoading.classList.add('hidden');
            if (textTombol) textTombol.textContent = 'Temukan Sentra Usaha Wisata';

            console.error('Error kalkulasi DBSCAN:', err);
            alert('Terjadi kesalahan saat memproses data: ' + err.message);
        });
    }

    /**
     * Merender poligon Convex Hull, marker titik (core, border, noise),
     * kartu ringkasan sentra, serta metrik Silhouette Score.
     */
    function renderHasilKlasterisasi(res) {
        // 1. Bersihkan seluruh layer spasial sebelumnya
        layerConvexHulls.clearLayers();
        layerCentroids.clearLayers();
        layerCoreMarkers.clearLayers();
        layerBorderMarkers.clearLayers();
        layerNoiseMarkers.clearLayers();
        poligonSentraMap = {};

        const totalClusters = res.total_clusters;
        const totalPoints = res.total_points;
        const totalNoise = res.total_noise;
        const totalClustered = totalPoints - totalNoise;
        const pctClustered = totalPoints > 0 ? ((totalClustered / totalPoints) * 100).toFixed(1) : '0';
        const pctNoise = totalPoints > 0 ? ((totalNoise / totalPoints) * 100).toFixed(1) : '0';

        // 2. Perbarui Angka di Bilah Atas & Dock Bawah
        const elBarSentra = document.getElementById('bar-total-sentra');
        if (elBarSentra) elBarSentra.textContent = totalClusters;

        document.getElementById('dock-count-sentra').textContent = totalClusters;
        document.getElementById('dock-txt-sentra').textContent = `${totalClusters} Kawasan`;
        document.getElementById('dock-count-clustered').textContent = totalClustered.toLocaleString('id-ID');
        document.getElementById('dock-pct-clustered').textContent = `(${pctClustered}%)`;
        document.getElementById('dock-count-noise').textContent = totalNoise.toLocaleString('id-ID');
        document.getElementById('dock-pct-noise').textContent = `(${pctNoise}%)`;
        document.getElementById('dock-execution-time').textContent = `${res.execution_time_ms} ms`;

        // 3. Render Silhouette Score & Badge Kualitatif
        const valSilEl = document.getElementById('dock-silhouette-val');
        const lblSilEl = document.getElementById('dock-silhouette-label');

        if (res.silhouette_score !== null && res.silhouette_score !== undefined) {
            const sVal = parseFloat(res.silhouette_score);
            valSilEl.textContent = sVal.toFixed(4);

            if (sVal >= 0.51) {
                lblSilEl.className = 'block text-[10px] font-bold text-emerald-700 truncate';
                lblSilEl.textContent = res.silhouette_label || 'Struktur Sangat Kuat';
            } else if (sVal >= 0.26) {
                lblSilEl.className = 'block text-[10px] font-bold text-amber-700 truncate';
                lblSilEl.textContent = res.silhouette_label || 'Struktur Sedang';
            } else {
                lblSilEl.className = 'block text-[10px] font-bold text-slate-600 truncate';
                lblSilEl.textContent = res.silhouette_label || 'Struktur Lemah / Padat';
            }
        } else {
            valSilEl.textContent = 'N/A';
            lblSilEl.className = 'block text-[10px] font-semibold text-slate-400 truncate';
            lblSilEl.textContent = 'Minimal 2 sentra untuk validasi';
        }

        // 4. Render Poligon Convex Hull & Centroid Tiap Sentra
        const clusters = res.clusters || {};
        const clusterIds = Object.keys(clusters).map(Number).sort((a, b) => a - b);

        clusterIds.forEach(cId => {
            const cData = clusters[cId];
            const warnaIdx = (cId - 1) % PALET_KLASTER.length;
            const warnaKlaster = PALET_KLASTER[warnaIdx];

            // Render Poligon jika memiliki minimal 3 koordinat convex hull
            if (cData.convex_hull && cData.convex_hull.length >= 3) {
                const polygon = L.polygon(cData.convex_hull, {
                    color: warnaKlaster,
                    weight: 2.5,
                    opacity: 0.9,
                    fillColor: warnaKlaster,
                    fillOpacity: 0.16,
                    dashArray: '5, 5'
                }).addTo(layerConvexHulls);

                // Efek hover poligon
                polygon.on('mouseover', function () {
                    this.setStyle({ fillOpacity: 0.32, weight: 3.5 });
                });
                polygon.on('mouseout', function () {
                    this.setStyle({ fillOpacity: 0.16, weight: 2.5 });
                });

                // Popup poligon
                const ringkasanSektor = Object.entries(cData.sektor_ringkasan || {})
                    .map(([sektor, jml]) => `<li class="flex justify-between py-0.5"><span class="truncate pr-2">${sektor}</span><strong class="font-mono text-slate-800">${jml}</strong></li>`)
                    .join('');

                const popupPoligon = `
                    <div class="p-3 text-xs max-w-xs font-sans">
                        <div class="flex items-center space-x-2 mb-2 pb-1.5 border-b border-slate-100">
                            <span class="w-3 h-3 rounded-full" style="background-color: ${warnaKlaster}"></span>
                            <h4 class="font-bold text-sm text-slate-900">Sentra Pariwisata #${cId}</h4>
                        </div>
                        <div class="space-y-1 mb-2.5">
                            <p class="text-slate-600"><span class="font-semibold text-slate-700">Sektor Dominan:</span> ${cData.sektor_dominan}</p>
                            <p class="text-slate-600"><span class="font-semibold text-slate-700">Total Anggota:</span> <strong class="font-mono text-slate-900">${cData.total_titik}</strong> usaha (${cData.total_core} Core, ${cData.total_border} Border)</p>
                        </div>
                        <div class="bg-slate-50 p-2 rounded-lg border border-slate-200/80">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Komposisi Sektor:</span>
                            <ul class="text-[11px] text-slate-600 divide-y divide-slate-100">
                                ${ringkasanSektor}
                            </ul>
                        </div>
                    </div>
                `;
                polygon.bindPopup(popupPoligon);
                poligonSentraMap[cId] = polygon;
            }

            // Render Centroid Badge
            if (cData.center && cData.center.latitude && cData.center.longitude) {
                const labelIcon = L.divIcon({
                    className: 'sentra-centroid-marker',
                    html: `
                        <div class="sentra-centroid-badge" style="border-color: ${warnaKlaster}" onclick="fokusKeSentra(${cId})">
                            <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: ${warnaKlaster}"></span>
                            <span>Sentra ${cId}: ${cData.sektor_dominan} (${cData.total_titik})</span>
                        </div>
                    `,
                    iconSize: [160, 24],
                    iconAnchor: [80, 12]
                });

                L.marker([cData.center.latitude, cData.center.longitude], { icon: labelIcon }).addTo(layerCentroids);
            }
        });

        // 5. Render Titik-Titik Usaha (Core, Border, Noise)
        const points = res.points || [];
        points.forEach(p => {
            const isCluster = p.cluster_id > 0;
            const cId = p.cluster_id;
            const warnaKlaster = isCluster ? PALET_KLASTER[(cId - 1) % PALET_KLASTER.length] : WARNA_NOISE;
            const tipeTitik = p.tipe_titik || (isCluster ? 'core' : 'noise');

            let marker;
            if (tipeTitik === 'core') {
                marker = L.circleMarker([p.latitude, p.longitude], {
                    radius: 5.5,
                    fillColor: warnaKlaster,
                    color: '#FFFFFF',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.95
                }).addTo(layerCoreMarkers);
            } else if (tipeTitik === 'border') {
                marker = L.circleMarker([p.latitude, p.longitude], {
                    radius: 4.5,
                    fillColor: warnaKlaster,
                    color: '#FFFFFF',
                    weight: 1.5,
                    opacity: 0.9,
                    fillOpacity: 0.75
                }).addTo(layerBorderMarkers);
            } else {
                marker = L.circleMarker([p.latitude, p.longitude], {
                    radius: 3.5,
                    fillColor: WARNA_NOISE,
                    color: '#FFFFFF',
                    weight: 1,
                    opacity: 0.8,
                    fillOpacity: 0.45
                }).addTo(layerNoiseMarkers);
            }

            // Konten Popup Titik Usaha
            const badgeTipe = tipeTitik === 'core' 
                ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Titik Inti (Core)</span>'
                : (tipeTitik === 'border'
                    ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Titik Batas (Border)</span>'
                    : '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">Pencilan (Noise)</span>');

            const badgeSentra = isCluster
                ? `<span class="px-2 py-0.5 rounded text-[10px] font-bold text-white" style="background-color: ${warnaKlaster}">Sentra #${cId}</span>`
                : '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-700 text-white">Usaha Mandiri</span>';

            const statusOperasionalBadge = (p.status_operasional === 'Buka' || !p.status_operasional)
                ? '<span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Aktif Buka</span>'
                : `<span class="text-[10px] font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">${p.status_operasional}</span>`;

            const popupContent = `
                <div class="p-3 text-xs max-w-xs font-sans">
                    <div class="flex items-center justify-between gap-1 mb-2">
                        <div class="flex items-center space-x-1.5">
                            ${badgeSentra}
                            ${badgeTipe}
                        </div>
                        ${statusOperasionalBadge}
                    </div>
                    <h3 class="font-bold text-sm text-slate-900 mb-1 leading-snug">${p.nama_usaha}</h3>
                    <div class="space-y-1 text-slate-600 mb-2 border-t border-slate-100 pt-1.5">
                        <p><span class="font-semibold text-slate-700">Kategori:</span> ${p.judul_kbli || '-'}</p>
                        <p><span class="font-semibold text-slate-700">Kecamatan:</span> ${p.nama_kecamatan || '-'}</p>
                        <p class="text-[11px] text-slate-500"><span class="font-semibold text-slate-700">Alamat:</span> ${p.alamat || '-'}</p>
                    </div>
                    <div class="bg-slate-50 p-1.5 rounded text-[10px] text-slate-400 font-mono flex items-center justify-between">
                        <span>Lat: ${p.latitude}</span>
                        <span>Lon: ${p.longitude}</span>
                    </div>
                </div>
            `;
            marker.bindPopup(popupContent);
        });

        // 6. Render Slider Kartu Sentra di Dock Bawah
        renderKartuSentra(clusters);
    }

    /**
     * Membuat kartu horizontal interaktif untuk setiap sentra pariwisata
     */
    function renderKartuSentra(clusters) {
        const kontainer = document.getElementById('kontainer-kartu-sentra');
        if (!kontainer) return;
        kontainer.innerHTML = '';

        const clusterIds = Object.keys(clusters).map(Number).sort((a, b) => a - b);

        if (clusterIds.length === 0) {
            kontainer.innerHTML = '<div class="text-xs text-slate-400 italic py-2 px-3">Tidak ada sentra yang memenuhi kriteria kerapatan dengan parameter saat ini.</div>';
            return;
        }

        clusterIds.forEach(cId => {
            const cData = clusters[cId];
            const warnaIdx = (cId - 1) % PALET_KLASTER.length;
            const warna = PALET_KLASTER[warnaIdx];

            const kartu = document.createElement('div');
            kartu.className = 'shrink-0 w-64 bg-white border border-slate-200/90 hover:border-slate-300 rounded-xl p-3 shadow-2xs hover:shadow-md transition duration-150 cursor-pointer flex flex-col justify-between';
            kartu.onclick = () => fokusKeSentra(cId);

            kartu.innerHTML = `
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-white shadow-2xs" style="background-color: ${warna}">
                            <span>Sentra #${cId}</span>
                        </span>
                        <span class="font-mono text-xs font-bold text-slate-800">${cData.total_titik} <span class="text-[10px] font-normal text-slate-500">usaha</span></span>
                    </div>
                    <h5 class="font-bold text-xs text-slate-900 truncate mb-1" title="${cData.sektor_dominan}">${cData.sektor_dominan}</h5>
                    <div class="flex items-center space-x-2 text-[10px] text-slate-500 font-mono">
                        <span class="text-emerald-700">${cData.total_core} Core</span>
                        <span>•</span>
                        <span class="text-amber-700">${cData.total_border} Border</span>
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-ampera-700 font-semibold group">
                    <span>Fokus ke Sentra</span>
                    <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            `;

            kontainer.appendChild(kartu);
        });
    }

    /**
     * Memperbesar peta dan memusatkan pandangan ke poligon sentra tertentu
     */
    function fokusKeSentra(clusterId) {
        if (!map) return;

        const polygon = poligonSentraMap[clusterId];
        if (polygon) {
            map.fitBounds(polygon.getBounds(), {
                padding: [60, 60],
                maxZoom: 16,
                animate: true,
                duration: 0.8
            });
            setTimeout(() => {
                polygon.openPopup();
            }, 500);
        } else if (dataHasilTerakhir && dataHasilTerakhir.clusters[clusterId]) {
            const center = dataHasilTerakhir.clusters[clusterId].center;
            if (center && center.latitude && center.longitude) {
                map.setView([center.latitude, center.longitude], 15, { animate: true });
            }
        }
    }

    /**
     * Logika Modal Wewenang Pimpinan untuk Mengunci Acuan Resmi
     */
    function inisialisasiModalPimpinan() {
        const btnBuka = document.getElementById('btn-buka-modal-kunci');
        const modal = document.getElementById('modal-kunci-konfigurasi');
        const btnTutup = document.getElementById('btn-tutup-modal-kunci');
        const btnBatal = document.getElementById('btn-batal-kunci');
        const formKunci = document.getElementById('form-kunci-konfigurasi');

        if (!btnBuka || !modal) return;

        function bukaModal() {
            if (!dataHasilTerakhir) {
                alert('Silakan jalankan simulasi terlebih dahulu sebelum mengunci konfigurasi.');
                return;
            }

            document.getElementById('modal-val-eps').textContent = `${dataHasilTerakhir.parameter_eps_meter} meter`;
            document.getElementById('modal-val-minpts').textContent = `${dataHasilTerakhir.parameter_minpts} usaha`;
            document.getElementById('modal-val-sentra').textContent = `${dataHasilTerakhir.total_clusters} Sentra`;
            document.getElementById('modal-val-silhouette').textContent = dataHasilTerakhir.silhouette_score !== null 
                ? `${dataHasilTerakhir.silhouette_score} (${dataHasilTerakhir.silhouette_label})` 
                : 'N/A';

            modal.classList.remove('hidden');
        }

        function tutupModal() {
            modal.classList.add('hidden');
        }

        btnBuka.addEventListener('click', bukaModal);
        if (btnTutup) btnTutup.addEventListener('click', tutupModal);
        if (btnBatal) btnBatal.addEventListener('click', tutupModal);

        if (formKunci) {
            formKunci.addEventListener('submit', function (e) {
                e.preventDefault();

                if (!dataHasilTerakhir) return;

                const namaKonfig = document.getElementById('input-nama-konfigurasi').value;
                const btnSubmit = document.getElementById('btn-submit-kunci');
                const spinner = document.getElementById('icon-submit-kunci-spinner');
                const teksSubmit = document.getElementById('text-submit-kunci');

                btnSubmit.disabled = true;
                if (spinner) spinner.classList.remove('hidden');
                if (teksSubmit) teksSubmit.textContent = 'Menyimpan...';

                const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

                fetch('/admin/kunci-konfigurasi', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        parameter_eps: dataHasilTerakhir.parameter_eps_meter,
                        parameter_minpts: dataHasilTerakhir.parameter_minpts,
                        cakupan_data: dataHasilTerakhir.cakupan_data || 'buka_saja',
                        nama_konfigurasi: namaKonfig
                    })
                })
                .then(res => res.json())
                .then(res => {
                    btnSubmit.disabled = false;
                    if (spinner) spinner.classList.add('hidden');
                    if (teksSubmit) teksSubmit.textContent = 'Konfirmasi & Kunci Resmi';

                    if (res.success) {
                        tutupModal();
                        alert('Berhasil: ' + res.message);
                        window.location.reload();
                    } else {
                        alert('Gagal mengunci: ' + (res.message || 'Terjadi kesalahan sistem.'));
                    }
                })
                .catch(err => {
                    btnSubmit.disabled = false;
                    if (spinner) spinner.classList.add('hidden');
                    if (teksSubmit) teksSubmit.textContent = 'Konfirmasi & Kunci Resmi';
                    console.error('Error saat mengunci konfigurasi:', err);
                    alert('Gagal menghubungi server: ' + err.message);
                });
            });
        }
    }
</script>
@endsection
