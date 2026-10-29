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
        // Hanya percayai proxy lokal (127.0.0.1 / ::1) secara default agar header X-Forwarded-For
        // tidak bisa dipalsukan (IP Spoofing) untuk membobol RateLimiter login/OTP.
        $trustedProxies = env('TRUSTED_PROXIES', '127.0.0.1,::1');
        $middleware->trustProxies(at: array_filter(array_map('trim', explode(',', (string)$trustedProxies))));
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->web(append: [\App\Http\Middleware\EnsureLicenseValid::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Prevent database credential & internal server detail leaks to public
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            \Illuminate\Support\Facades\Log::error("Database QueryException: " . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kendala pada komunikasi database server.'
                ], 500);
            }
            if (!config('app.debug', false)) {
                return response()->view('errors.500', [
                    'message' => 'Terjadi kendala pada komunikasi server. Silakan hubungi administrator.'
                ], 500);
            }
        });

        $exceptions->render(function (\PDOException $e, $request) {
            \Illuminate\Support\Facades\Log::error("Database PDOException: " . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Koneksi database sedang tidak dapat dijangkau.'
                ], 500);
            }
            if (!config('app.debug', false)) {
                return response()->view('errors.500', [
                    'message' => 'Terjadi kendala pada koneksi database server.'
                ], 500);
            }
        });
    })->create();
