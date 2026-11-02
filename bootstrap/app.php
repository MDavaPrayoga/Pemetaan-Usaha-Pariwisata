<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Bebaskan endpoint API publik dari verifikasi token CSRF agar simulasi peta selalu lancar
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // Daftarkan alias middleware role untuk pengecekan hak akses admin / pimpinan
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Jika pengguna sudah dalam kondisi login dan mencoba mengakses rute login,
        // alihkan sesuai peran masing-masing
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if ($user && $user->role === 'pimpinan') {
                return route('admin.dashboard');
            }
            return route('admin.usaha.index');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
