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
        $middleware->trustProxies(at: '*');
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->web(append: [\App\Http\Middleware\EnsureLicenseValid::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Prevent database credential & internal server detail leaks to public
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            \Illuminate\Support\Facades\Log::error("Database Exception: " . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kendala pada komunikasi database server.'
                ], 500);
            }
            if (env('APP_DEBUG', false) === false) {
                return response()->view('errors.500', [
                    'message' => 'Terjadi kendala pada komunikasi server. Silakan hubungi administrator.'
                ], 500);
            }
        });
    })->create();
