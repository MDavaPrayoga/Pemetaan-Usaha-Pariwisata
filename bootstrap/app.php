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
        // Jika pengguna sudah dalam kondisi login dan mencoba mengakses rute login,
        // alihkan langsung ke dashboard admin usaha, bukan kembali ke halaman publik
        $middleware->redirectUsersTo(fn () => route('admin.usaha.index'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
