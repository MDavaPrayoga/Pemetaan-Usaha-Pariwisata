<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WebGIS Usaha Pariwisata Kota Palembang')</title>

    <!-- Memuat font Plus Jakarta Sans dari Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Memuat pustaka CSS dan JS Leaflet dari folder lokal agar bisa berjalan offline -->
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    <!-- Memuat aset CSS dan JS Tailwind yang dikompilasi oleh Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Ruang tambahan untuk style khusus halaman -->
    @yield('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen">

    <!-- Bagian konten utama halaman -->
    <main class="flex-1 flex flex-col">
        @yield('content')
    </main>

    <!-- Ruang tambahan untuk skrip javascript khusus halaman -->
    @yield('scripts')
</body>
</html>
