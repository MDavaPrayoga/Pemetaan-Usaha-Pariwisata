<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WebGIS Usaha Pariwisata Kota Palembang')</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet Local Vendor -->
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    <!-- Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <!-- Tailwind & App Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Ruang style tambahan -->
    @yield('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen">

    <!-- Global Header & Navigation Bar: Ampera Maroon Theme -->
    <header class="bg-[#8B1E2D] text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Brand Title -->
                <a href="{{ route('beranda') }}" class="flex items-center space-x-3 group py-1">
                    <div class="flex items-center space-x-2 shrink-0">
                        <!-- Logo Pemkot Palembang -->
                        <img src="{{ asset('images/palembang.png') }}" 
                             alt="Logo Kota Palembang" 
                             class="h-9 w-auto object-contain drop-shadow-sm">
                        <!-- Logo Charming Palembang (Putih) -->
                        <img src="{{ asset('images/charmingputih.png') }}" 
                             alt="Logo Dinas Pariwisata Kota Palembang" 
                             class="h-8 w-auto object-contain drop-shadow-sm">
                    </div>
                    <div class="border-l border-white/20 pl-3 hidden sm:block">
                        <div class="flex items-center space-x-2">
                            <span class="text-base font-bold tracking-tight text-white leading-tight">SIP-WISATA</span>
                            <span class="text-[10px] bg-white/20 text-white font-semibold px-2 py-0.5 rounded-full uppercase tracking-wider">Palembang</span>
                        </div>
                        <p class="text-[11px] text-white/80 leading-tight">Dinas Pariwisata Kota Palembang</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <!-- Nav 1: Beranda -->
                    <a href="{{ route('beranda') }}" 
                       class="px-3 py-2 rounded-lg text-sm font-medium transition duration-150 flex items-center space-x-1.5 {{ request()->routeIs('beranda') ? 'bg-white/20 text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Beranda</span>
                    </a>

                    <!-- Nav 2: Peta Sebaran Usaha -->
                    <a href="{{ route('peta.sebaran') }}" 
                       class="px-3 py-2 rounded-lg text-sm font-medium transition duration-150 flex items-center space-x-1.5 {{ request()->routeIs('peta.sebaran') ? 'bg-white/20 text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Peta Sebaran</span>
                    </a>

                    <!-- Nav 3: Pusat Usaha Wisata -->
                    <a href="{{ route('peta.zonasi') }}" 
                       class="px-3 py-2 rounded-lg text-sm font-medium transition duration-150 flex items-center space-x-1.5 {{ request()->routeIs('peta.zonasi') ? 'bg-white/20 text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span>Pusat Usaha Wisata</span>
                    </a>
                </nav>

                <!-- Right Side Action: Auth / Status Indicator -->
                <div class="hidden md:flex items-center space-x-3">
                    @auth
                        <!-- Jika Staf Dinas / Admin Login -->
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.usaha.index') }}" 
                                class="inline-flex items-center space-x-1.5 bg-white/20 hover:bg-white/30 border border-white/30 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition duration-150">
                                <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Panel Admin</span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center space-x-1 bg-black/20 hover:bg-black/30 text-white/90 hover:text-white px-2.5 py-1.5 rounded-lg text-xs font-medium transition duration-150">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Tombol Masuk Admin bagi Publik -->
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center space-x-1.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white px-3.5 py-1.5 rounded-lg text-xs font-medium transition duration-150">
                            <svg class="w-4 h-4 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Masuk Admin</span>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex md:hidden items-center">
                    <button id="mobile-menu-btn" type="button" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 text-white focus:outline-none" aria-label="Buka Menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-white/10 bg-ampera-900 px-4 pt-3 pb-4 space-y-2">
            <a href="{{ route('beranda') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('beranda') ? 'bg-white/20 text-white font-semibold' : 'text-white/80 hover:bg-white/10' }}">
                Beranda
            </a>
            <a href="{{ route('peta.sebaran') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('peta.sebaran') ? 'bg-white/20 text-white font-semibold' : 'text-white/80 hover:bg-white/10' }}">
                Peta Sebaran Usaha
            </a>
            <a href="{{ route('peta.zonasi') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('peta.zonasi') ? 'bg-white/20 text-white font-semibold' : 'text-white/80 hover:bg-white/10' }}">
                Pusat Usaha Wisata
            </a>
            <div class="pt-2 border-t border-white/10">
                @auth
                    <a href="{{ route('admin.usaha.index') }}" class="block px-3 py-2 text-sm font-medium text-amber-200">Panel Admin</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 text-sm font-medium text-white/80 hover:text-white">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-sm font-medium text-white hover:bg-white/10 rounded-md">Masuk Admin</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 flex flex-col">
        @yield('content')
    </main>

    <!-- Global Footer: Selaras Merah Ampera dengan Logo Resmi -->
    @unless(View::hasSection('no-footer'))
    <footer class="bg-[#8B1E2D] text-white/80 text-sm border-t border-white/15 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <!-- Kolom 1: Profil Sistem & Logo Resmi -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center space-x-3">
                        <img src="{{ asset('images/palembang.png') }}" alt="Logo Kota Palembang" class="h-10 w-auto object-contain">
                        <img src="{{ asset('images/charmingputih.png') }}" alt="Logo Dinas Pariwisata Kota Palembang" class="h-9 w-auto object-contain">
                        <div class="border-l border-white/20 pl-3">
                            <div class="text-white font-bold text-sm tracking-tight leading-tight">SIP-WISATA PALEMBANG</div>
                            <div class="text-[11px] text-white/70 leading-tight">Dinas Pariwisata Kota Palembang</div>
                        </div>
                    </div>
                    <p class="text-xs text-white/80 leading-relaxed pr-6">
                        Sistem Informasi Geografis Pemetaan dan Klasterisasi Usaha Pariwisata Berbasis Algoritma Spasial DBSCAN (*Density-Based Spatial Clustering of Applications with Noise*) dan Perizinan OSS-RBA Terverifikasi Faktual.
                    </p>
                    <div class="flex items-center space-x-2 text-xs text-white/60 pt-1">
                        <span>Pemerintah Kota Palembang</span>
                        <span>•</span>
                        <span>Dinas Pariwisata</span>
                    </div>
                </div>

                <!-- Kolom 2: Navigasi Layanan Publik -->
                <div class="space-y-3">
                    <h2 class="text-xs font-bold text-white uppercase tracking-wider">Layanan Informasi</h2>
                    <ul class="space-y-2 text-xs">
                        <li>
                            <a href="{{ route('beranda') }}" class="text-white/80 hover:text-white transition duration-150">Beranda & Statistik</a>
                        </li>
                        <li>
                            <a href="{{ route('peta.sebaran') }}" class="text-white/80 hover:text-white transition duration-150">Peta Sebaran Usaha</a>
                        </li>
                        <li>
                            <a href="{{ route('peta.zonasi') }}" class="text-white/80 hover:text-white transition duration-150">Pusat Usaha Wisata</a>
                        </li>
                        <li>
                            <a href="{{ route('login') }}" class="text-white/80 hover:text-white transition duration-150">Portal Staf Admin</a>
                        </li>
                    </ul>
                </div>

                <!-- Kolom 3: Identitas Penelitian Skripsi -->
                <div class="space-y-3">
                    <h2 class="text-xs font-bold text-white uppercase tracking-wider">Informasi Akademis</h2>
                    <p class="text-xs text-white/80 leading-relaxed">
                        Penelitian Tugas Akhir / Skripsi Sarjana Terapan (D4)<br>
                        <strong class="text-white">Manajemen Informatika</strong><br>
                        Politeknik Negeri Sriwijaya
                    </p>
                    <p class="text-[11px] text-white/70">
                        Peneliti: <strong class="text-white">M. Dava Prayoga</strong><br>
                        Tahun Akademik: 2025/2026
                    </p>
                </div>

            </div>

            <!-- Bottom Copyright Bar -->
            <div class="mt-10 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-xs text-white/60 space-y-2 sm:space-y-0">
                <p>© 2026 Pemerintah Kota Palembang — Dinas Pariwisata. Seluruh hak cipta dilindungi undang-undang.</p>
                <div class="flex items-center space-x-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="text-white/90">1.421 Titik Usaha Terverifikasi Geospasial</span>
                </div>
            </div>
        </div>
    </footer>
    @endunless

    <!-- Mobile Menu Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', function () {
                    menu.classList.toggle('hidden');
                });
            }
        });
    </script>

    <!-- Ruang skrip javascript tambahan -->
    @yield('scripts')
</body>
</html>
