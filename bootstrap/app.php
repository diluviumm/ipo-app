<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SetSesiAktif::class,
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
            'superadmin' => \App\Http\Middleware\SuperAdminOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $kode = $response->getStatusCode();
                if ($kode < 400) $kode = 500;
                $pesan = $kode === 404 ? 'Tidak ditemukan' : ($kode === 403 ? 'Akses ditolak' : ($kode === 429 ? 'Terlalu banyak permintaan' : 'Terjadi kesalahan'));
                return response()->json(['error' => $pesan], $kode);
            }
            return $response;
        });
    })->create();
