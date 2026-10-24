<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    // Menampilkan halaman formulir login administrator
    public function tampilkanFormLogin(): View|RedirectResponse
    {
        // Jika admin sudah dalam kondisi login, langsung arahkan ke panel data usaha
        if (Auth::check()) {
            return redirect()->route('admin.usaha.index');
        }

        // Tampilkan view login
        return view('auth.login');
    }

    // Memproses data kiriman form login (username dan password)
    public function prosesLogin(Request $request): RedirectResponse
    {
        // Validasi input agar username dan password wajib diisi
        $kredensial = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Nama pengguna (username) wajib diisi.',
            'password.required' => 'Kata sandi (password) wajib diisi.',
        ]);

        // Cek opsi 'ingat saya' pada form
        $ingatSaya = $request->boolean('remember');

        // Melakukan verifikasi kredensial terhadap tabel users di database
        if (Auth::attempt($kredensial, $ingatSaya)) {
            // Regenerasi session ID untuk mencegah serangan session fixation
            $request->session()->regenerate();

            // Arahkan admin langsung ke halaman kelola usaha
            return redirect()->route('admin.usaha.index');
        }

        // Jika kredensial salah, kembalikan ke form login dengan pesan error
        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    // Memproses proses logout administrator dari sistem
    public function prosesLogout(Request $request): RedirectResponse
    {
        // Menghapus sesi autentikasi admin
        Auth::logout();

        // Menghancurkan session data saat ini
        $request->session()->invalidate();

        // Mengenerate ulang token CSRF demi keamanan
        $request->session()->regenerateToken();

        // Mengembalikan pengguna ke halaman peta utama
        return redirect()->route('peta.index');
    }
}
