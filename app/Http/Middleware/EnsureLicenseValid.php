<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengunci seluruh aplikasi bila lisensi tidak aktif.
 * Hanya halaman aktivasi lisensi dan health check yang dikecualikan.
 */
class EnsureLicenseValid
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('license', 'license/*', 'up')) {
            return $next($request);
        }

        if (app(LicenseService::class)->isValid()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Lisensi aplikasi tidak aktif. Hubungi penyedia aplikasi.',
            ], 403);
        }

        return redirect()->route('license.show');
    }
}
