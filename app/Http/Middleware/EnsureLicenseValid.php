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
            // Stealth Mode Domain Samaran Struk:
            // Jika pengunjung mengakses via Domain Samaran (misal cetaklawdiaoja.id),
            // kunci semua halaman selain /struk/{trxid} agar identitas server utama tidak bocor.
            $receiptService = app(\App\Services\ReceiptService::class);
            if ($receiptService->isStealthModeEnabled() && $receiptService->isCurrentRequestOnCustomDomain($request)) {
                if (!$request->is('struk/*') && !$request->is('images/receipt/*') && !$request->is('images/gateways/*')) {
                    $searchTrx = trim((string) $request->query('trxid', ''));
                    if ($searchTrx !== '' && preg_match('/^[A-Za-z0-9\-_]{1,40}$/', $searchTrx)) {
                        return redirect('/struk/' . urlencode($searchTrx));
                    }
                    return response()->view('webreport.receipt-lookup', [
                        'storeTitle' => \App\Models\Setting::get('receipt_store_title', 'PAYMENT POINT ONLINE BANKING'),
                        'footerInfo' => \App\Models\Setting::get('receipt_footer_info', 'Simpan struk ini sebagai bukti pembayaran yang sah.'),
                        'errorMsg'   => $searchTrx !== '' ? 'Format Nomor / ID Transaksi tidak valid.' : null,
                    ], 200);
                }
            }

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
