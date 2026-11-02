@extends('layouts.app')

@section('title', 'Dashboard Eksekutif & Statistik Mutu Data — Dinas Pariwisata')

@section('content')
<header class="bg-ampera-900 text-white shadow-md z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center space-x-3">
            <div class="p-2.5 bg-white/10 rounded-xl">
                <svg class="w-6 h-6 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-lg font-bold leading-tight">Dashboard Eksekutif & Statistik Mutu Data</h1>
                    @if(auth()->user()->isPimpinan())
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-400 text-ampera-950 uppercase tracking-wider">Akses Pimpinan</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-white uppercase tracking-wider">Akses Admin</span>
                    @endif
                </div>
                <p class="text-xs text-white/80 mt-0.5">Pemantauan Mutu Geospasial, Ketahanan Usaha, & Penetapan Zonasi Pariwisata</p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.usaha.index') }}" class="inline-flex items-center space-x-1.5 bg-white/15 hover:bg-white/25 border border-white/20 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span>Meja Kerja Data (2.809)</span>
                </a>
            @endif
            <a href="{{ route('peta.zonasi') }}" class="inline-flex items-center space-x-1.5 bg-white/15 hover:bg-white/25 border border-white/20 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Pusat Usaha Wisata</span>
            </a>
        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <!-- 4 Kartu Metrik Mutu Data -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Data Izin DPMPTSP</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1 font-mono">{{ number_format($totalSemua, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Baseline OSS-RBA Palembang</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Koordinat Terverifikasi</p>
            <p class="text-2xl font-extrabold text-emerald-700 mt-1 font-mono">{{ number_format($totalVerified, 0, ',', '.') }}</p>
            <p class="text-[11px] text-emerald-600/80 mt-0.5">{{ round(($totalVerified / max(1, $totalSemua)) * 100, 1) }}% dari total izin</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Antrean Tanpa Titik</p>
            <p class="text-2xl font-extrabold text-amber-700 mt-1 font-mono">{{ number_format($totalUnverified, 0, ',', '.') }}</p>
            <p class="text-[11px] text-amber-600/80 mt-0.5">Alamat tidak lengkap dari pemohon</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Tingkat Keaktifan Usaha</p>
            <p class="text-2xl font-extrabold text-blue-700 mt-1 font-mono">{{ round(($totalBuka / max(1, $totalVerified)) * 100, 1) }}%</p>
            <p class="text-[11px] text-blue-600/80 mt-0.5">{{ number_format($totalBuka, 0, ',', '.') }} aktif beroperasi</p>
        </div>
    </div>

    <!-- Alert Informasi Status Aktor -->
    <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/70 flex items-start space-x-3 text-xs text-blue-900">
        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="space-y-1">
            <p class="font-bold">Selamat Datang, {{ auth()->user()->nama_lengkap }} ({{ ucfirst(auth()->user()->role) }})</p>
            <p class="text-blue-700">
                @if(auth()->user()->isPimpinan())
                    Anda memiliki hak akses eksekutif untuk meninjau mutu perizinan, mengesahkan dan mengunci zonasi resmi di halaman Pusat Usaha Wisata, serta mengunduh dokumen laporan resmi untuk koordinasi dinas.
                @else
                    Anda memiliki hak akses teknis administrator untuk memelihara basis data usaha, melengkapi koordinat antrean via Map Picker, dan menyaring duplikasi data OSS.
                @endif
            </p>
        </div>
    </div>
</main>
@endsection
