@extends('layouts.app')

@section('title', 'Login Administrator - WebGIS Usaha Pariwisata Palembang')

@section('content')
<!-- Kontainer Halaman Login dengan latar belakang netral -->
<div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-100">
    
    <!-- Kartu Utama Form Login -->
    <div class="max-w-md w-full bg-white rounded-xl border border-slate-200 shadow-sm p-8">
        
        <!-- Header Kartu: Logo dan Judul Identitas Sistem -->
        <div class="text-center mb-6">
            <div class="inline-flex p-3 bg-[#8B1E2D]/10 rounded-xl mb-3">
                <svg class="w-8 h-8 text-[#8B1E2D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Login Administrator</h1>
            <p class="text-xs text-slate-500 mt-1">Sistem Informasi Geografis Pemetaan Usaha Pariwisata</p>
        </div>

        <!-- Notifikasi Pesan Kesalahan Validasi -->
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-3.5 py-2.5 rounded-lg text-xs flex items-start space-x-2">
                <svg class="w-4 h-4 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Formulir Input Kredensial -->
        <form action="{{ route('login.proses') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Input Username -->
            <div>
                <label for="username" class="block text-xs font-semibold text-slate-700 mb-1">Username Admin</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input 
                        id="username" 
                        name="username" 
                        type="text" 
                        autocomplete="username" 
                        required 
                        value="{{ old('username') }}" 
                        placeholder="Masukkan username"
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:border-[#8B1E2D] focus:ring-1 focus:ring-[#8B1E2D] outline-none transition"
                    >
                </div>
            </div>

            <!-- Input Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        autocomplete="current-password" 
                        required 
                        placeholder="Masukkan password"
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:border-[#8B1E2D] focus:ring-1 focus:ring-[#8B1E2D] outline-none transition"
                    >
                </div>
            </div>

            <!-- Opsi Ingat Saya -->
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center space-x-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#8B1E2D] focus:ring-[#8B1E2D]">
                    <span>Ingat sesi saya</span>
                </label>
            </div>

            <!-- Tombol Submit Login -->
            <button 
                type="submit" 
                class="w-full bg-[#8B1E2D] hover:bg-[#741D29] text-white text-xs font-semibold py-2.5 px-4 rounded-lg shadow-sm transition duration-150 flex items-center justify-center space-x-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <span>Masuk ke Panel Sistem</span>
            </button>
        </form>

        <!-- Tautan Kembali ke Peta Utama -->
        <div class="mt-6 pt-4 border-t border-slate-100 text-center">
            <a href="{{ route('peta.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-medium text-slate-600 hover:text-[#8B1E2D] transition duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Peta Publik</span>
            </a>
        </div>

    </div>

    <!-- Informasi Hak Cipta -->
    <p class="text-center text-[11px] text-slate-400 mt-6">
        &copy; {{ date('Y') }} DPMPTSP Kota Palembang. Hak Cipta Dilindungi.
    </p>

</div>
@endsection
