<?php

namespace App\Http\Controllers;

use App\Services\OtomaXService;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebreportController extends Controller
{
    use \App\Traits\ValidatesRecaptcha;

    protected OtomaXService $otomaXService;

    public function __construct(OtomaXService $otomaXService)
    {
        $this->otomaXService = $otomaXService;
    }

    /**
     * Show public landing / index page
     */
    public function landing()
    {
        if (!app(\App\Services\LicenseService::class)->isValid()) {
            return redirect()->route('license.show');
        }

        $isLoggedIn = Session::has('logged_in_reseller_code');
        $activeCode = Session::get('logged_in_reseller_code', '');

        return view('webreport.landing', [
            'is_logged_in' => $isLoggedIn,
            'active_code'  => $activeCode,
        ]);
    }

    /**
     * Show login page
     */
    public function loginView()
    {
        if (!app(\App\Services\LicenseService::class)->isValid()) {
            return redirect()->route('license.show');
        }

        if (Session::has('logged_in_reseller_code')) {
            return redirect()->route('webreport.dashboard');
        }

        $rcEnabled = Cache::get('recaptcha_enabled');
        if ($rcEnabled === null) {
            $rcEnabled = (\App\Models\Setting::get('recaptcha_enabled') === '1' || \App\Models\Setting::get('recaptcha_enabled') === true);
        } else {
            $rcEnabled = filter_var($rcEnabled, FILTER_VALIDATE_BOOLEAN);
        }
        $rcSiteKey = Cache::get('recaptcha_site_key') ?: \App\Models\Setting::get('recaptcha_site_key', '');
        $showRecaptcha = $rcEnabled && !empty($rcSiteKey);

        return view('webreport.login', compact('showRecaptcha', 'rcSiteKey'));
    }

    /**
     * Parse API response to support both Live OtomaX (ok & result) and Mock Service structures.
     */
    protected function parseResponse($response, $default = null)
    {
        if (is_array($response)) {
            // Check if it's the real OtomaX wrapper
            if (isset($response['ok'])) {
                if ($response['ok'] === true) {
                    return $response['result'] ?? true;
                }
                return null;
            }
            // Otherwise, it's already the direct mock response or custom error
            if (isset($response['status']) && $response['status'] === 'error') {
                return null;
            }
            return $response;
        }
        return $default;
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Rate Limiter Check (3 attempts per 5 minutes per IP)
        $throttleKey = 'webreport_login_' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            \Illuminate\Support\Facades\Log::warning("Reseller login locked by RateLimiter from IP {$request->ip()}");
            return back()->with('error', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.")->withInput();
        }

        // Validate reCAPTCHA
        if (!$this->validateRecaptcha($request)) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
            \Illuminate\Support\Facades\Log::warning("Reseller login failed CAPTCHA from IP {$request->ip()}");
            return back()->with('error', 'Validasi CAPTCHA gagal. Silakan centang kotak "I\'m not a robot".')->withInput();
        }

        $request->validate([
            'kode_reseller' => 'required|string|max:50',
            'pin'           => 'required|string|max:50',
        ]);

        $kode = strip_tags(trim(strtolower((string)$request->input('kode_reseller'))));
        $pin = (string)$request->input('pin');

        // Check if reseller exists in OtomaX
        $resResult = $this->otomaXService->call('GetRs', ['kode' => $kode]);
        $reseller = $this->parseResponse($resResult);
        
        if ($reseller) {
            // Verify PIN via TesPinRs endpoint
            $verification = $this->otomaXService->call('TesPinRs', [
                'kode' => $kode,
                'pin' => $pin
            ]);

            if ($this->parseResponse($verification)) {
                \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
                $request->session()->regenerate(); // Prevent Session Fixation
                Session::put('logged_in_reseller_code', $kode);
                Session::put('reseller_ua_hash', hash('sha256', (string)$request->userAgent()));
                $displayName = $reseller['nama'] ?? $reseller['name'] ?? $kode;

                \Illuminate\Support\Facades\Log::info("AUDIT: Reseller login SUCCESS for [{$kode}] from IP: {$request->ip()}");
                return redirect()->route('webreport.dashboard')->with('success', 'Selamat datang kembali, ' . $displayName);
            }
        }

        // If OtomaX check fails, return error
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
        \Illuminate\Support\Facades\Log::warning("AUDIT: Reseller login FAILED for [{$kode}] from IP: {$request->ip()}");

        $errorDesc = 'PIN yang Anda masukkan salah atau Kode Reseller tidak ditemukan!';
        if (isset($verification)) {
            $errorDesc = $verification['desc'] ?? $verification['message'] ?? $errorDesc;
        } elseif (!$reseller) {
            $errorDesc = $resResult['desc'] ?? $resResult['message'] ?? 'Kode Reseller tidak terdaftar!';
        }
        
        return back()->with('error', $errorDesc);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $current = Session::get('logged_in_reseller_code', 'unknown');
        \Illuminate\Support\Facades\Log::info("AUDIT: Reseller logout for [{$current}] from IP: {$request->ip()}");

        Session::forget('logged_in_reseller_code');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('webreport.login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Helper to check authentication in controller actions
     */
    protected function checkAuth()
    {
        if (!Session::has('logged_in_reseller_code')) {
            return redirect()->route('webreport.login');
        }
        $expectedUa = Session::get('reseller_ua_hash');
        $currentUa = hash('sha256', (string)request()->userAgent());
        if (!empty($expectedUa) && !hash_equals($expectedUa, $currentUa)) {
            Log::warning("SECURITY: Reseller session User-Agent mismatch for [" . Session::get('logged_in_reseller_code') . "] from IP: " . request()->ip());
            Session::forget(['logged_in_reseller_code', 'reseller_ua_hash']);
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('webreport.login')->with('error', 'Sesi Anda telah berakhir demi keamanan. Silakan login kembali.');
        }
        return null;
    }

    /**
     * Get active logged in reseller code
     */
    protected function getActiveReseller(): string
    {
        return Session::get('logged_in_reseller_code', '');
    }

    /**
     * Dashboard view showing member profiles, balances, points, etc.
     */
    public function dashboard(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $activeCode = $this->getActiveReseller();
        
        // Fetch Rs Info
        $resResult = $this->otomaXService->call('GetRs', ['kode' => $activeCode]);
        $reseller = $this->parseResponse($resResult);

        $saldoResult = $this->otomaXService->call('GetSaldoRs', ['kode' => $activeCode]);
        $saldo = $this->parseResponse($saldoResult);

        // Fetch Wallet Balances
        $walletARes = $this->otomaXService->call('GetSaldoDompet', ['kode_reseller' => $activeCode, 'kode_dompet' => 'A']);
        $walletA = $this->parseResponse($walletARes);

        $walletBRes = $this->otomaXService->call('GetSaldoDompet', ['kode_reseller' => $activeCode, 'kode_dompet' => 'B']);
        $walletB = $this->parseResponse($walletBRes);

        // List reseller options
        $resellerList = [];
        if (filter_var(env('OTOMAX_MOCK_MODE', false), FILTER_VALIDATE_BOOLEAN)) {
            $resellerList = Session::get('otomax_mock_resellers', []);
        } else {
            $resellerList = [
                $activeCode => [
                    'kode' => $activeCode,
                    'name' => $reseller['nama'] ?? $reseller['name'] ?? strtoupper($activeCode)
                ]
            ];
        }

        // Fetch 14-day transaction trend data for chart
        $trendResult = $this->otomaXService->call('GetTrendTransaksi', [
            'kode_reseller' => $activeCode,
            'days'          => 14
        ]);
        $trendData = $this->parseResponse($trendResult) ?? [];

        // Compute quick summary stats from trend data
        $totalTrx14  = array_sum(array_column($trendData, 'total_trx'));
        $successTrx14 = array_sum(array_column($trendData, 'trx_sukses'));
        $omzet14     = array_sum(array_column($trendData, 'omzet'));

        return view('webreport.dashboard', [
            'active_tab'           => 'dashboard',
            'active_reseller_code' => $activeCode,
            'reseller'             => $reseller,
            'saldo'                => $saldo,
            'wallet_a'             => $walletA,
            'wallet_b'             => $walletB,
            'resellers'            => $resellerList,
            'trend_data'           => $trendData,
            'total_trx_14'         => $totalTrx14,
            'success_trx_14'       => $successTrx14,
            'omzet_14'             => $omzet14,
        ]);
    }

    /**
     * View and filter Transaction History
     */
    public function transactions(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $activeCode = $this->getActiveReseller();
        
        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $limit = $request->input('limit', '50');
        $tujuan = $request->input('tujuan', '');
        $produk = $request->input('produk', '');
        $refId = $request->input('ref_id', '');
        $status = $request->input('status', '');

        // Hitung queryLimit berdasarkan opsi limit
        $queryLimit = ($limit === 'semua' || $limit === 'all' || (int)$limit <= 0) ? 999999 : (int)$limit;
        if ($request->input('export') === 'excel') {
            if (!$request->has('limit')) {
                $queryLimit = 999999;
            }
        }

        $trxResult = $this->otomaXService->call('GetTrxHistory', [
            'kode_reseller' => $activeCode,
            'tgl_awal' => $startDate,
            'tgl_akhir' => $endDate,
            'limit' => $queryLimit,
            'tujuan' => $tujuan,
            'produk' => $produk,
            'ref_id' => $refId,
            'status' => $status
        ]);
        $transactions = $this->parseResponse($trxResult, []);

        $resResult = $this->otomaXService->call('GetRs', ['kode' => $activeCode]);
        $reseller = $this->parseResponse($resResult);

        if ($request->input('export') === 'excel') {
            $filename = 'riwayat_transaksi_' . $activeCode . '_' . now()->format('Ymd_His') . '.xls';
            $html = view('webreport.transactions-excel', [
                'active_reseller_code' => $activeCode,
                'reseller_name' => $reseller['nama'] ?? $activeCode,
                'transactions' => is_array($transactions) ? $transactions : []
            ])->render();

            return response($html)
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }

        return view('webreport.transactions', [
            'active_tab' => 'transactions',
            'active_reseller_code' => $activeCode,
            'reseller_name' => $reseller['nama'] ?? $activeCode,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => $limit,
            'tujuan' => $tujuan,
            'produk' => $produk,
            'ref_id' => $refId,
            'status' => $status,
            'transactions' => is_array($transactions) ? $transactions : []
        ]);
    }

    /**
     * View Deposit and Mutasi Saldo
     */
    public function deposit(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $activeCode = $this->getActiveReseller();
        
        $mutasiResult = $this->otomaXService->call('GetMutasiDeposit', [
            'kode_reseller' => $activeCode,
            'limit' => 50
        ]);
        
        $pendingResult = $this->otomaXService->call('GetPendingTiket', [
            'kode_reseller' => $activeCode
        ]);

        $mutasi = $this->parseResponse($mutasiResult, []);
        $pendingTiket = $this->parseResponse($pendingResult, []);
        $banks = \Illuminate\Support\Facades\Cache::get('deposit_banks', []);

        if (is_array($pendingTiket)) {
            foreach ($pendingTiket as &$p) {
                $pKode = $p['kode'] ?? null;
                $pJumlah = $p['jumlah'] ?? null;
                $meta = null;
                if ($pKode) {
                    $meta = \Illuminate\Support\Facades\Cache::get('deposit_ticket_meta_id_' . $pKode);
                }
                if (!$meta && $pJumlah) {
                    $meta = \Illuminate\Support\Facades\Cache::get('deposit_ticket_meta_' . $pJumlah);
                }
                if ($meta) {
                    $p['bank'] = $meta['bank'] ?? ($p['bank'] ?? '');
                    $p['rekening'] = $meta['rekening'] ?? ($p['rekening'] ?? '');
                    $p['atas_nama'] = $meta['atas_nama'] ?? ($p['atas_nama'] ?? '');
                }
            }
            unset($p);
        }

        return view('webreport.deposit', [
            'active_tab' => 'deposit',
            'active_reseller_code' => $activeCode,
            'mutasi' => is_array($mutasi) ? $mutasi : [],
            'pending_tiket' => is_array($pendingTiket) ? $pendingTiket : [],
            'banks' => $banks
        ]);
    }

    /**
     * Request a deposit ticket
     */
    public function requestDeposit(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $request->validate([
            'nominal' => 'required|numeric|min:10000',
            'bank' => 'required',
        ]);

        $activeCode = $this->getActiveReseller();
        $nominal = $request->input('nominal');
        $bankInput = $request->input('bank');
        $banks = \Illuminate\Support\Facades\Cache::get('deposit_banks', []);

        $selectedBank = null;
        if (is_numeric($bankInput) && isset($banks[(int)$bankInput])) {
            $selectedBank = $banks[(int)$bankInput];
        } else {
            foreach ($banks as $b) {
                if (isset($b['rekening']) && $b['rekening'] === $bankInput) {
                    $selectedBank = $b;
                    break;
                }
                if (isset($b['bank']) && strcasecmp($b['bank'], $bankInput) === 0) {
                    $selectedBank = $b;
                    break;
                }
            }
        }

        if (!$selectedBank) {
            $selectedBank = [
                'bank' => is_string($bankInput) ? $bankInput : 'BCA',
                'rekening' => '',
                'atas_nama' => ''
            ];
        }

        $res = $this->otomaXService->call('RequestTiketDeposit', [
            'kode_reseller' => $activeCode,
            'nominal' => $nominal,
            'bank' => $selectedBank['bank'],
            'rekening' => $selectedBank['rekening'],
            'atas_nama' => $selectedBank['atas_nama'],
        ]);

        if ($res['ok']) {
            $data = $res['result'];
            return back()->with('tiket_success', [
                'nominal' => $data['nominal'],
                'bank' => $selectedBank['bank'],
                'rekening' => $selectedBank['rekening'],
                'atas_nama' => $selectedBank['atas_nama'],
            ]);
        }

        return back()->with('error', $res['desc'] ?? 'Gagal membuat tiket deposit.');
    }

    /**
     * Cancel a pending deposit ticket
     */
    public function cancelDeposit(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $request->validate([
            'kode' => 'required'
        ]);

        $activeCode = $this->getActiveReseller();
        $kode = $request->input('kode');

        $res = $this->otomaXService->call('CancelTiketDeposit', [
            'kode_reseller' => $activeCode,
            'kode' => $kode
        ]);

        if ($res['ok']) {
            return back()->with('success', 'Tiket deposit berhasil dibatalkan.');
        }

        return back()->with('error', $res['desc'] ?? 'Gagal membatalkan tiket deposit.');
    }

    /**
     * Details of a single transaction
     */
    public function transactionDetail(Request $request, $trxid)
    {
        if ($this->checkAuth() !== null) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!is_numeric($trxid) || (int)$trxid <= 0) {
            return response()->json(['status' => 'error', 'message' => 'ID Transaksi tidak valid'], 400);
        }

        $detailsResult = $this->otomaXService->call('GetTrx', [
            'trxid'         => (int)$trxid,
            'kode_reseller' => $this->getActiveReseller(),
        ]);
        $details = $this->parseResponse($detailsResult);

        if (!$details) {
            return response()->json([
                'status' => 'error', 
                'message' => $detailsResult['desc'] ?? $detailsResult['message'] ?? 'Transaction not found'
            ], 404);
        }

        return response()->json($details);
    }

    /**
     * Product price list page (Member logged-in)
     */
    public function prices(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $activeCode = $this->getActiveReseller();
        $filter = $request->input('filter', 'semua');
        $productCode = $request->input('product_code', '');
        
        // Kunci level ketat ke level resmi milik reseller yang login (cegah tembus via query string ?level=)
        $kodeLevel = '';
        if (!empty($activeCode)) {
            $rsInfo = $this->parseResponse($this->otomaXService->call('GetRs', ['kode' => $activeCode]));
            $kodeLevel = trim((string)($rsInfo['kode_level'] ?? ''));
        }

        $pricesRes = $this->otomaXService->call('GetDaftarHargaRs', [
            'kode_reseller' => $activeCode,
            'kode_level'    => $kodeLevel,
            'kode_produk'   => $productCode,
            'sort_filter'   => $filter
        ]);
        $prices = $this->parseResponse($pricesRes, []);
        $levelsRes = $this->otomaXService->call('GetLevels');
        $levels = $this->parseResponse($levelsRes, []);

        return view('webreport.prices', [
            'active_tab'           => 'prices',
            'active_reseller_code' => $activeCode,
            'filter'               => $filter,
            'product_code'         => $productCode,
            'kode_level'           => $kodeLevel,
            'levels'               => is_array($levels) ? $levels : [],
            'prices'               => is_array($prices) ? $prices : [],
            'is_public'            => false
        ]);
    }

    /**
     * Public Price List page (Bisa di-share & dilihat publik tanpa login)
     */
    public function publicPricesToken(Request $request, $token)
    {
        $request->merge(['token' => $token]);
        return $this->publicPrices($request);
    }

    public function publicPricesTokenJson(Request $request, $token)
    {
        $request->merge(['token' => $token]);
        return $this->publicPricesJson($request);
    }

    public function publicPricesJson(Request $request)
    {
        $token = $request->input('token') ?? $request->route('token');
        $activeLink = null;

        if ($token) {
            $links = Cache::get('generated_price_links', []);
            $updated = false;
            foreach ($links as &$item) {
                if (($item['token'] ?? '') === $token) {
                    $activeLink = $item;
                    
                    if (!empty($item['expires_at'])) {
                        if (Carbon::now()->greaterThan(Carbon::parse($item['expires_at']))) {
                            return response()->json([
                                'status'  => false,
                                'message' => 'Tautan daftar harga telah kadaluarsa.'
                            ], 410);
                        }
                    }

                    $item['hits'] = ($item['hits'] ?? 0) + 1;
                    $updated = true;
                    break;
                }
            }
            if ($updated) {
                Cache::forever('generated_price_links', $links);
            }
        }

        $filter = $request->input('filter', 'semua');
        $productCode = $request->input('product_code', '');
        // Hanya token resmi yang berhak membuka level tertentu. Tanpa token selalu harga umum ('')
        $kodeLevel = $activeLink ? ($activeLink['level'] ?? '') : '';

        $pricesRes = $this->otomaXService->call('GetDaftarHargaRs', [
            'kode_reseller' => '',
            'kode_level'    => $kodeLevel,
            'kode_produk'   => $productCode,
            'sort_filter'   => $filter
        ]);
        $prices = $this->parseResponse($pricesRes, []);
        $pricesList = is_array($prices) ? array_values($prices) : [];

        return response()->json([
            'status'       => true,
            'label'        => $activeLink['label'] ?? 'Daftar Harga Resmi',
            'level'        => !empty($kodeLevel) ? strtoupper($kodeLevel) : 'UMUM',
            'total_produk' => count($pricesList),
            'updated_at'   => Carbon::now()->format('Y-m-d H:i:s'),
            'data'         => $pricesList
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function publicPrices(Request $request)
    {
        $token = $request->input('token') ?? $request->route('token');
        $activeLink = null;

        if ($token) {
            $links = Cache::get('generated_price_links', []);
            $updated = false;
            foreach ($links as &$item) {
                if (($item['token'] ?? '') === $token) {
                    $activeLink = $item;
                    
                    // Cek masa berlaku kadaluarsa
                    if (!empty($item['expires_at'])) {
                        if (Carbon::now()->greaterThan(Carbon::parse($item['expires_at']))) {
                            return view('webreport.price-expired', [
                                'link' => $item,
                                'is_public' => true
                            ]);
                        }
                    }

                    // Tambah statistik kunjungan (hits)
                    $item['hits'] = ($item['hits'] ?? 0) + 1;
                    $updated = true;
                    break;
                }
            }
            if ($updated) {
                Cache::forever('generated_price_links', $links);
            }
        }

        $filter = $request->input('filter', 'semua');
        $productCode = $request->input('product_code', '');
        
        // Hanya tautan token terdaftar yang dapat memuat level tertentu. URL publik tanpa token selalu terkunci harga umum ('')
        $kodeLevel = $activeLink ? ($activeLink['level'] ?? '') : '';

        $pricesRes = $this->otomaXService->call('GetDaftarHargaRs', [
            'kode_reseller' => '',
            'kode_level'    => $kodeLevel,
            'kode_produk'   => $productCode,
            'sort_filter'   => $filter
        ]);
        $prices = $this->parseResponse($pricesRes, []);
        $levelsRes = $this->otomaXService->call('GetLevels');
        $levels = $this->parseResponse($levelsRes, []);

        return view('webreport.prices', [
            'active_tab'           => 'prices',
            'active_reseller_code' => '',
            'filter'               => $filter,
            'product_code'         => $productCode,
            'kode_level'           => $kodeLevel,
            'levels'               => is_array($levels) ? $levels : [],
            'prices'               => is_array($prices) ? $prices : [],
            'is_public'            => true,
            'active_link'          => $activeLink
        ]);
    }



    /**
     * Action handlers for Quick Actions on Dashboard (Add points, test PIN, top-up)
     */
    public function quickAction(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        if (!filter_var(env('OTOMAX_MOCK_MODE', false), FILTER_VALIDATE_BOOLEAN)) {
            Log::warning("SECURITY: Blocked quickAction [{$request->input('action')}] in production mode from IP: {$request->ip()}");
            return back()->with('error', 'Fitur simulasi dinonaktifkan pada mode produksi.');
        }

        $action = $request->input('action');
        $activeCode = $this->getActiveReseller();

        switch ($action) {
            case 'tambah_saldo':
                $jumlah = (int)$request->input('jumlah', 10000);
                $dompet = $request->input('kode_dompet', 'A');
                
                // Get current saldo for verification as requested by TambahSaldo endpoint guidelines
                $saldoRes = $this->otomaXService->call('GetSaldoRs', ['kode' => $activeCode]);
                $saldoData = $this->parseResponse($saldoRes);
                $currentSaldo = $saldoData ? ($saldoData['saldo'] ?? 0) : 0;

                $res = $this->otomaXService->call('TambahSaldo', [
                    'kode_reseller' => $activeCode,
                    'jumlah' => $jumlah,
                    'saldo' => $currentSaldo,
                    'keterangan' => 'Simulated Webreport Topup',
                    'kode_dompet' => $dompet
                ]);
                
                if ($this->parseResponse($res)) {
                    return back()->with('success', 'Saldo berhasil ditambahkan! Rp ' . number_format($jumlah, 0, ',', '.'));
                }
                
                $err = $res['desc'] ?? $res['message'] ?? 'Gagal menambah saldo';
                return back()->with('error', $err);

            case 'tambah_poin':
                $jumlah = (int)$request->input('jumlah', 5);
                $res = $this->otomaXService->call('TambahPoin', [
                    'kode_reseller' => $activeCode,
                    'jumlah' => $jumlah
                ]);
                
                if ($this->parseResponse($res)) {
                    return back()->with('success', $jumlah . ' Poin berhasil ditambahkan!');
                }
                
                $err = $res['desc'] ?? $res['message'] ?? 'Gagal menambah poin';
                return back()->with('error', $err);

            case 'test_pin':
                $pin = $request->input('pin', '123456');
                $res = $this->otomaXService->call('TesPinRs', [
                    'kode' => $activeCode,
                    'pin' => $pin
                ]);
                
                if ($this->parseResponse($res)) {
                    return back()->with('success', 'PIN Valid!');
                }
                
                $err = $res['desc'] ?? $res['message'] ?? 'PIN Salah!';
                return back()->with('error', $err);

            case 'register_reseller':
                $name = $request->input('name');
                $pengirim = $request->input('pengirim');
                $tipe = $request->input('tipe_pengirim', 'W');
                $pin = $request->input('pin', '123456');

                $res = $this->otomaXService->call('InsertReseller', [
                    'name' => $name,
                    'pengirim' => $pengirim,
                    'tipe_pengirim' => $tipe,
                    'pin' => $pin
                ]);

                $reseller = $this->parseResponse($res);
                if ($reseller) {
                    $newCode = $reseller['kode_reseller'] ?? $reseller['kode'] ?? '';
                    Session::put('logged_in_reseller_code', $newCode);
                    return redirect()->route('webreport.dashboard')->with('success', 'Pendaftaran Berhasil! Login sebagai ' . $name);
                }
                
                $err = $res['desc'] ?? $res['message'] ?? 'Registrasi gagal';
                return back()->with('error', $err);
        }

        return back()->with('error', 'Aksi gagal dieksekusi atau parameter salah.');
    }

    /**
     * Serve portal branding logo dynamically
     */
    public function getLogo()
    {
        $logoPath = \Illuminate\Support\Facades\Cache::get('site_logo_path');
        if ($logoPath && \Illuminate\Support\Facades\Storage::disk('local')->exists($logoPath)) {
            $path = \Illuminate\Support\Facades\Storage::disk('local')->path($logoPath);
            $mime = mime_content_type($path) ?: 'image/png';
            return response()->file($path, [
                'Content-Type' => $mime,
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
        }

        $files = \Illuminate\Support\Facades\Storage::disk('local')->files('branding');
        if (!empty($files)) {
            $path = \Illuminate\Support\Facades\Storage::disk('local')->path($files[0]);
            $mime = mime_content_type($path) ?: 'image/png';
            return response()->file($path, [
                'Content-Type' => $mime,
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
        }

        return response()->json(['error' => 'Not found'], 404);
    }

    public function settings(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $activeCode = $this->getActiveReseller();
        $rsResult = $this->otomaXService->call('GetRs', ['kode' => $activeCode]);
        $reseller = $rsResult['ok'] ? $rsResult['result'] : [];

        return view('webreport.settings', [
            'active_tab' => 'settings',
            'active_reseller_code' => $activeCode,
            'reseller' => $reseller
        ]);
    }

    public function sendSettingsOtp(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $activeCode = $this->getActiveReseller();
        $throttleKey = 'settings_otp_send_' . $activeCode . '_' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return response()->json([
                'status'  => 'error',
                'message' => "Terlalu banyak permintaan OTP. Silakan tunggu {$seconds} detik."
            ], 429);
        }

        $rsResult = $this->otomaXService->call('GetRs', ['kode' => $activeCode]);
        $reseller = $rsResult['ok'] ? $rsResult['result'] : [];

        $email = $reseller['email'] ?? null;
        if (empty($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email Anda belum terdaftar di sistem. Silakan hubungi Customer Service.'
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);

        $otp = (string)random_int(100000, 999999);
        Session::put('settings_otp', $otp);
        Session::put('settings_otp_email', $email);
        Session::put('settings_otp_expires', time() + 600);
        Session::put('settings_otp_attempts', 0);

        $sent = MailService::sendOtp($email, $otp, 'Perubahan Data Keamanan');

        $maskedEmail = substr($email, 0, 3) . '***' . strstr($email, '@');

        return response()->json([
            'status'  => 'success',
            'message' => $sent
                ? "OTP berhasil dikirim ke {$maskedEmail}. Silakan cek Inbox atau folder Spam."
                : "OTP telah dibuat (mode log). Cek laravel.log karena Gmail belum dikonfigurasi di admin panel."
        ]);
    }

    /**
     * Validate callback URL against SSRF (blocks loopback, private IPs, and non-HTTP/HTTPS schemes)
     */
    protected function isSafeCallbackUrl(?string $url): bool
    {
        if (empty($url)) {
            return true;
        }
        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? '');
        $host   = strtolower($parsed['host'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true) || empty($host)) {
            return false;
        }
        if (in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1', '[::1]', '169.254.169.254'], true) || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return false;
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }
        return true;
    }

    public function updateSettings(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $activeCode = $this->getActiveReseller();
        $throttleKey = 'settings_otp_verify_' . $activeCode . '_' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withInput()->with('error', "Terlalu banyak percobaan OTP. Silakan coba lagi dalam {$seconds} detik.");
        }
        
        $validated = $request->validate([
            'otp' => 'required|string|size:6',
            'pin' => 'nullable|digits_between:4,8',
            'pin_confirm' => 'nullable|required_with:pin|same:pin',
            'alamat_ip' => 'nullable|string|max:100',
            'password_ip' => 'nullable|string|max:100',
            'url_report' => 'nullable|url|max:255',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus berupa 6 digit.',
            'pin.digits_between' => 'PIN baru harus berupa 4-8 digit angka.',
            'pin_confirm.same' => 'Konfirmasi PIN baru tidak cocok.',
            'url_report.url' => 'Format URL Callback tidak valid.',
        ]);

        if (!$this->isSafeCallbackUrl($validated['url_report'] ?? null)) {
            return back()->withInput()->withErrors(['url_report' => 'URL Callback tidak diizinkan mengarah ke jaringan lokal / internal (SSRF Protection).']);
        }

        $sessionOtp = (string)Session::get('settings_otp', '');
        $otpExpires = (int)Session::get('settings_otp_expires', 0);
        $otpAttempts = (int)Session::get('settings_otp_attempts', 0);

        if (empty($sessionOtp) || time() > $otpExpires || $otpAttempts >= 5) {
            Session::forget(['settings_otp', 'settings_otp_email', 'settings_otp_expires', 'settings_otp_attempts']);
            return back()->withInput()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa atau melebihi batas percobaan. Silakan minta OTP baru!']);
        }

        if (!hash_equals($sessionOtp, (string)$request->input('otp'))) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
            Session::put('settings_otp_attempts', $otpAttempts + 1);
            return back()->withInput()->withErrors(['otp' => 'Kode OTP salah!']);
        }

        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

        // Clear OTP once verified
        Session::forget(['settings_otp', 'settings_otp_email', 'settings_otp_expires', 'settings_otp_attempts']);

        // Prepare updates
        $updateData = ['kode' => $activeCode];
        if (!empty($validated['pin'])) {
            $updateData['pin'] = $validated['pin'];
        }
        if (isset($validated['alamat_ip'])) {
            $updateData['alamat_ip'] = strip_tags(trim($validated['alamat_ip']));
        }
        if (!empty($validated['password_ip'])) {
            $updateData['password_ip'] = $validated['password_ip'];
        }
        if (isset($validated['url_report'])) {
            $updateData['url_report'] = trim($validated['url_report']);
        }

        $res = $this->otomaXService->call('UpdateRs', $updateData);

        if ($res['ok']) {
            Log::info("AUDIT: Reseller [{$activeCode}] updated security settings from IP: {$request->ip()}");
            return redirect()->route('webreport.settings')->with('success', 'Pengaturan keamanan berhasil diperbarui.');
        }

        return back()->withInput()->with('error', $res['desc'] ?? 'Gagal memperbarui pengaturan keamanan.');
    }
    public function apiDocs(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }
        $activeCode = $this->getActiveReseller();
        return view('webreport.api-docs', [
            'active_tab' => 'api-docs',
            'active_reseller_code' => $activeCode
        ]);
    }

    /**
     * Cetak Struk Transaksi (Public Link / Reseller Struk)
     */
    public function printReceipt(Request $request, $trxid)
    {
        $throttleKey = 'receipt_view_' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 30)) {
            return response()->view('errors.404', [
                'title'   => 'Terlalu Banyak Permintaan',
                'message' => 'Silakan tunggu beberapa saat sebelum mencetak struk kembali.'
            ], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        if (!preg_match('/^[A-Za-z0-9\-_]{1,40}$/', (string)$trxid)) {
            return response()->view('errors.404', [
                'message' => 'Format ID Transaksi tidak valid.'
            ], 404);
        }

        $receiptService = app(\App\Services\ReceiptService::class);
        
        $trxRes = $this->otomaXService->call('GetSingleTransaction', ['trxid' => $trxid]);
        if (!($trxRes['ok'] ?? false)) {
            return response()->view('errors.404', [
                'message' => 'Transaksi dengan ID ' . e($trxid) . ' tidak ditemukan di sistem.'
            ], 404);
        }

        $trx = $trxRes['result'];
        
        // Cari aturan logo & kategori struk yang cocok
        $rule = $receiptService->findMatchingRule($trx);

        if (!$rule || !in_array(strtoupper(trim($rule['category'] ?? '')), ['PRABAYAR', 'PASCA BAYAR', 'BANK'], true)) {
            return response()->view('errors.404', [
                'title'   => 'Struk Belum Tersedia',
                'message' => 'Fitur cetak struk belum tersedia untuk jenis transaksi ini.'
            ], 404);
        }

        // Validasi transaksi: HANYA transaksi Sukses yang boleh mencetak struk
        if (!$receiptService->isPrintable($trx)) {
            return response()->view('errors.404', [
                'title'   => 'Struk Belum Tersedia',
                'message' => 'Struk hanya dapat dicetak untuk transaksi yang telah berhasil (Sukses).'
            ], 403);
        }

        // Jika ada override admin fee via parameter URL (?admin=3500)
        if ($request->has('admin') && is_numeric($request->input('admin'))) {
            $adminOverride = (float)$request->input('admin');
            if ($adminOverride >= 0 && $adminOverride <= 1000000) {
                $rule['default_admin'] = $adminOverride;
            }
        }

        // Parse semua data terstruktur struk
        $receipt = $receiptService->parseReceipt($trx, $rule);

        return view('webreport.receipt', compact('receipt', 'trx', 'rule'));
    }
}

