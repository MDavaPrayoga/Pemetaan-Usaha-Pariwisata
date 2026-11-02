@extends('layouts.app')

@section('title', 'Pusat Usaha Wisata (Klasterisasi Spasial DBSCAN) — Kota Palembang')

@section('no-footer', true)

@section('styles')
<style>
    /* Mengunci viewport agar tidak ada scrollbar ganda di luar kanvas peta */
    html, body {
        height: 100%;
        overflow: hidden;
    }

    /* Peta Leaflet membentang penuh di area kanvas kanan */
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

    /* Kustomisasi scrollbar sidebar direktori sentra & form parameter */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Pin Titik Pusat (Pola Sentra) di Peta: Kompak, Elegan & Bebas Tabrakan Teks */
    .sentra-center-pin-marker {
        background: transparent !important;
        border: none !important;
    }
    .sentra-center-pin {
        width: 26px;
        height: 26px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11px;
        font-weight: 800;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.35), 0 0 0 2px #ffffff;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .sentra-center-pin:hover {
        transform: scale(1.22);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.45), 0 0 0 2.5px #ffffff;
    }

    /* Kustomisasi Popup Leaflet */
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
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

    /* Styling Thumb Slider Input Range */
    input[type=range]::-webkit-slider-thumb {
        appearance: none;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #8B1E2D;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(139, 30, 45, 0.4);
        border: 2px solid #ffffff;
        transition: transform 0.1s ease;
    }
    input[type=range]::-webkit-slider-thumb:hover {
        transform: scale(1.15);
    }
</style>
@endsection

@section('content')
<!-- Bilah Kerja Atas (Sub-Navbar Toolbar Bersih Putih h-11) -->
<div class="h-11 bg-white border-b border-slate-200/90 shadow-2xs z-30 shrink-0 px-3 sm:px-4 flex items-center justify-between gap-2">
    
    <!-- Kiri: Tombol Buka/Tutup Sidebar & Indikator Parameter -->
    <div class="flex items-center space-x-2">
        <button id="btn-toggle-sidebar" 
                type="button" 
                title="Buka / Sembunyikan Bilah Samping" 
                class="px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200/90 hover:border-slate-300 transition flex items-center space-x-1.5 shadow-2xs">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
            <span id="label-toggle-sidebar" class="hidden sm:inline">Tutup Panel</span>
        </button>

        <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>

        <!-- Pill Parameter Aktif -->
        <div class="hidden sm:inline-flex items-center space-x-1.5 bg-ampera-50 text-ampera-900 border border-ampera-200/80 px-2.5 py-1 rounded-lg text-xs font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-ampera-600 animate-pulse"></span>
            <span>&epsilon;: <strong id="bar-eps" class="font-mono">{{ $konfigurasiAktif->parameter_eps ?? 300 }}m</strong></span>
            <span class="text-ampera-300">•</span>
            <span>MinPts: <strong id="bar-minpts" class="font-mono">{{ $konfigurasiAktif->parameter_minpts ?? 4 }}</strong></span>
        </div>
    </div>

    <!-- Tengah: Status Acuan Standar Dinas / Simulasi Kustom -->
    <div class="flex items-center space-x-2">
        @if($konfigurasiAktif)
            <div id="bar-status-acuan" class="inline-flex items-center space-x-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1 rounded-full text-xs font-semibold">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span id="teks-status-acuan">Standar Acuan Dinas Aktif ({{ $konfigurasiAktif->total_cluster }} Sentra)</span>
            </div>
        @else
            <div id="bar-status-acuan" class="inline-flex items-center space-x-1.5 bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1 rounded-full text-xs font-semibold">
                <span id="teks-status-acuan">Simulasi Eksploratif DBSCAN</span>
            </div>
        @endif
    </div>

    <!-- Kanan: Aksi Cepat (Pusatkan Peta & Wewenang Pimpinan) -->
    <div class="flex items-center space-x-1.5 shrink-0">
        <!-- Pusatkan Peta -->
        <button id="btn-recenter-map" 
                type="button" 
                title="Pusatkan Peta ke Kota Palembang" 
                class="px-2.5 py-1 rounded-lg text-xs font-medium text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-900 transition flex items-center space-x-1.5 shadow-2xs">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="hidden md:inline">Pusatkan Peta</span>
        </button>

        <!-- Wewenang Eksklusif Pimpinan Dinas: Kunci Acuan Resmi -->
        @if(Auth::check() && Auth::user()->isPimpinan())
            <button id="btn-buka-modal-kunci" 
                    type="button" 
                    class="px-3 py-1 rounded-lg text-xs font-bold text-white bg-amber-500 hover:bg-amber-600 transition flex items-center space-x-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Kunci Acuan Resmi</span>
            </button>
        @endif
    </div>

</div>

<!-- Wadah Utama 2 Kolom (Sidebar Kiri + Canvas Peta Kanan) -->
<div class="flex-1 min-h-0 flex relative overflow-hidden w-full">

    <!-- ============================================================== -->
    <!-- SIDEBAR KIRI: KONTROL PARAMETER DBSCAN & DIREKTORI SENTRA      -->
    <!-- ============================================================== -->
    <aside id="sidebar-zonasi" 
           class="w-full md:w-96 lg:w-[410px] bg-white border-r border-slate-200 flex flex-col h-full min-h-0 z-20 shrink-0 shadow-sm transition-all duration-200">
        
        <!-- Header Sidebar dengan Tombol Panah Tutup -->
        <div class="px-4 py-3 border-b border-slate-200/90 bg-white flex items-center justify-between shrink-0">
            <div>
                <h2 class="text-xs font-bold text-slate-900 tracking-wider uppercase flex items-center space-x-1.5">
                    <span>Pusat Usaha Wisata (DBSCAN)</span>
                </h2>
                <p class="text-[11px] text-slate-500">Delineasi Kawasan Sentra & Analisis Aglomerasi</p>
            </div>
            <button id="btn-collapse-sidebar" 
                    type="button" 
                    title="Sembunyikan Panel" 
                    class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Konten Sidebar Scrollable Mandiri -->
        <div class="flex-1 overflow-y-auto custom-scrollbar flex flex-col divide-y divide-slate-100">

            <!-- BAGIAN 1: FORMULIR PARAMETER SIMULASI DBSCAN -->
            <div class="p-3.5 space-y-3 bg-slate-50/60">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5 text-ampera-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        <span>Parameter Algoritma</span>
                    </span>
                    <span class="text-[10px] font-semibold bg-ampera-100/80 text-ampera-800 px-2 py-0.5 rounded">Haversine</span>
                </div>

                <!-- Opsi Cakupan Data Usaha -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cakupan Basis Data</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="cakupan_data" value="buka" checked class="peer sr-only">
                            <div class="text-center p-1.5 rounded-lg border border-slate-200 peer-checked:border-ampera-700 peer-checked:bg-ampera-50/70 peer-checked:text-ampera-900 transition">
                                <span class="block text-xs font-bold leading-tight">Usaha Buka</span>
                                <span class="block text-[10px] text-slate-500 font-mono">1.281 Titik</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="cakupan_data" value="semua" class="peer sr-only">
                            <div class="text-center p-1.5 rounded-lg border border-slate-200 peer-checked:border-ampera-700 peer-checked:bg-ampera-50/70 peer-checked:text-ampera-900 transition">
                                <span class="block text-xs font-bold leading-tight">Semua Usaha</span>
                                <span class="block text-[10px] text-slate-500 font-mono">1.421 Total</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Filter KBLI & Kecamatan -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="filter-kbli" class="block text-[11px] font-semibold text-slate-600 mb-1">Sektor (KBLI)</label>
                        <select id="filter-kbli" class="w-full text-xs rounded-lg border border-slate-200 bg-white px-2 py-1.5 focus:border-ampera-700 outline-none">
                            <option value="">Semua Sektor</option>
                            @foreach($daftarKbli as $k)
                                <option value="{{ $k->id_kbli }}">{{ $k->judul_kbli }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter-kecamatan" class="block text-[11px] font-semibold text-slate-600 mb-1">Kecamatan</label>
                        <select id="filter-kecamatan" class="w-full text-xs rounded-lg border border-slate-200 bg-white px-2 py-1.5 focus:border-ampera-700 outline-none">
                            <option value="">Semua Kecamatan</option>
                            @foreach($daftarKecamatan as $kc)
                                <option value="{{ $kc->id_kecamatan }}">{{ $kc->nama_kecamatan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Slider Parameter Epsilon (eps) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="input-eps" class="text-[11px] font-semibold text-slate-600">Radius Jarak (&epsilon;)</label>
                        <span id="label-eps" class="text-xs font-mono font-bold text-ampera-800 bg-ampera-50 border border-ampera-200 px-2 py-0.5 rounded">
                            {{ $konfigurasiAktif->parameter_eps ?? 300 }} meter
                        </span>
                    </div>
                    <input id="input-eps" type="range" min="100" max="1000" step="50" value="{{ $konfigurasiAktif->parameter_eps ?? 300 }}" 
                           class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                    <div class="flex items-center gap-1 mt-1.5">
                        <span class="text-[10px] text-slate-400">Preset:</span>
                        <button type="button" class="btn-preset-eps text-[10px] font-mono px-1.5 py-0.5 rounded bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 transition" data-val="200">200m</button>
                        <button type="button" class="btn-preset-eps text-[10px] font-mono px-1.5 py-0.5 rounded bg-ampera-50 border border-ampera-200 text-ampera-800 font-bold transition" data-val="300">300m (Resmi)</button>
                        <button type="button" class="btn-preset-eps text-[10px] font-mono px-1.5 py-0.5 rounded bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 transition" data-val="500">500m</button>
                        <button type="button" class="btn-preset-eps text-[10px] font-mono px-1.5 py-0.5 rounded bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 transition" data-val="750">750m</button>
                    </div>
                </div>

                <!-- Slider Parameter MinPts -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="input-minpts" class="text-[11px] font-semibold text-slate-600">Kerapatan Minimal (MinPts)</label>
                        <span id="label-minpts" class="text-xs font-mono font-bold text-ampera-800 bg-ampera-50 border border-ampera-200 px-2 py-0.5 rounded">
                            {{ $konfigurasiAktif->parameter_minpts ?? 4 }} usaha
                        </span>
                    </div>
                    <input id="input-minpts" type="range" min="2" max="20" step="1" value="{{ $konfigurasiAktif->parameter_minpts ?? 4 }}" 
                           class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                    <div class="flex items-center gap-1 mt-1.5">
                        <span class="text-[10px] text-slate-400">Preset:</span>
                        <button type="button" class="btn-preset-minpts text-[10px] font-mono px-1.5 py-0.5 rounded bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 transition" data-val="3">3</button>
                        <button type="button" class="btn-preset-minpts text-[10px] font-mono px-1.5 py-0.5 rounded bg-ampera-50 border border-ampera-200 text-ampera-800 font-bold transition" data-val="4">4 (Resmi)</button>
                        <button type="button" class="btn-preset-minpts text-[10px] font-mono px-1.5 py-0.5 rounded bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 transition" data-val="6">6</button>
                        <button type="button" class="btn-preset-minpts text-[10px] font-mono px-1.5 py-0.5 rounded bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 transition" data-val="10">10</button>
                    </div>
                </div>

                <!-- Tombol Eksekusi & Reset -->
                <div class="pt-1 flex items-center space-x-2">
                    <button id="btn-jalankan-simulasi" type="button" 
                            class="flex-1 bg-ampera-800 hover:bg-ampera-900 active:scale-[0.99] text-white font-bold py-2 px-3 rounded-lg text-xs shadow-xs transition flex items-center justify-center space-x-1.5">
                        <svg id="icon-simulasi-play" class="w-3.5 h-3.5 text-ampera-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg id="icon-simulasi-loading" class="animate-spin w-3.5 h-3.5 text-white hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="text-tombol-simulasi">Deteksi Sentra</span>
                    </button>
                    <button id="btn-reset-param" type="button" title="Kembali ke Standar Acuan Dinas"
                            class="bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold py-2 px-3 rounded-lg text-xs transition">
                        Reset
                    </button>
                </div>
            </div>

            <!-- BAGIAN 2: RINGKASAN METRIK HASIL & SILHOUETTE SCORE -->
            <div class="p-3.5 bg-white space-y-2.5">
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-slate-50 border border-slate-200/90 p-2 rounded-lg">
                        <span class="block text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Sentra</span>
                        <strong id="kpi-total-sentra" class="text-sm font-bold text-slate-900 font-mono">0</strong>
                    </div>
                    <div class="bg-emerald-50/70 border border-emerald-200/80 p-2 rounded-lg">
                        <span class="block text-[10px] text-emerald-800 font-semibold uppercase tracking-wider">Berkelompok</span>
                        <strong id="kpi-total-clustered" class="text-sm font-bold text-emerald-700 font-mono">0</strong>
                    </div>
                    <div class="bg-slate-50 border border-slate-200/90 p-2 rounded-lg">
                        <span class="block text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Mandiri (Noise)</span>
                        <strong id="kpi-total-noise" class="text-sm font-bold text-slate-600 font-mono">0</strong>
                    </div>
                </div>

                <!-- Kotak Validitas Klaster: Silhouette Coefficient -->
                <div class="bg-slate-50 border border-slate-200/90 rounded-lg p-2.5 flex items-center justify-between text-xs">
                    <div>
                        <div class="flex items-center space-x-1.5">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Silhouette Score:</span>
                            <span id="kpi-silhouette-val" class="font-mono font-bold text-slate-900">-</span>
                        </div>
                        <span id="kpi-silhouette-label" class="block text-[10px] font-semibold text-slate-500 truncate max-w-[210px]">
                            Menunggu evaluasi...
                        </span>
                    </div>
                    <span id="kpi-execution-time" class="text-[10px] font-mono text-slate-400 bg-white border border-slate-200 px-1.5 py-0.5 rounded">
                        0 ms
                    </span>
                </div>
            </div>

            <!-- BAGIAN 3: DAFTAR KAWASAN SENTRA PARIWISATA (SCROLLABLE LIST) -->
            <div class="p-3.5 flex-1 flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Direktori Kawasan Sentra</span>
                    <span id="badge-total-sentra-list" class="text-[10px] font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">0 Kawasan</span>
                </div>

                <!-- Kontainer Daftar Kartu Sentra Vertikal -->
                <div id="daftar-sentra-list" class="space-y-1.5">
                    <div class="text-xs text-slate-400 italic py-3 text-center">Memuat sentra pariwisata...</div>
                </div>
            </div>

        </div>

    </aside>

    <!-- ============================================================== -->
    <!-- KANVAS PETA UTAMA KANAN (100% LUAS, BERSIH DARI TEKS TUMPUK)   -->
    <!-- ============================================================== -->
    <div class="flex-1 relative w-full h-full bg-slate-100">
        
        <!-- Peta Leaflet -->
        <div id="map-zonasi" class="w-full h-full"></div>

        <!-- Widget Legenda Spasial Peta (Kanan Bawah) -->
        <div id="widget-legenda-zonasi" 
             class="hidden sm:block absolute bottom-4 right-4 z-20 bg-white/95 backdrop-blur-md shadow-md rounded-xl border border-slate-200/90 p-3 text-xs w-64">
            <div class="flex items-center justify-between mb-2 pb-1 border-b border-slate-100">
                <span class="font-bold text-slate-800 text-[11px] uppercase tracking-wider">Legenda Pola Sentra</span>
                <span class="text-[10px] text-slate-400 font-mono">DBSCAN</span>
            </div>
            <div class="space-y-1.5 text-slate-600 text-[11px]">
                <div class="flex items-center space-x-2">
                    <span class="w-4 h-3 rounded border border-ampera-700 bg-ampera-700/20 inline-block"></span>
                    <span>Poligon Batas Sentra (Convex Hull)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-4 h-4 rounded-full bg-ampera-800 text-white font-bold text-[9px] flex items-center justify-center inline-block">#</span>
                    <span>Titik Pusat Sentra (Centroid Pin)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-ampera-800 border border-white shadow-xs inline-block"></span>
                    <span>Titik Inti (Core Point &ge; MinPts)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-ampera-600/70 border border-white inline-block"></span>
                    <span>Titik Batas (Border Point)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400 inline-block"></span>
                    <span>Usaha Mandiri (Pencilan / Noise)</span>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal Konfirmasi Kunci Konfigurasi Acuan Resmi (Khusus Akun Pimpinan) -->
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

@endsection

@section('scripts')
<script>
    /**
     * Engine Visualisasi Geospasial Pusat Usaha Wisata Kota Palembang
     * Layout: Split View (Sidebar Direktori Kiri + Peta Bebas Teks Tumpuk Kanan)
     * Algoritma: DBSCAN (Density-Based Spatial Clustering of Applications with Noise)
     * Batas Pola Kawasan: Monotone Chain Convex Hull Polygons
     * Pola Pusat: Centroid Pin Elegan
     */

    // Palet warna resmi 10 sentra pariwisata
    const PALET_KLASTER = [
        '#8B1E2D', // Sentra 1: Ampera Crimson
        '#1E40AF', // Sentra 2: Royal Blue
        '#047857', // Sentra 3: Musi Emerald
        '#B45309', // Sentra 4: Sriwijaya Amber
        '#6D28D9', // Sentra 5: Regal Violet
        '#0E7490', // Sentra 6: Steel Cyan
        '#BE185D', // Sentra 7: Dark Pink
        '#4D7C0F', // Sentra 8: Lime Green
        '#C2410C', // Sentra 9: Orange Rust
        '#4338CA', // Sentra 10: Indigo
    ];
    const WARNA_NOISE = '#64748B';
    const PALEMBANG_CENTER = [-2.983596, 104.7541449];
    const PALEMBANG_ZOOM = 12;

    // Status State
    let map = null;
    let layerConvexHulls = null;
    let layerCenterPins = null;
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

        map = L.map('map-zonasi', {
            center: PALEMBANG_CENTER,
            zoom: PALEMBANG_ZOOM,
            zoomControl: false
        });

        L.control.zoom({ position: 'topright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors | DPMPTSP Kota Palembang'
        }).addTo(map);

        // Layer group terstruktur
        layerConvexHulls = L.layerGroup().addTo(map);
        layerCenterPins = L.layerGroup().addTo(map);
        layerNoiseMarkers = L.layerGroup().addTo(map);
        layerBorderMarkers = L.layerGroup().addTo(map);
        layerCoreMarkers = L.layerGroup().addTo(map);

        setTimeout(() => {
            map.invalidateSize();
        }, 150);

        // Langsung jalankan deteksi awal dengan acuan resmi
        jalankanSimulasiDbscan();
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        inisialisasiPetaZonasi();
    } else {
        window.addEventListener('DOMContentLoaded', inisialisasiPetaZonasi);
    }

    // Registrasi Event Handlers
    document.addEventListener('DOMContentLoaded', function () {
        
        // 1. Slider Epsilon Live Update
        const sliderEps = document.getElementById('input-eps');
        const labelEps = document.getElementById('label-eps');
        const barEps = document.getElementById('bar-eps');
        if (sliderEps && labelEps) {
            sliderEps.addEventListener('input', function () {
                const meter = parseInt(this.value);
                labelEps.textContent = `${meter} meter`;
                if (barEps) barEps.textContent = `${meter}m`;
            });
        }

        document.querySelectorAll('.btn-preset-eps').forEach(btn => {
            btn.addEventListener('click', function () {
                const val = this.getAttribute('data-val');
                sliderEps.value = val;
                labelEps.textContent = `${val} meter`;
                if (barEps) barEps.textContent = `${val}m`;
            });
        });

        // 2. Slider MinPts Live Update
        const sliderMinPts = document.getElementById('input-minpts');
        const labelMinPts = document.getElementById('label-minpts');
        const barMinPts = document.getElementById('bar-minpts');
        if (sliderMinPts && labelMinPts) {
            sliderMinPts.addEventListener('input', function () {
                const pts = parseInt(this.value);
                labelMinPts.textContent = `${pts} usaha`;
                if (barMinPts) barMinPts.textContent = `${pts}`;
            });
        }

        document.querySelectorAll('.btn-preset-minpts').forEach(btn => {
            btn.addEventListener('click', function () {
                const val = this.getAttribute('data-val');
                sliderMinPts.value = val;
                labelMinPts.textContent = `${val} usaha`;
                if (barMinPts) barMinPts.textContent = `${val}`;
            });
        });

        // 3. Tombol Eksekusi Simulasi
        const btnSimulasi = document.getElementById('btn-jalankan-simulasi');
        if (btnSimulasi) {
            btnSimulasi.addEventListener('click', jalankanSimulasiDbscan);
        }

        // 4. Tombol Reset Parameter
        const btnReset = document.getElementById('btn-reset-param');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                sliderEps.value = 300;
                sliderMinPts.value = 4;
                labelEps.textContent = '300 meter';
                labelMinPts.textContent = '4 usaha';
                if (barEps) barEps.textContent = '300m';
                if (barMinPts) barMinPts.textContent = '4';

                document.getElementById('filter-kbli').value = '';
                document.getElementById('filter-kecamatan').value = '';

                const radBuka = document.querySelector('input[name="cakupan_data"][value="buka"]');
                if (radBuka) radBuka.checked = true;

                jalankanSimulasiDbscan();
            });
        }

        // 5. Toggle Buka / Tutup Sidebar Kiri
        const sidebar = document.getElementById('sidebar-zonasi');
        const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
        const btnCollapseSidebar = document.getElementById('btn-collapse-sidebar');
        const labelToggleSidebar = document.getElementById('label-toggle-sidebar');

        function setSidebarVisible(visible) {
            if (visible) {
                sidebar.classList.remove('hidden');
                if (labelToggleSidebar) labelToggleSidebar.textContent = 'Tutup Panel';
            } else {
                sidebar.classList.add('hidden');
                if (labelToggleSidebar) labelToggleSidebar.textContent = 'Buka Panel';
            }
            setTimeout(() => {
                if (map) map.invalidateSize();
            }, 100);
        }

        if (btnToggleSidebar) {
            btnToggleSidebar.addEventListener('click', function () {
                const isHidden = sidebar.classList.contains('hidden');
                setSidebarVisible(isHidden);
            });
        }

        if (btnCollapseSidebar) {
            btnCollapseSidebar.addEventListener('click', function () {
                setSidebarVisible(false);
            });
        }

        // 6. Tombol Pusatkan Peta
        const btnRecenter = document.getElementById('btn-recenter-map');
        if (btnRecenter) {
            btnRecenter.addEventListener('click', function () {
                if (map) {
                    map.flyTo(PALEMBANG_CENTER, PALEMBANG_ZOOM);
                }
            });
        }

        // 7. Modal Pimpinan
        inisialisasiModalPimpinan();
    });

    /**
     * Memanggil Endpoint API /api/simulasi-dbscan
     */
    function jalankanSimulasiDbscan() {
        const btnSimulasi = document.getElementById('btn-jalankan-simulasi');
        const iconPlay = document.getElementById('icon-simulasi-play');
        const iconLoading = document.getElementById('icon-simulasi-loading');
        const textTombol = document.getElementById('text-tombol-simulasi');

        const epsMeter = parseFloat(document.getElementById('input-eps').value);
        const minPts = parseInt(document.getElementById('input-minpts').value);
        const idKbli = document.getElementById('filter-kbli').value;
        const idKecamatan = document.getElementById('filter-kecamatan').value;
        const cakupanEl = document.querySelector('input[name="cakupan_data"]:checked');
        const cakupan = cakupanEl ? cakupanEl.value : 'buka';

        if (btnSimulasi) btnSimulasi.disabled = true;
        if (iconPlay) iconPlay.classList.add('hidden');
        if (iconLoading) iconLoading.classList.remove('hidden');
        if (textTombol) textTombol.textContent = 'Mengkalkulasi...';

        fetch('/api/simulasi-dbscan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
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
            if (!res.ok) throw new Error('Status respon ' + res.status);
            return res.json();
        })
        .then(res => {
            if (btnSimulasi) btnSimulasi.disabled = false;
            if (iconPlay) iconPlay.classList.remove('hidden');
            if (iconLoading) iconLoading.classList.add('hidden');
            if (textTombol) textTombol.textContent = 'Deteksi Sentra';

            if (!res.success) {
                alert('Peringatan: ' + (res.message || 'Kalkulasi gagal.'));
                return;
            }

            dataHasilTerakhir = res;
            renderHasilKlaster(res);
        })
        .catch(err => {
            if (btnSimulasi) btnSimulasi.disabled = false;
            if (iconPlay) iconPlay.classList.remove('hidden');
            if (iconLoading) iconLoading.classList.add('hidden');
            if (textTombol) textTombol.textContent = 'Deteksi Sentra';
            console.error('Error DBSCAN:', err);
            alert('Gagal memproses kalkulasi spasial: ' + err.message);
        });
    }

    /**
     * Merender poligon Convex Hull, center pin kompak, marker titik,
     * serta memperbarui daftar ranking direktori sentra di sidebar kiri.
     */
    function renderHasilKlaster(res) {
        layerConvexHulls.clearLayers();
        layerCenterPins.clearLayers();
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

        // Update KPI Sidebar
        document.getElementById('kpi-total-sentra').textContent = totalClusters;
        document.getElementById('kpi-total-clustered').textContent = `${totalClustered.toLocaleString('id-ID')} (${pctClustered}%)`;
        document.getElementById('kpi-total-noise').textContent = `${totalNoise.toLocaleString('id-ID')} (${pctNoise}%)`;
        document.getElementById('kpi-execution-time').textContent = `${res.execution_time_ms} ms`;
        document.getElementById('badge-total-sentra-list').textContent = `${totalClusters} Kawasan`;

        // Update Status Sub-navbar
        const barStatusText = document.getElementById('teks-status-acuan');
        if (barStatusText) {
            barStatusText.textContent = `${totalClusters} Sentra Terdeteksi (${totalClustered} Berkelompok)`;
        }

        // Update Silhouette Score
        const valSilEl = document.getElementById('kpi-silhouette-val');
        const lblSilEl = document.getElementById('kpi-silhouette-label');
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
                lblSilEl.textContent = res.silhouette_label || 'Kawasan Cenderung Rapat / Menyambung';
            }
        } else {
            valSilEl.textContent = 'N/A';
            lblSilEl.className = 'block text-[10px] font-semibold text-slate-400 truncate';
            lblSilEl.textContent = 'Minimal 2 sentra untuk evaluasi';
        }

        // Render Poligon & Pola Pusat (Center Pin)
        const clusters = res.clusters || {};
        const clusterIds = Object.keys(clusters).map(Number).sort((a, b) => a - b);

        clusterIds.forEach(cId => {
            const cData = clusters[cId];
            const warnaIdx = (cId - 1) % PALET_KLASTER.length;
            const warnaKlaster = PALET_KLASTER[warnaIdx];

            // 1. Poligon Batas Sentra (Convex Hull)
            if (cData.convex_hull && cData.convex_hull.length >= 3) {
                const polygon = L.polygon(cData.convex_hull, {
                    color: warnaKlaster,
                    weight: 2.5,
                    opacity: 0.9,
                    fillColor: warnaKlaster,
                    fillOpacity: 0.20,
                    dashArray: '4, 4'
                }).addTo(layerConvexHulls);

                polygon.on('mouseover', function () {
                    this.setStyle({ fillOpacity: 0.38, weight: 3.5 });
                });
                polygon.on('mouseout', function () {
                    this.setStyle({ fillOpacity: 0.20, weight: 2.5 });
                });

                const ringkasanSektor = Object.entries(cData.sektor_ringkasan || {})
                    .map(([sektor, jml]) => `<li class="flex justify-between py-0.5"><span class="truncate pr-2">${sektor}</span><strong class="font-mono text-slate-800">${jml}</strong></li>`)
                    .join('');

                const popupPoligon = `
                    <div class="p-3 text-xs max-w-xs font-sans">
                        <div class="flex items-center space-x-2 mb-2 pb-1.5 border-b border-slate-100">
                            <span class="w-3.5 h-3.5 rounded-full" style="background-color: ${warnaKlaster}"></span>
                            <h4 class="font-bold text-sm text-slate-900">Sentra Pariwisata #${cId}</h4>
                        </div>
                        <div class="space-y-1 mb-2.5">
                            <p class="text-slate-600"><span class="font-semibold text-slate-700">Sektor Dominan:</span> ${cData.sektor_dominan}</p>
                            <p class="text-slate-600"><span class="font-semibold text-slate-700">Total Anggota:</span> <strong class="font-mono text-slate-900">${cData.total_titik}</strong> usaha (${cData.total_core} Core, ${cData.total_border} Border)</p>
                        </div>
                        <div class="bg-slate-50 p-2 rounded-lg border border-slate-200/80">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Komposisi Sektor Teratas:</span>
                            <ul class="text-[11px] text-slate-600 divide-y divide-slate-100">
                                ${ringkasanSektor}
                            </ul>
                        </div>
                    </div>
                `;
                polygon.bindPopup(popupPoligon);
                poligonSentraMap[cId] = polygon;
            }

            // 2. Pola Pusat Sentra (Pin Kompak Berdiameter 26px - Bebas dari Teks Bertumpuk)
            if (cData.center && cData.center.latitude && cData.center.longitude) {
                const pinIcon = L.divIcon({
                    className: 'sentra-center-pin-marker',
                    html: `
                        <div class="sentra-center-pin" style="background-color: ${warnaKlaster};" title="Sentra #${cId}: ${cData.sektor_dominan} (${cData.total_titik} usaha)">
                            <span>${cId}</span>
                        </div>
                    `,
                    iconSize: [26, 26],
                    iconAnchor: [13, 13]
                });

                const pinMarker = L.marker([cData.center.latitude, cData.center.longitude], { icon: pinIcon }).addTo(layerCenterPins);
                
                // Tooltip informatif saat pin di-hover
                pinMarker.bindTooltip(`<strong>Sentra #${cId}</strong>: ${cData.sektor_dominan} (${cData.total_titik} usaha)`, {
                    direction: 'top',
                    offset: [0, -10]
                });

                pinMarker.on('click', () => fokusKeSentra(cId));
            }
        });

        // 3. Render Titik Usaha (Core, Border, Noise)
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

        // 4. Render Daftar Ranking Sentra di Sidebar Kiri
        renderDaftarSentraSidebar(clusters, totalPoints);
    }

    /**
     * Merender daftar ranking sentra pariwisata di sidebar kiri
     */
    function renderDaftarSentraSidebar(clusters, totalPoints) {
        const kontainer = document.getElementById('daftar-sentra-list');
        if (!kontainer) return;
        kontainer.innerHTML = '';

        const clusterIds = Object.keys(clusters).map(Number).sort((a, b) => a - b);

        if (clusterIds.length === 0) {
            kontainer.innerHTML = '<div class="text-xs text-slate-400 italic py-3 text-center">Tidak ada sentra yang memenuhi kerapatan pada parameter ini.</div>';
            return;
        }

        clusterIds.forEach(cId => {
            const cData = clusters[cId];
            const warnaIdx = (cId - 1) % PALET_KLASTER.length;
            const warna = PALET_KLASTER[warnaIdx];
            const porsiKota = totalPoints > 0 ? ((cData.total_titik / totalPoints) * 100).toFixed(1) : '0';

            const item = document.createElement('div');
            item.className = 'item-sentra p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/80 transition duration-150 cursor-pointer flex items-center justify-between group';
            item.dataset.clusterId = cId;
            item.onclick = () => fokusKeSentra(cId);

            item.innerHTML = `
                <div class="flex items-center space-x-2.5 min-w-0">
                    <span class="w-6 h-6 rounded-full text-white font-bold text-[10px] flex items-center justify-center shrink-0 shadow-2xs" style="background-color: ${warna}">
                        ${cId}
                    </span>
                    <div class="min-w-0">
                        <h4 class="text-xs font-bold text-slate-800 truncate leading-snug group-hover:text-ampera-800 transition" title="${cData.sektor_dominan}">
                            ${cData.sektor_dominan}
                        </h4>
                        <div class="flex items-center space-x-2 text-[10px] text-slate-500 font-mono">
                            <span class="text-emerald-700">${cData.total_core} Core</span>
                            <span>•</span>
                            <span class="text-amber-700">${cData.total_border} Border</span>
                        </div>
                    </div>
                </div>
                <div class="text-right shrink-0 pl-2">
                    <span class="font-mono text-xs font-bold text-slate-800">${cData.total_titik} <span class="text-[10px] font-normal text-slate-500">usaha</span></span>
                    <span class="block text-[10px] text-slate-400 font-mono">${porsiKota}% kota</span>
                </div>
            `;

            kontainer.appendChild(item);
        });
    }

    /**
     * Memfokuskan peta ke poligon sentra tertentu secara halus
     */
    function fokusKeSentra(clusterId) {
        if (!map) return;

        // Highlight baris di sidebar
        document.querySelectorAll('.item-sentra').forEach(el => {
            if (el.dataset.clusterId == clusterId) {
                el.classList.add('border-ampera-600', 'bg-ampera-50/50');
            } else {
                el.classList.remove('border-ampera-600', 'bg-ampera-50/50');
            }
        });

        const polygon = poligonSentraMap[clusterId];
        if (polygon) {
            map.fitBounds(polygon.getBounds(), {
                padding: [50, 50],
                maxZoom: 16,
                animate: true,
                duration: 0.8
            });
            setTimeout(() => {
                polygon.openPopup();
            }, 400);
        } else if (dataHasilTerakhir && dataHasilTerakhir.clusters[clusterId]) {
            const center = dataHasilTerakhir.clusters[clusterId].center;
            if (center && center.latitude && center.longitude) {
                map.setView([center.latitude, center.longitude], 15, { animate: true });
            }
        }
    }

    /**
     * Logika Modal Pimpinan
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
                alert('Silakan jalankan deteksi sentra terlebih dahulu.');
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
                        alert('Gagal: ' + (res.message || 'Terjadi kesalahan sistem.'));
                    }
                })
                .catch(err => {
                    btnSubmit.disabled = false;
                    if (spinner) spinner.classList.add('hidden');
                    if (teksSubmit) teksSubmit.textContent = 'Konfirmasi & Kunci Resmi';
                    console.error('Error saat mengunci:', err);
                    alert('Gagal menghubungi server: ' + err.message);
                });
            });
        }
    }
</script>
@endsection
