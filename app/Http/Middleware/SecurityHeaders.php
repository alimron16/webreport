<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Blokir request yang mengandung null-byte injection pada query atau input
        foreach ($request->query() as $k => $v) {
            if ((is_string($k) && str_contains($k, "\0")) || (is_string($v) && str_contains($v, "\0"))) {
                abort(400, 'Invalid request parameters.');
            }
        }

        $response = $next($request);

        // Hapus header yang membocorkan versi PHP / Server (Anti-Fingerprinting)
        @header_remove('X-Powered-By');
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // Security Headers to prevent Clickjacking, MIME-sniffing, XSS, Cross-Domain Leaks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=(), payment=(), usb=()');

        // HSTS if HTTPS is enabled
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
