@extends('layouts.app')

@section('title', 'Peta Sebaran Usaha Pariwisata — Kota Palembang')

@section('no-footer', true)

@section('styles')
<!-- Leaflet MarkerCluster CSS -->
<link rel="stylesheet" href="{{ asset('vendor/leaflet/MarkerCluster.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/leaflet/MarkerCluster.Default.css') }}">

<style>
    /* Mengunci layar agar jendela peramban tidak memiliki scrollbar ganda di luar peta */
    html, body {
        height: 100%;
        overflow: hidden;
    }

    /* Peta selalu membentang penuh 100% mengikuti kontainer kanvas */
    #map-sebaran, #map-choropleth {
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

    /* Custom Leaflet MarkerCluster Styling - Palet Ampera */
    .marker-cluster-small {
        background-color: rgba(254, 226, 226, 0.7);
    }
    .marker-cluster-small div {
        background-color: #8B1E2D;
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        font-size: 12px;
    }
    .marker-cluster-medium {
        background-color: rgba(254, 202, 202, 0.75);
    }
    .marker-cluster-medium div {
        background-color: #741D29;
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        font-size: 13px;
    }
    .marker-cluster-large {
        background-color: rgba(239, 68, 68, 0.3);
    }
    .marker-cluster-large div {
        background-color: #55121B;
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 14px;
    }

    /* Custom popup styling */
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

    /* Custom scrollbar for place directory */
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
</style>
@endsection

@section('content')
<!-- Hidden Config & Server Data Store (Safe JSON passing) -->
<div id="peta-meta" 
     class="hidden"
     data-initial-query="{{ $initialQuery }}"
     data-initial-kecamatan="{{ $initialKecamatan }}"
     data-initial-kbli="{{ $initialKbli }}"
     data-initial-status="{{ $initialStatus }}"
     data-api-titik-url="{{ url('api/titik-usaha') }}"
     data-geojson-url="{{ asset('geojson/palembang_kecamatan.geojson') }}"
     data-total-verified="{{ $totalUsahaVerified }}">
</div>

<!-- Embedded Server Datasets for Zero-Latency Instant Rendering -->
<script id="server-titik-usaha" type="application/json">
    {!! json_encode($daftarTitikUsaha) !!}
</script>
<script id="server-statistik-kecamatan" type="application/json">
    {!! json_encode($statistikKecamatan) !!}
</script>

<!-- Subheader & Toolbar Navigasi Peta -->
<div class="bg-slate-900 text-white border-b border-slate-800 z-30 shrink-0">
    <div class="px-4 py-2.5 flex flex-wrap items-center justify-between gap-3">
        
        <!-- Left: Judul & KPI Status Cepat -->
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-ampera-900 border border-white/20 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-sm sm:text-base font-bold text-white tracking-tight leading-none">Peta Sebaran Usaha Pariwisata</h1>
                    <span class="text-[10px] bg-white/10 text-slate-300 px-2 py-0.5 rounded border border-white/10 font-mono hidden sm:inline-block">1.421 Titik</span>
                </div>
                <div class="flex items-center space-x-2 text-[11px] text-slate-300 mt-1">
                    <span class="inline-flex items-center space-x-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="text-white font-medium">{{ number_format($totalUsahaBuka, 0, ',', '.') }} Buka</span>
                    </span>
                    <span class="text-slate-500">•</span>
                    <span class="inline-flex items-center space-x-1">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                        <span>{{ number_format($totalUsahaTutupPermanen, 0, ',', '.') }} Tutup Permanen</span>
                    </span>
                    <span class="text-slate-500">•</span>
                    <span class="inline-flex items-center space-x-1">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span>{{ number_format($totalUsahaTutupSementara, 0, ',', '.') }} Sementara</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Center: Segmented Control Tabs (Mode Switcher) -->
        <div class="flex items-center bg-slate-800/90 p-1 rounded-xl border border-slate-700">
            <!-- Tab 1: Sebaran Titik Usaha -->
            <button id="btn-tab-sebaran" 
                    type="button" 
                    class="tab-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center space-x-1.5 transition duration-150 bg-ampera-900 text-white shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                <span>Sebaran Titik Usaha</span>
            </button>

            <!-- Tab 2: Kepadatan per Kecamatan (Choropleth) -->
            <button id="btn-tab-choropleth" 
                    type="button" 
                    class="tab-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center space-x-1.5 transition duration-150 text-slate-300 hover:text-white hover:bg-slate-700/60">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span>Kepadatan per Wilayah</span>
            </button>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center space-x-2">
            <!-- Mobile Toggle Sidebar Button -->
            <button id="btn-toggle-sidebar" 
                    type="button" 
                    class="md:hidden px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium border border-slate-700 flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
                <span>Filter & Daftar</span>
            </button>

            <!-- Reset Map View Button -->
            <button id="btn-recenter-map" 
                    type="button" 
                    title="Pusatkan Peta ke Kota Palembang" 
                    class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-medium border border-slate-700 flex items-center space-x-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="hidden sm:inline">Pusatkan Peta</span>
            </button>
        </div>

    </div>
</div>

<!-- Main Interactive Split View Container -->
<div class="flex-1 min-h-0 flex relative overflow-hidden w-full">

    <!-- ============================================================== -->
    <!-- PANEL TAB 1: SEBARAN TITIK USAHA (Sidebar & Cluster Map)       -->
    <!-- ============================================================== -->
    <div id="view-tab-sebaran" class="flex-1 flex w-full h-full min-h-0 overflow-hidden">
        
        <!-- Left Sidebar: Filter & Interactive Directory List -->
        <aside id="sidebar-sebaran" 
               class="w-full md:w-96 lg:w-104 bg-white border-r border-slate-200 flex flex-col h-full min-h-0 z-20 shrink-0 shadow-sm transition-all duration-200">
            
            <!-- Top Controls Box -->
            <div class="p-3.5 border-b border-slate-200 bg-slate-50/70 space-y-2.5 shrink-0">
                
                <!-- Live Search Box -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input id="input-pencarian-sebaran" 
                           type="text" 
                           value="{{ $initialQuery }}"
                           placeholder="Cari nama usaha, NIB, alamat..." 
                           class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-slate-300 bg-white placeholder-slate-400 focus:outline-none focus:border-ampera-900 focus:ring-1 focus:ring-ampera-900 transition">
                    <button id="btn-clear-search" 
                            type="button" 
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 {{ $initialQuery ? '' : 'hidden' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Status Operasional Filter Pills -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Status Operasional</label>
                    <div class="grid grid-cols-4 gap-1">
                        <button type="button" 
                                data-status="all" 
                                class="btn-filter-status text-[11px] py-1 px-1.5 rounded font-medium border text-center transition {{ $initialStatus === 'all' || !$initialStatus ? 'bg-ampera-900 text-white border-ampera-900 font-semibold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                            Semua
                        </button>
                        <button type="button" 
                                data-status="buka" 
                                class="btn-filter-status text-[11px] py-1 px-1.5 rounded font-medium border text-center transition {{ $initialStatus === 'buka' ? 'bg-ampera-900 text-white border-ampera-900 font-semibold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                            Buka
                        </button>
                        <button type="button" 
                                data-status="tutup_sementara" 
                                class="btn-filter-status text-[11px] py-1 px-1.5 rounded font-medium border text-center transition {{ $initialStatus === 'tutup_sementara' ? 'bg-ampera-900 text-white border-ampera-900 font-semibold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                            Sementara
                        </button>
                        <button type="button" 
                                data-status="tutup_permanen" 
                                class="btn-filter-status text-[11px] py-1 px-1.5 rounded font-medium border text-center transition {{ $initialStatus === 'tutup_permanen' ? 'bg-ampera-900 text-white border-ampera-900 font-semibold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                            Permanen
                        </button>
                    </div>
                </div>

                <!-- Dropdown Sektor & Kecamatan -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="select-kbli" class="block text-[11px] font-semibold text-slate-500 mb-1">Kategori Sektor</label>
                        <select id="select-kbli" class="w-full text-xs rounded-lg border border-slate-300 bg-white px-2 py-1.5 focus:border-ampera-900 focus:ring-1 focus:ring-ampera-900 outline-none text-slate-700">
                            <option value="">Semua Sektor Usaha</option>
                            @foreach($daftarKbli as $k)
                                <option value="{{ $k->id_kbli }}" {{ $initialKbli == $k->id_kbli ? 'selected' : '' }}>
                                    {{ $k->judul_kbli }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="select-kecamatan" class="block text-[11px] font-semibold text-slate-500 mb-1">Kecamatan</label>
                        <select id="select-kecamatan" class="w-full text-xs rounded-lg border border-slate-300 bg-white px-2 py-1.5 focus:border-ampera-900 focus:ring-1 focus:ring-ampera-900 outline-none text-slate-700">
                            <option value="">Semua Kecamatan (18)</option>
                            @foreach($daftarKecamatan as $kc)
                                <option value="{{ $kc->id_kecamatan }}" {{ $initialKecamatan == $kc->id_kecamatan ? 'selected' : '' }}>
                                    {{ $kc->nama_kecamatan }} ({{ $kc->total_verified }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filter Counter & Reset Action -->
                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200/80">
                    <span class="text-slate-500">
                        Menampilkan <strong id="count-tampil" class="text-slate-800">0</strong> tempat usaha
                    </span>
                    <button id="btn-reset-filters" type="button" class="text-ampera-900 hover:underline text-[11px] font-semibold">
                        Reset Filter
                    </button>
                </div>

            </div>

            <!-- Scrollable Results Place Directory -->
            <div id="container-daftar-usaha" class="flex-1 min-h-0 overflow-y-auto custom-scrollbar p-3 space-y-2">
                <div id="loading-daftar-usaha" class="py-12 text-center text-slate-400">
                    <svg class="animate-spin w-6 h-6 mx-auto mb-2 text-ampera-900" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-xs">Memuat direktori tempat usaha...</p>
                </div>
            </div>

        </aside>

        <!-- Right: Leaflet Map Canvas -->
        <main class="flex-1 relative w-full h-full min-h-0 bg-slate-100 overflow-hidden">
            <div id="map-sebaran"></div>

            <!-- Loading overlay for map operations -->
            <div id="map-loading-indicator" class="hidden absolute top-4 left-4 bg-white/90 backdrop-blur-md border border-slate-300 shadow-lg px-3.5 py-2 rounded-lg z-30 items-center space-x-2 text-xs font-medium text-slate-800">
                <svg class="animate-spin w-4 h-4 text-ampera-900" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Sinkronisasi data spasial...</span>
            </div>

            <!-- Map Floating Legend (Bottom Right) -->
            <div class="absolute bottom-6 right-6 bg-white/95 backdrop-blur-sm border border-slate-200/90 shadow-xl rounded-xl p-3 z-20 text-xs text-slate-700 max-w-xs pointer-events-auto">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200">
                    <span class="font-bold text-[11px] uppercase tracking-wider text-slate-800">Legenda Peta</span>
                    <span class="text-[10px] text-slate-400 font-mono">OSS-RBA</span>
                </div>
                <div class="space-y-1.5 text-[11px]">
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 border border-white shadow-sm shrink-0"></span>
                        <span class="text-slate-700">Buka / Aktif Beroperasi</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500 border border-white shadow-sm shrink-0"></span>
                        <span class="text-slate-700">Tutup Sementara</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500 border border-white shadow-sm shrink-0"></span>
                        <span class="text-slate-700">Tutup Permanen</span>
                    </div>
                    <div class="flex items-center space-x-2 pt-1 border-t border-slate-100">
                        <span class="w-4 h-4 rounded-full bg-ampera-900 border border-white flex items-center justify-center text-[8px] text-white font-bold shrink-0">N</span>
                        <span class="text-slate-500">Klaster Titik Terpusat (Klik untuk melebarkan)</span>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ============================================================== -->
    <!-- PANEL TAB 2: KEPADATAN PER KECAMATAN (Choropleth Peta Tematik) -->
    <!-- ============================================================== -->
    <div id="view-tab-choropleth" class="hidden flex-1 w-full h-full min-h-0 overflow-hidden">
        
        <!-- Left Sidebar: Ranking Kepadatan 18 Wilayah -->
        <aside class="w-full md:w-96 lg:w-104 bg-white border-r border-slate-200 flex flex-col h-full min-h-0 z-20 shrink-0 shadow-sm">
            <div class="p-3.5 border-b border-slate-200 bg-slate-50/70 shrink-0">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">Peringkat Kepadatan Usaha</h2>
                        <p class="text-[11px] text-slate-500">Distribusi 1.421 usaha di 18 kecamatan</p>
                    </div>
                    <span class="text-[10px] bg-ampera-900/10 text-ampera-900 font-bold px-2 py-0.5 rounded-full">18 Wilayah</span>
                </div>
            </div>

            <!-- List 18 Kecamatan Ranked by Density -->
            <div id="list-ranking-kecamatan" class="flex-1 min-h-0 overflow-y-auto custom-scrollbar divide-y divide-slate-100 p-2 space-y-1">
                @php
                    $sortedKecamatans = collect($statistikKecamatan)->sortByDesc('total')->values();
                @endphp
                @foreach($sortedKecamatans as $idx => $item)
                    <div class="item-kecamatan p-2.5 rounded-lg hover:bg-slate-50 transition cursor-pointer group"
                         data-id-kecamatan="{{ $item['id_kecamatan'] }}">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center space-x-2">
                                <span class="w-5 h-5 rounded flex items-center justify-center text-[10px] font-bold {{ $idx < 3 ? 'bg-ampera-900 text-white' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $idx + 1 }}
                                </span>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-ampera-900 transition">
                                    {{ $item['nama_kecamatan'] }}
                                </span>
                            </div>
                            <span class="text-xs font-bold font-mono text-slate-900">
                                {{ number_format($item['total'], 0, ',', '.') }} <span class="text-[10px] font-normal text-slate-400">usaha</span>
                            </span>
                        </div>
                        
                        <!-- Progress Bar Kepadatan -->
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden mb-1.5">
                            @php
                                $pctBar = round(($item['total'] / 263) * 100, 1);
                            @endphp
                            <div class="bar-kepadatan h-1.5 rounded-full bg-ampera-900" data-pct="{{ $pctBar }}"></div>
                        </div>

                        <div class="flex items-center justify-between text-[10px] text-slate-500">
                            <span>{{ $item['persentase'] }}% dari total kota</span>
                            <span class="text-emerald-700 font-medium">{{ $item['buka'] }} Buka</span>
                            @if($item['tutup'] > 0)
                                <span class="text-rose-700 font-medium">{{ $item['tutup'] }} Tutup</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>

        <!-- Right: Leaflet Choropleth Map Canvas -->
        <main class="flex-1 relative w-full h-full min-h-0 bg-slate-100 overflow-hidden">
            <div id="map-choropleth"></div>

            <!-- Floating Info Panel on Hover (Top Right) -->
            <div id="choropleth-info-panel" class="absolute top-4 right-4 bg-white/95 backdrop-blur-md border border-slate-300 shadow-xl rounded-xl p-4 z-20 max-w-sm pointer-events-auto transition duration-150">
                <div class="text-slate-400 text-xs italic flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Arahkan kursor atau klik wilayah kecamatan untuk melihat profil kepadatan.</span>
                </div>
            </div>

            <!-- Choropleth Legend (Bottom Right) -->
            <div class="absolute bottom-6 right-6 bg-white/95 backdrop-blur-sm border border-slate-200/90 shadow-xl rounded-xl p-3 z-20 text-xs text-slate-700 max-w-xs pointer-events-auto">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200">
                    <span class="font-bold text-[11px] uppercase tracking-wider text-slate-800">Skala Kepadatan Usaha</span>
                </div>
                <div class="space-y-1.5 text-[11px]">
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-3 rounded-sm border border-black/10 shrink-0" style="background-color: #8B1E2D;"></span>
                        <span class="text-slate-700">> 200 Usaha (Sangat Padat)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-3 rounded-sm border border-black/10 shrink-0" style="background-color: #B32E3F;"></span>
                        <span class="text-slate-700">100 - 200 Usaha (Padat)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-3 rounded-sm border border-black/10 shrink-0" style="background-color: #D9534F;"></span>
                        <span class="text-slate-700">50 - 100 Usaha (Sedang)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-3 rounded-sm border border-black/10 shrink-0" style="background-color: #EAA4A4;"></span>
                        <span class="text-slate-700">20 - 50 Usaha (Berkembang)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-3 rounded-sm border border-black/10 shrink-0" style="background-color: #F7D6D6;"></span>
                        <span class="text-slate-700">< 20 Usaha (Rendah)</span>
                    </div>
                </div>
            </div>

        </main>
    </div>

</div>
@endsection

@section('scripts')
<!-- Leaflet MarkerCluster Plugin JS -->
<script src="{{ asset('vendor/leaflet/leaflet.markercluster.js') }}"></script>

<script>
    // Palembang default geodetic center & initial zoom
    const PALEMBANG_CENTER = [-2.976073, 104.757833];
    const PALEMBANG_ZOOM = 12;

    // Load static config from DOM safely
    const metaEl = document.getElementById('peta-meta');
    const geojsonUrl = metaEl ? metaEl.dataset.geojsonUrl : '';
    const apiTitikUrl = metaEl ? metaEl.dataset.apiTitikUrl : '';
    const initialQuery = metaEl ? (metaEl.dataset.initialQuery || '') : '';
    const initialKecamatan = metaEl ? (metaEl.dataset.initialKecamatan || '') : '';
    const initialKbli = metaEl ? (metaEl.dataset.initialKbli || '') : '';
    const initialStatus = metaEl ? (metaEl.dataset.initialStatus || 'all') : 'all';

    let dataStatistikKecamatan = {};
    try {
        const statsScript = document.getElementById('server-statistik-kecamatan');
        if (statsScript && statsScript.textContent.trim()) {
            dataStatistikKecamatan = JSON.parse(statsScript.textContent);
        }
    } catch (e) {
        console.error('Gagal membaca data statistik kecamatan:', e);
    }

    // App state
    let semuaDataTitik = [];
    let dataTerfilter = [];
    let mapSebaran = null;
    let clusterGroup = null;
    let markersById = {};
    let activeTab = 'sebaran';

    // Choropleth Map State
    let mapChoropleth = null;
    let geojsonLayer = null;
    let rawGeojsonData = null;

    // SVG Marker Icons Generator
    function createCustomMarkerIcon(status) {
        let color = '#10B981'; // Emerald for Buka
        if (status === 'Tutup Sementara') {
            color = '#F59E0B'; // Amber
        } else if (status === 'Tutup Permanen') {
            color = '#EF4444'; // Red
        }

        const svgHtml = `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 32" width="24" height="32" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.35));">
                <path fill="${color}" stroke="#FFFFFF" stroke-width="1.5" d="M12 0C5.37 0 0 5.37 0 12c0 8.5 12 20 12 20s12-11.5 12-20c0-6.63-5.37-12-12-12z"/>
                <circle cx="12" cy="12" r="4.5" fill="#FFFFFF"/>
            </svg>
        `;

        return L.divIcon({
            html: svgHtml,
            className: 'custom-pin-marker',
            iconSize: [24, 32],
            iconAnchor: [12, 32],
            popupAnchor: [0, -30]
        });
    }

    // Initialize Map Sebaran (Tab 1)
    function inisialisasiPetaSebaran() {
        if (mapSebaran || !document.getElementById('map-sebaran')) return;

        mapSebaran = L.map('map-sebaran', {
            center: PALEMBANG_CENTER,
            zoom: PALEMBANG_ZOOM,
            zoomControl: true,
            maxZoom: 19
        });

        // OpenStreetMap Standard Tile Layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | Dinas Pariwisata Kota Palembang',
            subdomains: ['a', 'b', 'c'],
            maxZoom: 19
        }).addTo(mapSebaran);

        // Marker Cluster Group with custom radius & safe fallback
        if (typeof L.markerClusterGroup === 'function') {
            clusterGroup = L.markerClusterGroup({
                showCoverageOnHover: false,
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                zoomToBoundsOnClick: true,
                chunkedLoading: true
            });
        } else {
            console.warn('L.markerClusterGroup tidak tersedia, menggunakan L.layerGroup() biasa.');
            clusterGroup = L.layerGroup();
        }
        mapSebaran.addLayer(clusterGroup);

        setTimeout(() => {
            if (mapSebaran) mapSebaran.invalidateSize();
        }, 150);
        setTimeout(() => {
            if (mapSebaran) mapSebaran.invalidateSize();
        }, 500);
    }

    // Initialize Map Choropleth (Tab 2)
    function inisialisasiPetaChoropleth() {
        if (mapChoropleth || !document.getElementById('map-choropleth')) return;

        mapChoropleth = L.map('map-choropleth', {
            center: PALEMBANG_CENTER,
            zoom: PALEMBANG_ZOOM,
            zoomControl: true,
            maxZoom: 18
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors | Peta Tematik Palembang',
            subdomains: ['a', 'b', 'c'],
            maxZoom: 19
        }).addTo(mapChoropleth);

        muatDataGeojsonKecamatan();

        setTimeout(() => {
            if (mapChoropleth) mapChoropleth.invalidateSize();
        }, 150);
        setTimeout(() => {
            if (mapChoropleth) mapChoropleth.invalidateSize();
        }, 500);
    }

    // Color gradient scale based on total businesses
    function getChoroplethColor(total) {
        if (total > 200) return '#8B1E2D'; // Tier 5: > 200 (Ampera Maroon)
        if (total >= 100) return '#B32E3F'; // Tier 4: 100 - 200
        if (total >= 50) return '#D9534F';  // Tier 3: 50 - 100
        if (total >= 20) return '#EAA4A4';  // Tier 2: 20 - 50
        return '#F7D6D6';                   // Tier 1: < 20
    }

    // Fetch GeoJSON and render polygons
    function muatDataGeojsonKecamatan() {
        if (rawGeojsonData) {
            renderChoroplethLayers(rawGeojsonData);
            return;
        }

        if (!geojsonUrl) return;

        fetch(geojsonUrl)
            .then(res => res.json())
            .then(data => {
                rawGeojsonData = data;
                renderChoroplethLayers(data);
            })
            .catch(err => {
                console.error('Gagal memuat GeoJSON kecamatan:', err);
            });
    }

    function renderChoroplethLayers(geojson) {
        if (geojsonLayer && mapChoropleth) {
            mapChoropleth.removeLayer(geojsonLayer);
        }

        geojsonLayer = L.geoJSON(geojson, {
            style: function(feature) {
                const idKec = feature.properties.id_kecamatan;
                const stats = dataStatistikKecamatan[idKec] || { total: 0 };
                const color = getChoroplethColor(stats.total);

                return {
                    fillColor: color,
                    weight: 2,
                    opacity: 1,
                    color: '#FFFFFF',
                    fillOpacity: 0.82
                };
            },
            onEachFeature: function(feature, layer) {
                const idKec = feature.properties.id_kecamatan;
                const stats = dataStatistikKecamatan[idKec] || {
                    id_kecamatan: idKec,
                    nama_kecamatan: feature.properties.nama_kecamatan || 'Kecamatan',
                    total: 0,
                    persentase: 0,
                    buka: 0,
                    tutup: 0,
                    tutup_permanen: 0,
                    tutup_sementara: 0,
                    sektor: {}
                };

                // Tooltip interaktif
                layer.bindTooltip(`
                    <div class="text-xs p-0.5 font-sans leading-tight">
                        <strong class="text-slate-900">${stats.nama_kecamatan}</strong><br>
                        <span class="text-red-800 font-bold">${stats.total} tempat usaha</span> (${stats.persentase}%)
                    </div>
                `, {
                    sticky: true,
                    direction: 'auto',
                    opacity: 0.95
                });

                layer.on({
                    mouseover: function(e) {
                        const l = e.target;
                        l.setStyle({
                            weight: 3.5,
                            color: '#741D29',
                            fillOpacity: 0.95
                        });
                        l.bringToFront();
                        updateChoroplethInfo(stats);
                    },
                    mouseout: function(e) {
                        if (geojsonLayer) geojsonLayer.resetStyle(e.target);
                    },
                    click: function(e) {
                        mapChoropleth.fitBounds(e.target.getBounds());
                        updateChoroplethInfo(stats, true);
                    }
                });
            }
        }).addTo(mapChoropleth);

        // Otomatis pusatkan seluruh 18 kecamatan Kota Palembang
        try {
            const bounds = geojsonLayer.getBounds();
            if (bounds && bounds.isValid()) {
                mapChoropleth.fitBounds(bounds, { padding: [20, 20] });
            }
        } catch (e) {
            // ignore
        }
    }

    // Update floating info panel in Tab 2
    function updateChoroplethInfo(stats, isClick = false) {
        const panel = document.getElementById('choropleth-info-panel');
        if (!panel) return;

        let sektorHtml = '';
        if (stats.sektor && Object.keys(stats.sektor).length > 0) {
            const topSektors = Object.entries(stats.sektor).slice(0, 3);
            sektorHtml = topSektors.map(([name, count]) => `
                <div class="flex items-center justify-between text-[11px] text-slate-600 py-0.5">
                    <span class="truncate max-w-[190px]">${name}</span>
                    <span class="font-bold text-slate-800 font-mono">${count}</span>
                </div>
            `).join('');
        } else {
            sektorHtml = '<p class="text-[11px] text-slate-400 italic">Belum ada rincian sektor.</p>';
        }

        panel.innerHTML = `
            <div class="space-y-3">
                <div class="flex items-start justify-between border-b border-slate-200 pb-2">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-ampera-900 bg-ampera-900/10 px-2 py-0.5 rounded">Wilayah Kecamatan</span>
                        <h3 class="font-bold text-base text-slate-900 mt-1">${stats.nama_kecamatan}</h3>
                    </div>
                    <span class="font-mono text-sm font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg">
                        ${stats.total} <span class="text-[10px] font-normal text-slate-500">usaha</span>
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-1.5 text-center">
                    <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                        <div class="text-[10px] text-slate-500">Porsi Kota</div>
                        <div class="text-xs font-bold text-slate-800 font-mono mt-0.5">${stats.persentase}%</div>
                    </div>
                    <div class="p-2 bg-emerald-50 rounded-lg border border-emerald-100">
                        <div class="text-[10px] text-emerald-700">Aktif Buka</div>
                        <div class="text-xs font-bold text-emerald-800 font-mono mt-0.5">${stats.buka}</div>
                    </div>
                    <div class="p-2 bg-rose-50 rounded-lg border border-rose-100">
                        <div class="text-[10px] text-rose-700">Tutup</div>
                        <div class="text-xs font-bold text-rose-800 font-mono mt-0.5">${stats.tutup}</div>
                    </div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-slate-700 mb-1">Komposisi Sektor Teratas:</div>
                    <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 divide-y divide-slate-100">
                        ${sektorHtml}
                    </div>
                </div>

                <button type="button" 
                        onclick="alihkanKeTabSebaranFilter(${stats.id_kecamatan})"
                        class="w-full bg-ampera-900 hover:bg-ampera-800 text-white text-xs font-semibold py-2 px-3 rounded-lg shadow-sm transition flex items-center justify-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>Telusuri ${stats.total} Usaha di Peta Sebaran</span>
                </button>
            </div>
        `;
    }

    // Switch from Tab 2 to Tab 1 with a specific kecamatan filtered
    window.alihkanKeTabSebaranFilter = function(idKecamatan) {
        document.getElementById('select-kecamatan').value = idKecamatan;
        document.getElementById('btn-tab-sebaran').click();
        terapkanFilterDanRender();
    };

    // Load points data: Pre-loaded from server script tag with AJAX fallback
    function muatSemuaTitik() {
        const loadingIndicator = document.getElementById('map-loading-indicator');
        
        // 1. Cek data bawaan server (zero network latency)
        const serverDataScript = document.getElementById('server-titik-usaha');
        if (serverDataScript && serverDataScript.textContent.trim()) {
            try {
                semuaDataTitik = JSON.parse(serverDataScript.textContent);
                if (Array.isArray(semuaDataTitik) && semuaDataTitik.length > 0) {
                    terapkanFilterDanRender();
                    return;
                }
            } catch (err) {
                console.warn('Gagal membaca data titik tersemat, mencoba memuat via AJAX:', err);
            }
        }

        // 2. Fallback AJAX fetch
        if (loadingIndicator) {
            loadingIndicator.classList.remove('hidden');
            loadingIndicator.classList.add('flex');
        }

        const fetchUrl = (apiTitikUrl || '/api/titik-usaha') + '?status_operasional=all';

        fetch(fetchUrl)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}: Gagal menghubungi endpoint`);
                return res.json();
            })
            .then(res => {
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                    loadingIndicator.classList.remove('flex');
                }
                semuaDataTitik = res.data || [];
                terapkanFilterDanRender();
            })
            .catch(err => {
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                    loadingIndicator.classList.remove('flex');
                }
                console.error('Gagal memuat titik usaha:', err);
                const container = document.getElementById('container-daftar-usaha');
                if (container) {
                    container.innerHTML = `
                        <div class="p-6 text-center text-rose-500 text-xs">
                            <p class="font-bold">Gagal memuat data titik usaha</p>
                            <p class="mt-1 text-slate-500">${err.message}</p>
                            <button onclick="location.reload()" class="mt-3 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md font-semibold">
                                Muat Ulang Halaman
                            </button>
                        </div>
                    `;
                }
            });
    }

    // Apply filters and render both list and markers
    function terapkanFilterDanRender() {
        const query = (document.getElementById('input-pencarian-sebaran').value || '').toLowerCase().trim();
        const idKbli = document.getElementById('select-kbli').value;
        const idKecamatan = document.getElementById('select-kecamatan').value;

        // Current status filter from active button
        const activeStatusBtn = document.querySelector('.btn-filter-status.bg-ampera-900');
        const statusFilter = activeStatusBtn ? activeStatusBtn.dataset.status : 'all';

        dataTerfilter = semuaDataTitik.filter(item => {
            // Status filter
            if (statusFilter === 'buka' && item.status_operasional !== 'Buka') return false;
            if (statusFilter === 'tutup_permanen' && item.status_operasional !== 'Tutup Permanen') return false;
            if (statusFilter === 'tutup_sementara' && item.status_operasional !== 'Tutup Sementara') return false;

            // KBLI filter
            if (idKbli && item.id_kbli != idKbli) return false;

            // Kecamatan filter
            if (idKecamatan && item.id_kecamatan != idKecamatan) return false;

            // Query filter
            if (query) {
                const matchNama = (item.nama_usaha || '').toLowerCase().includes(query);
                const matchProyek = (item.nama_proyek || '').toLowerCase().includes(query);
                const matchPerusahaan = (item.nama_perusahaan || '').toLowerCase().includes(query);
                const matchNib = (item.nib || '').toLowerCase().includes(query);
                const matchAlamat = (item.alamat || '').toLowerCase().includes(query);
                const matchKecamatan = (item.nama_kecamatan || '').toLowerCase().includes(query);
                if (!matchNama && !matchProyek && !matchPerusahaan && !matchNib && !matchAlamat && !matchKecamatan) {
                    return false;
                }
            }

            return true;
        });

        // Update count indicator
        const countEl = document.getElementById('count-tampil');
        if (countEl) countEl.textContent = Number(dataTerfilter.length).toLocaleString('id-ID');

        // Render markers on map
        renderMarkersPeta();

        // Render scrollable place directory list
        renderDaftarTempatUsaha();
    }

    // Render Markers into ClusterGroup
    function renderMarkersPeta() {
        if (!clusterGroup) return;

        clusterGroup.clearLayers();
        markersById = {};

        const markers = [];

        dataTerfilter.forEach(item => {
            const icon = createCustomMarkerIcon(item.status_operasional);
            const marker = L.marker([item.latitude, item.longitude], { icon: icon });

            let statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Buka</span>';
            if (item.status_operasional === 'Tutup Sementara') {
                statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Tutup Sementara</span>';
            } else if (item.status_operasional === 'Tutup Permanen') {
                statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Tutup Permanen</span>';
            }

            const gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${item.latitude},${item.longitude}`;

            const popupHtml = `
                <div class="p-3.5 max-w-xs space-y-2">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">NIB: ${item.nib || '-'}</span>
                        ${statusBadge}
                    </div>

                    <div>
                        <h4 class="font-bold text-sm text-slate-900 leading-snug">${item.nama_usaha}</h4>
                        ${item.nama_perusahaan && item.nama_perusahaan !== item.nama_usaha ? `<p class="text-[11px] text-slate-500 mt-0.5">${item.nama_perusahaan}</p>` : ''}
                    </div>

                    <div class="space-y-1 text-xs text-slate-600">
                        <div class="inline-block bg-slate-100 text-slate-700 text-[10px] font-medium px-2 py-0.5 rounded">
                            ${item.judul_kbli} (${item.kode_kbli})
                        </div>
                        <p class="text-slate-500 text-[11px] leading-relaxed">
                            <span class="font-semibold text-slate-700">Kecamatan:</span> ${item.nama_kecamatan}
                        </p>
                        <p class="text-slate-500 text-[11px] leading-relaxed line-clamp-2" title="${item.alamat || '-'}">
                            <span class="font-semibold text-slate-700">Alamat:</span> ${item.alamat || '-'}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400 font-mono">${item.latitude.toFixed(5)}, ${item.longitude.toFixed(5)}</span>
                        <a href="${gmapsUrl}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center space-x-1 text-[11px] font-semibold text-ampera-900 hover:text-ampera-800 hover:underline">
                            <span>Petunjuk Arah</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>
            `;

            marker.bindPopup(popupHtml);
            markers.push(marker);
            markersById[item.id_usaha] = marker;
        });

        if (typeof clusterGroup.addLayers === 'function') {
            clusterGroup.addLayers(markers);
        } else {
            markers.forEach(m => clusterGroup.addLayer(m));
        }

        // Fit map bounds to markers (on initial load and whenever filtered)
        if (clusterGroup && clusterGroup.getLayers().length > 0 && mapSebaran) {
            try {
                const bounds = clusterGroup.getBounds();
                if (bounds && bounds.isValid()) {
                    mapSebaran.fitBounds(bounds, { padding: [30, 30] });
                }
            } catch (e) {
                // Ignore bounds error
            }
        }
    }

    // Render Left Place Directory List
    function renderDaftarTempatUsaha() {
        const container = document.getElementById('container-daftar-usaha');
        if (!container) return;

        if (dataTerfilter.length === 0) {
            container.innerHTML = `
                <div class="py-12 px-4 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-800">Tidak ada usaha yang sesuai</p>
                    <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci lain atau setel ulang filter.</p>
                    <button type="button" 
                            onclick="document.getElementById('btn-reset-filters').click()" 
                            class="mt-3 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">
                        Setel Ulang Filter
                    </button>
                </div>
            `;
            return;
        }

        // Display up to 80 items
        const displayed = dataTerfilter.slice(0, 80);

        let html = '';
        displayed.forEach(item => {
            let statusPill = '<span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span><span class="text-emerald-700 font-semibold">Buka</span>';
            if (item.status_operasional === 'Tutup Sementara') {
                statusPill = '<span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span><span class="text-amber-700 font-semibold">Sementara</span>';
            } else if (item.status_operasional === 'Tutup Permanen') {
                statusPill = '<span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span><span class="text-rose-700 font-semibold">Tutup</span>';
            }

            html += `
                <div class="card-usaha p-3 bg-white rounded-xl border border-slate-200 hover:border-ampera-900/50 hover:shadow-md transition duration-150 cursor-pointer group"
                     data-id-usaha="${item.id_usaha}">
                    
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center space-x-1.5 text-[10px]">
                            ${statusPill}
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">${item.nama_kecamatan}</span>
                    </div>

                    <h4 class="font-bold text-xs text-slate-900 group-hover:text-ampera-900 transition leading-snug line-clamp-1">
                        ${item.nama_usaha}
                    </h4>

                    <div class="mt-1.5 flex items-center space-x-1 text-[10px] text-slate-500">
                        <span class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 truncate max-w-[200px]">${item.judul_kbli}</span>
                    </div>

                    <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">${item.alamat || '-'}</p>

                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400 font-mono">NIB: ${item.nib || '-'}</span>
                        <button type="button" 
                                class="btn-focus-usaha inline-flex items-center space-x-1 text-[11px] font-semibold text-ampera-900 group-hover:underline"
                                data-id="${item.id_usaha}"
                                data-lat="${item.latitude}"
                                data-lng="${item.longitude}">
                            <span>Fokus Peta</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>

                </div>
            `;
        });

        if (dataTerfilter.length > 80) {
            html += `
                <div class="p-3 text-center text-xs text-slate-500 bg-slate-50 rounded-lg border border-slate-200">
                    Menampilkan 80 dari ${dataTerfilter.length} hasil. Persempit pencarian atau pilih kecamatan untuk melihat tempat spesifik.
                </div>
            `;
        }

        container.innerHTML = html;

        // Attach click listeners to cards
        container.querySelectorAll('.card-usaha').forEach(card => {
            card.addEventListener('click', function(e) {
                const idUsaha = this.dataset.idUsaha;
                fokusKeTitikUsaha(idUsaha);
            });
        });
    }

    // Fly map to a specific business marker and open its popup
    function fokusKeTitikUsaha(idUsaha) {
        const item = semuaDataTitik.find(x => x.id_usaha == idUsaha);
        if (!item || !mapSebaran) return;

        mapSebaran.flyTo([item.latitude, item.longitude], 17, {
            duration: 1.2
        });

        setTimeout(() => {
            const marker = markersById[idUsaha];
            if (marker) {
                if (clusterGroup && typeof clusterGroup.zoomToShowLayer === 'function') {
                    clusterGroup.zoomToShowLayer(marker, () => {
                        marker.openPopup();
                    });
                } else {
                    marker.openPopup();
                }
            }
        }, 1300);
    }

    // Set up DOM events
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Inisialisasi Peta
        inisialisasiPetaSebaran();
        
        // 2. Muat Titik Usaha
        muatSemuaTitik();

        // 3. Setel lebar bar kepadatan kecamatan secara dinamis
        document.querySelectorAll('.bar-kepadatan').forEach(el => {
            el.style.width = (el.dataset.pct || 0) + '%';
        });

        // 4. Pastikan peta menghitung ulang ukuran kanvas setelah DOM stabil
        setTimeout(() => {
            if (mapSebaran) {
                mapSebaran.invalidateSize();
                if (clusterGroup && clusterGroup.getLayers().length > 0) {
                    const bounds = clusterGroup.getBounds();
                    if (bounds && bounds.isValid()) {
                        mapSebaran.fitBounds(bounds, { padding: [30, 30] });
                    }
                }
            }
        }, 300);

        // Mode Switcher Tabs
        const btnTabSebaran = document.getElementById('btn-tab-sebaran');
        const btnTabChoropleth = document.getElementById('btn-tab-choropleth');
        const viewTabSebaran = document.getElementById('view-tab-sebaran');
        const viewTabChoropleth = document.getElementById('view-tab-choropleth');

        btnTabSebaran.addEventListener('click', function() {
            activeTab = 'sebaran';
            btnTabSebaran.classList.add('bg-ampera-900', 'text-white', 'shadow');
            btnTabSebaran.classList.remove('text-slate-300', 'hover:text-white', 'hover:bg-slate-700/60');

            btnTabChoropleth.classList.remove('bg-ampera-900', 'text-white', 'shadow');
            btnTabChoropleth.classList.add('text-slate-300', 'hover:text-white', 'hover:bg-slate-700/60');

            viewTabSebaran.classList.remove('hidden');
            viewTabSebaran.classList.add('flex');
            viewTabChoropleth.classList.add('hidden');
            viewTabChoropleth.classList.remove('flex');

            if (mapSebaran) {
                setTimeout(() => mapSebaran.invalidateSize(), 150);
            }
        });

        btnTabChoropleth.addEventListener('click', function() {
            activeTab = 'choropleth';
            btnTabChoropleth.classList.add('bg-ampera-900', 'text-white', 'shadow');
            btnTabChoropleth.classList.remove('text-slate-300', 'hover:text-white', 'hover:bg-slate-700/60');

            btnTabSebaran.classList.remove('bg-ampera-900', 'text-white', 'shadow');
            btnTabSebaran.classList.add('text-slate-300', 'hover:text-white', 'hover:bg-slate-700/60');

            viewTabChoropleth.classList.remove('hidden');
            viewTabChoropleth.classList.add('flex');
            viewTabSebaran.classList.add('hidden');
            viewTabSebaran.classList.remove('flex');

            inisialisasiPetaChoropleth();
            if (mapChoropleth) {
                setTimeout(() => mapChoropleth.invalidateSize(), 150);
            }
        });

        // Search Input with debounce
        const searchInput = document.getElementById('input-pencarian-sebaran');
        const clearBtn = document.getElementById('btn-clear-search');
        let debounceTimer = null;

        searchInput.addEventListener('input', function() {
            if (clearBtn) {
                if (this.value.length > 0) {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }
            }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                terapkanFilterDanRender();
            }, 250);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                clearBtn.classList.add('hidden');
                terapkanFilterDanRender();
            });
        }

        // Status Operasional Button Group
        const statusBtns = document.querySelectorAll('.btn-filter-status');
        statusBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                statusBtns.forEach(b => {
                    b.classList.remove('bg-ampera-900', 'text-white', 'border-ampera-900', 'font-semibold');
                    b.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
                });
                this.classList.add('bg-ampera-900', 'text-white', 'border-ampera-900', 'font-semibold');
                this.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');
                terapkanFilterDanRender();
            });
        });

        // Dropdown Filters
        document.getElementById('select-kbli').addEventListener('change', terapkanFilterDanRender);
        document.getElementById('select-kecamatan').addEventListener('change', terapkanFilterDanRender);

        // Reset Filter Action
        document.getElementById('btn-reset-filters').addEventListener('click', function() {
            searchInput.value = '';
            if (clearBtn) clearBtn.classList.add('hidden');
            document.getElementById('select-kbli').value = '';
            document.getElementById('select-kecamatan').value = '';

            statusBtns.forEach(b => {
                if (b.dataset.status === 'all') {
                    b.classList.add('bg-ampera-900', 'text-white', 'border-ampera-900', 'font-semibold');
                    b.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');
                } else {
                    b.classList.remove('bg-ampera-900', 'text-white', 'border-ampera-900', 'font-semibold');
                    b.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
                }
            });

            terapkanFilterDanRender();
            if (mapSebaran) {
                mapSebaran.flyTo(PALEMBANG_CENTER, PALEMBANG_ZOOM);
            }
        });

        // Recenter Map Button
        document.getElementById('btn-recenter-map').addEventListener('click', function() {
            if (activeTab === 'sebaran' && mapSebaran) {
                mapSebaran.flyTo(PALEMBANG_CENTER, PALEMBANG_ZOOM);
            } else if (activeTab === 'choropleth' && mapChoropleth) {
                mapChoropleth.flyTo(PALEMBANG_CENTER, PALEMBANG_ZOOM);
            }
        });

        // Mobile Toggle Sidebar Button
        const toggleSidebarBtn = document.getElementById('btn-toggle-sidebar');
        const sidebarSebaran = document.getElementById('sidebar-sebaran');
        if (toggleSidebarBtn && sidebarSebaran) {
            toggleSidebarBtn.addEventListener('click', function() {
                sidebarSebaran.classList.toggle('hidden');
            });
        }

        // Click row in Choropleth ranking list
        document.querySelectorAll('.item-kecamatan').forEach(item => {
            item.addEventListener('click', function() {
                const idKec = this.dataset.idKecamatan;
                if (!geojsonLayer || !mapChoropleth) return;

                geojsonLayer.eachLayer(layer => {
                    if (layer.feature && layer.feature.properties.id_kecamatan == idKec) {
                        mapChoropleth.fitBounds(layer.getBounds(), { padding: [50, 50] });
                        layer.fire('click');
                    }
                });
            });
        });
    });
</script>
@endsection
