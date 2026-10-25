@extends('layouts.app')

@section('title', 'Beranda & Statistik Pariwisata — WebGIS Kota Palembang')

@section('content')
<!-- Section 1: Hero Banner (Ampera Maroon Theme) -->
<section class="relative bg-linear-to-br from-cluster-1 via-ampera-900 to-ampera-950 text-white py-16 lg:py-20 overflow-hidden">
    <!-- Subtle Background Geometric Accents -->
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <defs>
                <pattern id="grid-pattern" width="10" height="10" patternUnits="userSpaceOnUse">
                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid-pattern)" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl">
            <!-- Institutional Badge -->
            <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-sm border border-white/20 px-3.5 py-1.5 rounded-full text-xs font-semibold text-white/95 mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Sistem Informasi Geografis Resmi Kota Palembang</span>
            </div>

            <!-- Main Title -->
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight mb-4 text-white">
                Pemetaan & Klasterisasi Usaha Pariwisata
            </h1>

            <!-- Subtitle -->
            <p class="text-base sm:text-lg text-white/85 leading-relaxed mb-8">
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
                   class="inline-flex items-center space-x-2.5 bg-white/10 hover:bg-white/20 border border-white/30 text-white font-semibold px-6 py-3.5 rounded-xl backdrop-blur-sm transition duration-200 text-sm">
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

<!-- Section 3: 2 Grafik Analitis Interaktif (Kecamatan & Sektor) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Statistik & Kepadatan Sektor Pariwisata</h2>
        <p class="text-sm text-slate-600 mt-1">Sebaran kuantitatif tempat usaha pariwisata di 18 kecamatan Kota Palembang berdasarkan data faktual.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Grafik 1: Kepadatan Usaha per Kecamatan (8 Kolom) -->
        <div class="lg:col-span-7 bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-base font-bold text-slate-900">Peringkat Kepadatan 18 Kecamatan</h3>
                    <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md font-medium">Data Terverifikasi</span>
                </div>
                <p class="text-xs text-slate-500 mb-6">Jumlah tempat usaha pariwisata yang aktif dan berizin di setiap kecamatan.</p>
                
                <div class="relative h-95 w-full">
                    <canvas id="chartKecamatan"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Sumber: Dinas Penanaman Modal & PTSP Kota Palembang</span>
                <span class="font-semibold text-slate-700">Total: 18 Kecamatan</span>
            </div>
        </div>

        <!-- Grafik 2: Komposisi Sektor Usaha Pariwisata (5 Kolom) -->
        <div class="lg:col-span-5 bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-base font-bold text-slate-900">Komposisi Sektor Usaha</h3>
                    <span class="text-xs bg-[#8B1E2D]/10 text-[#8B1E2D] px-2.5 py-1 rounded-md font-medium">Berdasarkan KBLI</span>
                </div>
                <p class="text-xs text-slate-500 mb-6">Pembagian proporsi bidang usaha penunjang kepariwisataan Kota Palembang.</p>
                
                <div class="relative h-70 w-full flex items-center justify-center">
                    <canvas id="chartSektor"></canvas>
                </div>

                <!-- Custom Sector Legend / Summary -->
                <div class="mt-6 space-y-2.5">
                    @php
                        $colors = ['#8B1E2D', '#1E40AF', '#047857', '#64748B'];
                    @endphp
                    @foreach($chartSektor['labels'] as $index => $label)
                        <div class="flex items-center justify-between text-xs py-1 px-2.5 rounded-lg bg-slate-50">
                            <div class="flex items-center space-x-2">
                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $colors[$index] }};"></span>
                                <span class="text-slate-700 font-medium">{{ $label }}</span>
                            </div>
                            <span class="font-bold text-slate-900">{{ number_format($chartSektor['data'][$index], 0, ',', '.') }} Usaha</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500 text-center">
                Didominasi oleh sektor <strong>Penyediaan Makanan & Minuman (Kuliner)</strong>
            </div>
        </div>

    </div>
</section>

<!-- Section 4: 3 Pilar Metodologi Ilmiah & Keunggulan Sistem -->
<section class="bg-white border-y border-slate-200 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#8B1E2D] mb-2">Metodologi & Integritas Data</h2>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Tiga Pilar Utama Sistem WebGIS</h3>
            <p class="text-sm text-slate-600 mt-2">Menjamin keabsahan ilmiah untuk penelitian tugas akhir dan keakuratan praktis bagi Pemerintah Kota Palembang.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Pilar 1 -->
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-[#8B1E2D]/40 transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-[#8B1E2D]/10 text-[#8B1E2D] flex items-center justify-center mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-900 mb-2">Audit Lapangan & Geocoding Presisi</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Data primer perizinan OSS-RBA (2.809 baris) diverifikasi langsung secara geospasial menggunakan Google Maps factual audit, menghasilkan 1.421 titik berkoordinat valid (1.281 Buka, 109 Tutup Permanen, 31 Tutup Sementara).
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-200 text-[11px] font-semibold text-[#8B1E2D]">
                    100% Bebas Data Fiktif
                </div>
            </div>

            <!-- Pilar 2 -->
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-[#8B1E2D]/40 transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-900 mb-2">Algoritma Kerapatan Spasial DBSCAN</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Mengidentifikasi pusat-pusat konsentrasi usaha secara otomatis tanpa batasan bentuk poligon administratif kaku. Menghitung jarak presisi dengan formula <em>Haversine Distance</em> dan membungkus area dengan <em>Convex Hull</em>.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-200 text-[11px] font-semibold text-blue-700">
                    Adaptif & Kebal Data Noise (Pencilan)
                </div>
            </div>

            <!-- Pilar 3 -->
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-[#8B1E2D]/40 transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-900 mb-2">Validasi Ilmiah Silhouette Coefficient</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Menguji mutu kerapihan dan keterpisahan antar kelompok klaster secara matematis. Menghasilkan skor evaluasi kualitas yang transparan dan dapat dipertanggungjawabkan pada saat sidang sarjana terapan.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-200 text-[11px] font-semibold text-emerald-700">
                    Validasi Mutu Standar Internasional
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Section 5: Call to Action (Ajakan Eksplorasi Peta) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-linear-to-r from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-8 sm:p-12 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="max-w-xl space-y-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-400">Eksplorasi Interaktif</span>
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
    // 1. Data untuk Grafik Batang Kepadatan Kecamatan
    const ctxKecamatan = document.getElementById('chartKecamatan');
    if (ctxKecamatan) {
        const labelsKecamatan = @json($chartKecamatanLabels);
        const dataKecamatan = @json($chartKecamatanData);

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
                indexAxis: 'y', // Bar horizontal agar nama 18 kecamatan terbaca jelas
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
        const labelsSektor = @json($chartSektor['labels']);
        const dataSektor = @json($chartSektor['data']);

        new Chart(ctxSektor, {
            type: 'doughnut',
            data: {
                labels: labelsSektor,
                datasets: [{
                    data: dataSektor,
                    backgroundColor: [
                        '#8B1E2D', // Kuliner (Ampera Maroon)
                        '#1E40AF', // Jasa Perjalanan (Navy Blue)
                        '#047857', // Akomodasi/Hotel (Emerald Green)
                        '#64748B'  // Lainnya (Slate Grey)
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
                        display: false // Menggunakan kustom legend di bawah kanvas
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
});
</script>
@endsection
