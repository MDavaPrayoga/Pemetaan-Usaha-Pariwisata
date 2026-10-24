@extends('layouts.app')

@section('title', 'Peta Pemetaan dan Klasterisasi Usaha Pariwisata Kota Palembang')

@section('styles')
<style>
    /* Menjamin kontainer peta selalu mengisi penuh tinggi layar yang tersedia */
    #map {
        width: 100%;
        height: 100%;
        min-height: calc(100vh - 61px);
        z-index: 1;
    }
    .leaflet-container {
        width: 100%;
        height: 100%;
        background-color: #f1f5f9;
    }
</style>
@endsection

@section('content')
<!-- Header Sistem dengan identitas warna Maroon Ampera -->
<header class="bg-[#8B1E2D] text-white shadow-md z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <!-- Ikon logo peta (vektor SVG murni tanpa emoji) -->
            <div class="p-2 bg-white/10 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-bold leading-tight tracking-tight">Sistem Informasi Geografis Usaha Pariwisata</h1>
                <p class="text-xs text-white/80 font-normal">Pemerintah Kota Palembang — Pemetaan & Klasterisasi Spasial DBSCAN</p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Indikator jumlah data terverifikasi -->
            <div class="hidden md:flex items-center space-x-2 bg-black/20 px-3 py-1.5 rounded-md text-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>{{ number_format($totalUsahaVerified, 0, ',', '.') }} Usaha Terverifikasi</span>
            </div>

            @auth
                <!-- Jika admin sudah login, tampilkan tombol ke Panel Admin -->
                <a href="{{ route('admin.usaha.index') }}" class="inline-flex items-center space-x-1.5 bg-white/20 hover:bg-white/30 border border-white/30 px-3 py-1.5 rounded-md text-xs font-medium transition duration-150">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Panel Admin ({{ Auth::user()->username }})</span>
                </a>
            @else
                <!-- Jika pengunjung publik belum login, tombol Login Admin -->
                <a href="{{ route('login') }}" class="inline-flex items-center space-x-1.5 bg-white/10 hover:bg-white/20 border border-white/20 px-3 py-1.5 rounded-md text-xs font-medium transition duration-150">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span>Login Admin</span>
                </a>
            @endauth
        </div>
    </div>
</header>

<!-- Area Utama: Sidebar Kontrol di kiri dan Canvas Peta di kanan -->
<div class="flex-1 flex flex-col md:flex-row h-[calc(100vh-61px)] overflow-hidden">

    <!-- Sidebar Panel Kontrol -->
    <aside class="w-full md:w-96 bg-white border-r border-slate-200 flex flex-col h-full z-10 shadow-sm overflow-y-auto">
        
        <!-- Bagian 1: Filter Data Spasial -->
        <div class="p-4 border-b border-slate-200">
            <div class="flex items-center space-x-2 mb-3">
                <svg class="w-4 h-4 text-[#8B1E2D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <h2 class="text-xs font-bold tracking-wider uppercase text-slate-700">Filter Data Usaha</h2>
            </div>

            <!-- Dropdown Pilihan Kategori KBLI -->
            <div class="mb-3">
                <label for="filter-kbli" class="block text-xs font-semibold text-slate-600 mb-1">Kategori Usaha (KBLI)</label>
                <select id="filter-kbli" class="w-full text-xs rounded-md border border-slate-300 bg-white px-2.5 py-1.5 focus:border-[#8B1E2D] focus:ring-1 focus:ring-[#8B1E2D] outline-none">
                    <option value="">Semua Kategori Usaha</option>
                    @foreach($daftarKbli as $k)
                        <option value="{{ $k->id_kbli }}">{{ $k->judul_kbli }} ({{ $k->kode_kbli }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Dropdown Pilihan Wilayah Kecamatan -->
            <div class="mb-3">
                <label for="filter-kecamatan" class="block text-xs font-semibold text-slate-600 mb-1">Kecamatan Kota Palembang</label>
                <select id="filter-kecamatan" class="w-full text-xs rounded-md border border-slate-300 bg-white px-2.5 py-1.5 focus:border-[#8B1E2D] focus:ring-1 focus:ring-[#8B1E2D] outline-none">
                    <option value="">Semua Kecamatan</option>
                    @foreach($daftarKecamatan as $kc)
                        <option value="{{ $kc->id_kecamatan }}">{{ $kc->nama_kecamatan }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Terapkan Filter -->
            <button id="btn-filter" type="button" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium py-1.5 px-3 rounded-md transition duration-150 flex items-center justify-center space-x-1.5">
                <span>Terapkan Filter Peta</span>
            </button>
        </div>

        <!-- Bagian 2: Panel Parameter Simulasi DBSCAN -->
        <div class="p-4 border-b border-slate-200 bg-slate-50/50">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-[#8B1E2D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <h2 class="text-xs font-bold tracking-wider uppercase text-slate-700">Simulasi DBSCAN</h2>
                </div>
                <span class="text-[10px] font-semibold bg-[#8B1E2D]/10 text-[#8B1E2D] px-2 py-0.5 rounded">Stateless</span>
            </div>

            <!-- Slider Parameter Epsilon (Jarak Tetangga) -->
            <div class="mb-4">
                <div class="flex justify-between items-center mb-1">
                    <label for="input-eps" class="text-xs font-semibold text-slate-600">Jarak Radius (Epsilon / &epsilon;)</label>
                    <span id="label-eps" class="text-xs font-bold font-mono text-[#8B1E2D] bg-[#8B1E2D]/10 px-2 py-0.5 rounded">0.5 km (500m)</span>
                </div>
                <input id="input-eps" type="range" min="0.1" max="3.0" step="0.1" value="0.5" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#8B1E2D]">
                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                    <span>100 meter</span>
                    <span>1.5 km</span>
                    <span>3.0 km</span>
                </div>
            </div>

            <!-- Slider Parameter MinPts (Densitas Minimum Titik) -->
            <div class="mb-4">
                <div class="flex justify-between items-center mb-1">
                    <label for="input-minpts" class="text-xs font-semibold text-slate-600">Min. Kepadatan Titik (MinPts)</label>
                    <span id="label-minpts" class="text-xs font-bold font-mono text-[#8B1E2D] bg-[#8B1E2D]/10 px-2 py-0.5 rounded">5 titik</span>
                </div>
                <input id="input-minpts" type="range" min="2" max="20" step="1" value="5" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#8B1E2D]">
                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                    <span>2 titik</span>
                    <span>10 titik</span>
                    <span>20 titik</span>
                </div>
            </div>

            <!-- Tombol Eksekusi Simulasi dan Reset -->
            <div class="flex space-x-2">
                <button id="btn-simulasi" type="button" class="flex-1 bg-[#8B1E2D] hover:bg-[#741D29] text-white text-xs font-semibold py-2 px-3 rounded-md transition duration-150 flex items-center justify-center space-x-1.5 shadow-sm">
                    <svg id="icon-simulasi" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span id="text-simulasi">Jalankan Simulasi</span>
                </button>
                <button id="btn-reset" type="button" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-medium py-2 px-3 rounded-md transition duration-150">
                    Reset
                </button>
            </div>
        </div>

        <!-- Bagian 3: Ringkasan Metrik Hasil Simulasi -->
        <div id="panel-hasil" class="p-4 border-b border-slate-200 hidden">
            <h2 class="text-xs font-bold tracking-wider uppercase text-slate-700 mb-3">Hasil Evaluasi Klaster</h2>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-slate-50 border border-slate-200 p-2 rounded-md">
                    <p class="text-[10px] font-medium text-slate-500">Klaster</p>
                    <p id="metrik-klaster" class="text-base font-bold text-slate-800 font-mono">0</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 p-2 rounded-md">
                    <p class="text-[10px] font-medium text-slate-500">Noise</p>
                    <p id="metrik-noise" class="text-base font-bold text-slate-800 font-mono">0</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 p-2 rounded-md">
                    <p class="text-[10px] font-medium text-slate-500">Waktu</p>
                    <p id="metrik-waktu" class="text-base font-bold text-slate-800 font-mono">0 ms</p>
                </div>
            </div>
        </div>

        <!-- Bagian 4: Legenda Warna Klaster -->
        <div class="p-4 flex-1">
            <h2 class="text-xs font-bold tracking-wider uppercase text-slate-700 mb-2">Legenda Peta</h2>
            <div id="kontainer-legenda" class="space-y-1.5 text-xs text-slate-600">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-[#8B1E2D] border border-white shadow-sm inline-block"></span>
                    <span>Titik Usaha Terverifikasi</span>
                </div>
            </div>
        </div>

    </aside>

    <!-- Canvas Peta Utama Leaflet -->
    <div class="flex-1 relative w-full h-full min-h-[450px]">
        <div id="map" class="w-full h-full"></div>

        <!-- Indikator Loading Saat Proses Komputasi Berjalan -->
        <div id="indikator-loading" class="hidden absolute top-4 right-4 bg-white/95 backdrop-blur-sm border border-slate-300 shadow-md px-3.5 py-2 rounded-md z-30 flex items-center space-x-2 text-xs font-medium text-slate-700">
            <svg class="animate-spin w-4 h-4 text-[#8B1E2D]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Memproses Data...</span>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Palet warna resmi 10 klaster DBSCAN sesuai dokumen DESIGN.md
    const PALET_KLASTER = [
        '#8B1E2D', // Klaster 1: Ampera Crimson
        '#1E40AF', // Klaster 2: Deep Royal Blue
        '#047857', // Klaster 3: Musi Emerald
        '#B45309', // Klaster 4: Sriwijaya Amber
        '#6D28D9', // Klaster 5: Regal Violet
        '#0E7490', // Klaster 6: Steel Cyan
        '#BE185D', // Klaster 7: Dark Pink
        '#4D7C0F', // Klaster 8: Lime Green
        '#C2410C', // Klaster 9: Orange Rust
        '#4338CA', // Klaster 10: Indigo
    ];
    const WARNA_NOISE = '#64748B'; // Abu-abu Slate untuk titik noise / outlier

    let mapInstance = null;
    let layerTitikUsaha = null;

    // Fungsi inisialisasi awal saat dokumen web siap
    function inisialisasiPeta() {
        if (!window.L) {
            console.error('Pustaka Leaflet belum termuat di browser.');
            return;
        }

        // 1. Membuat instance peta Leaflet berpusat di Kota Palembang
        mapInstance = L.map('map', {
            center: [-2.983596, 104.7541449],
            zoom: 12,
            zoomControl: true
        });

        // 2. Menambahkan layer peta dasar dari OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors | DPMPTSP Kota Palembang'
        }).addTo(mapInstance);

        // 3. Menyiapkan layer group untuk marker titik usaha
        layerTitikUsaha = L.layerGroup().addTo(mapInstance);

        // 4. Memaksa Leaflet menghitung ulang ukuran kontainer agar ubin peta segera muncul
        setTimeout(() => {
            mapInstance.invalidateSize();
        }, 150);

        // 5. Memuat seluruh data 1.421 titik awal dari server
        muatDataTitik();
    }

    // Eksekusi inisialisasi saat window dan dokumen selesai dimuat
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        inisialisasiPeta();
    } else {
        window.addEventListener('DOMContentLoaded', inisialisasiPeta);
    }

    // Mendaftarkan event listener elemen interaktif
    document.addEventListener('DOMContentLoaded', function () {
        // Slider nilai Epsilon
        const sliderEps = document.getElementById('input-eps');
        const labelEps = document.getElementById('label-eps');
        if (sliderEps && labelEps) {
            sliderEps.addEventListener('input', function () {
                const meter = Math.round(this.value * 1000);
                labelEps.textContent = `${this.value} km (${meter}m)`;
            });
        }

        // Slider nilai MinPts
        const sliderMinPts = document.getElementById('input-minpts');
        const labelMinPts = document.getElementById('label-minpts');
        if (sliderMinPts && labelMinPts) {
            sliderMinPts.addEventListener('input', function () {
                labelMinPts.textContent = `${this.value} titik`;
            });
        }

        // Tombol terapkan filter kategori / wilayah
        const btnFilter = document.getElementById('btn-filter');
        if (btnFilter) {
            btnFilter.addEventListener('click', function () {
                const kbliId = document.getElementById('filter-kbli').value;
                const kecamatanId = document.getElementById('filter-kecamatan').value;
                muatDataTitik(kbliId, kecamatanId);
            });
        }

        // Tombol jalankan simulasi DBSCAN
        const btnSimulasi = document.getElementById('btn-simulasi');
        if (btnSimulasi) {
            btnSimulasi.addEventListener('click', function () {
                jalankanSimulasiDbscan();
            });
        }

        // Tombol reset peta kembali ke data awal
        const btnReset = document.getElementById('btn-reset');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                document.getElementById('filter-kbli').value = '';
                document.getElementById('filter-kecamatan').value = '';
                document.getElementById('panel-hasil').classList.add('hidden');
                resetLegendaAwal();
                muatDataTitik();
            });
        }
    });

    // Mengambil data titik usaha dari endpoint API dan merendernya di peta
    function muatDataTitik(idKbli = '', idKecamatan = '') {
        const loading = document.getElementById('indikator-loading');
        if (loading) loading.classList.remove('hidden');

        let url = '/api/titik-usaha';
        const params = new URLSearchParams();
        if (idKbli) params.append('id_kbli', idKbli);
        if (idKecamatan) params.append('id_kecamatan', idKecamatan);

        if (params.toString()) {
            url += '?' + params.toString();
        }

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (loading) loading.classList.add('hidden');
                if (!layerTitikUsaha) return;

                layerTitikUsaha.clearLayers();

                res.data.forEach(item => {
                    const marker = L.circleMarker([item.latitude, item.longitude], {
                        radius: 5,
                        fillColor: '#8B1E2D',
                        color: '#FFFFFF',
                        weight: 1.5,
                        opacity: 1,
                        fillOpacity: 0.85
                    });

                    // Format isi popup info saat marker diklik
                    const popupContent = `
                        <div class="text-xs font-sans p-1 leading-relaxed max-w-xs">
                            <h3 class="font-bold text-sm text-slate-900 border-b pb-1 mb-1.5">${item.nama_usaha}</h3>
                            <p class="text-slate-600 mb-1"><span class="font-semibold text-slate-700">Kategori:</span> ${item.judul_kbli}</p>
                            <p class="text-slate-600 mb-1"><span class="font-semibold text-slate-700">Kecamatan:</span> ${item.nama_kecamatan}</p>
                            <p class="text-slate-500 mb-1.5 text-[11px]"><span class="font-semibold text-slate-700">Alamat:</span> ${item.alamat || '-'}</p>
                            <p class="text-[10px] text-slate-400 font-mono">${item.latitude}, ${item.longitude}</p>
                        </div>
                    `;
                    marker.bindPopup(popupContent);
                    layerTitikUsaha.addLayer(marker);
                });
            })
            .catch(err => {
                if (loading) loading.classList.add('hidden');
                console.error('Gagal memuat titik:', err);
            });
    }

    // Mengirim parameter simulasi ke API dan mewarnai marker sesuai klasternya
    function jalankanSimulasiDbscan() {
        const loading = document.getElementById('indikator-loading');
        const btnSimulasi = document.getElementById('btn-simulasi');
        const eps = parseFloat(document.getElementById('input-eps').value);
        const minPts = parseInt(document.getElementById('input-minpts').value);
        const idKbli = document.getElementById('filter-kbli').value;
        const idKecamatan = document.getElementById('filter-kecamatan').value;

        if (loading) loading.classList.remove('hidden');
        if (btnSimulasi) btnSimulasi.disabled = true;

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
                eps: eps,
                min_pts: minPts,
                id_kbli: idKbli || null,
                id_kecamatan: idKecamatan || null
            })
        })
        .then(res => res.json())
        .then(res => {
            if (loading) loading.classList.add('hidden');
            if (btnSimulasi) btnSimulasi.disabled = false;

            if (!res.success) {
                alert('Simulasi gagal: ' + res.message);
                return;
            }

            // 1. Tampilkan kartu metrik hasil perhitungan
            document.getElementById('panel-hasil').classList.remove('hidden');
            document.getElementById('metrik-klaster').textContent = res.total_clusters;
            document.getElementById('metrik-noise').textContent = res.total_noise;
            document.getElementById('metrik-waktu').textContent = res.execution_time_ms + ' ms';

            // 2. Bersihkan layer marker lama dan render marker baru bergradasi warna klaster
            if (!layerTitikUsaha) return;
            layerTitikUsaha.clearLayers();

            res.points.forEach(item => {
                let warna = WARNA_NOISE;
                let namaStatusKlaster = 'Pencilan (Noise)';

                if (item.cluster_label > 0) {
                    const warnaIdx = (item.cluster_label - 1) % PALET_KLASTER.length;
                    warna = PALET_KLASTER[warnaIdx];
                    namaStatusKlaster = `Klaster ${item.cluster_label} (${item.tipe_titik.toUpperCase()})`;
                }

                const marker = L.circleMarker([item.latitude, item.longitude], {
                    radius: item.cluster_label > 0 ? 6 : 4,
                    fillColor: warna,
                    color: item.cluster_label > 0 ? '#FFFFFF' : '#475569',
                    weight: 1.5,
                    opacity: 1,
                    fillOpacity: item.cluster_label > 0 ? 0.9 : 0.6
                });

                const popupContent = `
                    <div class="text-xs font-sans p-1 leading-relaxed max-w-xs">
                        <div class="mb-1.5 flex items-center justify-between">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded text-white" style="background-color: ${warna}">
                                ${namaStatusKlaster}
                            </span>
                        </div>
                        <h3 class="font-bold text-sm text-slate-900 border-b pb-1 mb-1.5">${item.nama_usaha}</h3>
                        <p class="text-slate-600 mb-1"><span class="font-semibold text-slate-700">Kategori:</span> ${item.judul_kbli}</p>
                        <p class="text-slate-600 mb-1"><span class="font-semibold text-slate-700">Kecamatan:</span> ${item.nama_kecamatan}</p>
                        <p class="text-slate-500 mb-1.5 text-[11px]"><span class="font-semibold text-slate-700">Alamat:</span> ${item.alamat || '-'}</p>
                        <p class="text-[10px] text-slate-400 font-mono">${item.latitude}, ${item.longitude}</p>
                    </div>
                `;
                marker.bindPopup(popupContent);
                layerTitikUsaha.addLayer(marker);
            });

            // 3. Perbarui elemen legenda sesuai klaster yang aktif terbentuk
            perbaruiLegenda(res.clusters, res.total_noise);
        })
        .catch(err => {
            if (loading) loading.classList.add('hidden');
            if (btnSimulasi) btnSimulasi.disabled = false;
            console.error('Error saat simulasi DBSCAN:', err);
        });
    }

    // Memperbarui daftar legenda sesuai klaster yang aktif terbentuk
    function perbaruiLegenda(clusters, totalNoise) {
        const kontainer = document.getElementById('kontainer-legenda');
        if (!kontainer) return;
        kontainer.innerHTML = '';

        const clusterIds = Object.keys(clusters).map(Number).sort((a, b) => a - b);

        clusterIds.forEach(cId => {
            const warnaIdx = (cId - 1) % PALET_KLASTER.length;
            const warna = PALET_KLASTER[warnaIdx];
            const jumlahAnggota = clusters[cId].length;

            const baris = document.createElement('div');
            baris.className = 'flex items-center justify-between py-1';
            baris.innerHTML = `
                <div class="flex items-center space-x-2">
                    <span class="w-3.5 h-3.5 rounded-full border border-white shadow-sm inline-block" style="background-color: ${warna}"></span>
                    <span class="font-medium text-slate-700">Klaster ${cId}</span>
                </div>
                <span class="text-slate-500 font-mono text-[11px] bg-slate-100 px-1.5 py-0.5 rounded">${jumlahAnggota} usaha</span>
            `;
            kontainer.appendChild(baris);
        });

        if (totalNoise > 0) {
            const barisNoise = document.createElement('div');
            barisNoise.className = 'flex items-center justify-between py-1 border-t border-slate-100 pt-1.5 mt-1';
            barisNoise.innerHTML = `
                <div class="flex items-center space-x-2">
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-400 bg-slate-500 inline-block"></span>
                    <span class="text-slate-500 italic">Pencilan (Noise)</span>
                </div>
                <span class="text-slate-500 font-mono text-[11px] bg-slate-100 px-1.5 py-0.5 rounded">${totalNoise} usaha</span>
            `;
            kontainer.appendChild(barisNoise);
        }
    }

    // Mengembalikan legenda ke format awal
    function resetLegendaAwal() {
        const kontainer = document.getElementById('kontainer-legenda');
        if (!kontainer) return;
        kontainer.innerHTML = `
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-[#8B1E2D] border border-white shadow-sm inline-block"></span>
                <span>Titik Usaha Terverifikasi</span>
            </div>
        `;
    }
</script>
@endsection
