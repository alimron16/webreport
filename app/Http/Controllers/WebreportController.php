<?php

namespace App\Http\Controllers;

use App\Services\OtomaXService;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class WebreportController extends Controller
{
    use \App\Traits\ValidatesRecaptcha;

    protected OtomaXService $otomaXService;

    public function __construct(OtomaXService $otomaXService)
    {
        $this->otomaXService = $otomaXService;
    }

    /**
     * Show login page
     */
    public function loginView()
    {
        if (Session::has('logged_in_reseller_code')) {
            return redirect()->route('webreport.dashboard');
        }
        return view('webreport.login');
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
        // Rate Limiter Check (5 attempts per 5 minutes per IP)
        $throttleKey = 'webreport_login_' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.")->withInput();
        }

        // Validate reCAPTCHA
        if (!$this->validateRecaptcha($request)) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
            return back()->with('error', 'Validasi CAPTCHA gagal. Silakan centang kotak "I\'m not a robot".')->withInput();
        }

        $kode = strtolower($request->input('kode_reseller'));
        $pin = $request->input('pin');

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
                Session::put('logged_in_reseller_code', $kode);
                $displayName = $reseller['nama'] ?? $reseller['name'] ?? $kode;
                return redirect()->route('webreport.dashboard')->with('success', 'Selamat datang kembali, ' . $displayName);
            }
        }

        // If OtomaX login failed or reseller not found, check local Registration table for pending users
        // The user might have entered their email or no_hp instead of a Kode Reseller
        $pendingUser = \App\Models\Registration::where(function($query) use ($kode) {
                $query->where('email', $kode)->orWhere('no_hp', $kode);
            })
            ->where('pin', $pin)
            ->first();

        if ($pendingUser) {
            if ($pendingUser->status === 'pending') {
                \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
                Session::put('pending_login_id', $pendingUser->id);
                return redirect()->route('webreport.dashboard');
            } elseif ($pendingUser->status === 'approved') {
                \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
                return back()->with('error', 'Akun Anda sudah disetujui. Silakan login menggunakan Kode Reseller yang telah dikirim ke email/WA Anda.');
            } elseif ($pendingUser->status === 'rejected') {
                \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
                return back()->with('error', 'Pendaftaran Anda ditolak. Silakan hubungi admin.');
            }
        }

        // If both OtomaX and local check fail, log hit and return appropriate error
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
        $errorDesc = 'PIN yang Anda masukkan salah atau Akun tidak ditemukan!';
        if (isset($verification)) {
            $errorDesc = $verification['desc'] ?? $verification['message'] ?? $errorDesc;
        } elseif (!$reseller) {
            $errorDesc = $resResult['desc'] ?? $resResult['message'] ?? 'Kode Reseller / Email / No. HP tidak terdaftar!';
        }
        
        return back()->with('error', $errorDesc);
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
        Session::forget('logged_in_reseller_code');
        Session::forget('pending_login_id');
        return redirect()->route('webreport.login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Helper to check authentication in controller actions
     */
    protected function checkAuth()
    {
        if (Session::has('pending_login_id')) {
            return null; // Let the action handle it
        }
        if (!Session::has('logged_in_reseller_code')) {
            return redirect()->route('webreport.login');
        }
        return null;
    }

    /**
     * Handle document uploads for pending registrations
     */
    public function uploadDocuments(Request $request)
    {
        if (!Session::has('pending_login_id')) {
            return redirect()->route('webreport.login');
        }

        $reg = \App\Models\Registration::find(Session::get('pending_login_id'));
        if (!$reg || $reg->status !== 'pending') {
            Session::forget('pending_login_id');
            return redirect()->route('webreport.login');
        }

        $request->validate([
            'foto_ktp'  => 'required|file|mimes:jpg,jpeg,png|max:3072',
            'berkas'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:3072',
            'foto_npwp' => 'nullable|file|mimes:jpg,jpeg,png|max:3072',
        ], [
            'foto_ktp.required' => 'Foto KTP wajib diunggah.',
            'foto_ktp.mimes'    => 'Foto KTP harus berformat JPG atau PNG.',
            'foto_ktp.max'      => 'Ukuran Foto KTP maksimal 3MB.',
            'berkas.mimes'      => 'Berkas PKS harus berformat PDF, JPG, PNG, atau DOC/DOCX.',
            'berkas.max'        => 'Ukuran berkas PKS maksimal 3MB.',
            'foto_npwp.mimes'   => 'Foto NPWP harus berformat JPG atau PNG.',
            'foto_npwp.max'     => 'Ukuran Foto NPWP maksimal 3MB.',
        ]);

        $safeName = str_replace(' ', '_', $reg->nama);

        if ($request->hasFile('foto_ktp')) {
            $fileKtp = $request->file('foto_ktp');
            $nameKtp = time() . '_ktp_' . $safeName . '.' . $fileKtp->getClientOriginalExtension();
            $reg->foto_ktp_path = $fileKtp->storeAs('registrations', $nameKtp, 'local');
        }

        if ($request->hasFile('berkas')) {
            $fileBerkas = $request->file('berkas');
            $nameBerkas = time() . '_berkas_' . $safeName . '.' . $fileBerkas->getClientOriginalExtension();
            $reg->berkas_path = $fileBerkas->storeAs('registrations', $nameBerkas, 'local');
            $reg->berkas_nama = $fileBerkas->getClientOriginalName();
        }

        if ($request->hasFile('foto_npwp')) {
            $fileNpwp = $request->file('foto_npwp');
            $nameNpwp = time() . '_npwp_' . $safeName . '.' . $fileNpwp->getClientOriginalExtension();
            $reg->foto_npwp_path = $fileNpwp->storeAs('registrations', $nameNpwp, 'local');
        }

        $reg->save();

        return redirect()->back()->with('success', 'Berkas berhasil diunggah. Tim kami akan segera meninjau akun Anda.');
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

        if (Session::has('pending_login_id')) {
            $reg = \App\Models\Registration::find(Session::get('pending_login_id'));
            if (!$reg || $reg->status !== 'pending') {
                Session::forget('pending_login_id');
                return redirect()->route('webreport.login');
            }
            return view('webreport.pending-review', compact('reg'));
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

        // List all registered resellers (for switcher)
        $resellerList = [];
        if (env('OTOMAX_MOCK_MODE', true) || empty(env('OTOMAX_API_URL'))) {
            $resellerList = Session::get('otomax_mock_resellers', []);
        } else {
            // For live mode, we can show the logged in user as the switcher options
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
        $limit = (int)$request->input('limit', 20);
        $tujuan = $request->input('tujuan', '');
        $produk = $request->input('produk', '');
        $refId = $request->input('ref_id', '');
        $status = $request->input('status', '');

        $trxResult = $this->otomaXService->call('GetTrxHistory', [
            'kode_reseller' => $activeCode,
            'tgl_awal' => $startDate,
            'tgl_akhir' => $endDate,
            'limit' => $limit,
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
            'bank' => 'required|string',
        ]);

        $activeCode = $this->getActiveReseller();
        $nominal = $request->input('nominal');
        $bank = $request->input('bank');

        $res = $this->otomaXService->call('RequestTiketDeposit', [
            'kode_reseller' => $activeCode,
            'nominal' => $nominal,
            'bank' => $bank
        ]);

        if ($res['ok']) {
            $data = $res['result'];
            return back()->with('tiket_success', [
                'nominal' => $data['nominal'],
                'bank' => $data['bank']
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
        if (!Session::has('logged_in_reseller_code')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $detailsResult = $this->otomaXService->call('GetTrx', ['trxid' => (int)$trxid]);
        $details = $this->parseResponse($detailsResult);

        if (!$details) {
            return response()->json([
                'status' => 'error', 
                'message' => $detailsResult['desc'] ?? $detailsResult['message'] ?? 'Transaction not found'
            ]);
        }

        return response()->json($details);
    }

    /**
     * Product price list page
     */
    public function prices(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $activeCode = $this->getActiveReseller();
        $filter = $request->input('filter', 'semua');
        $productCode = $request->input('product_code', '');

        $pricesRes = $this->otomaXService->call('GetDaftarHargaRs', [
            'kode_reseller' => $activeCode,
            'kode_produk' => $productCode,
            'sort_filter' => $filter
        ]);
        $prices = $this->parseResponse($pricesRes, []);

        return view('webreport.prices', [
            'active_tab' => 'prices',
            'active_reseller_code' => $activeCode,
            'filter' => $filter,
            'product_code' => $productCode,
            'prices' => is_array($prices) ? $prices : []
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
            return response()->file(\Illuminate\Support\Facades\Storage::disk('local')->path($logoPath));
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
        $rsResult = $this->otomaXService->call('GetRs', ['kode' => $activeCode]);
        $reseller = $rsResult['ok'] ? $rsResult['result'] : [];

        $email = $reseller['email'] ?? null;
        if (empty($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email Anda belum terdaftar di sistem. Silakan hubungi Customer Service.'
            ]);
        }

        $otp = (string)rand(100000, 999999);
        Session::put('settings_otp', $otp);
        Session::put('settings_otp_email', $email);

        $sent = MailService::sendOtp($email, $otp, 'Perubahan Data Keamanan');

        $maskedEmail = substr($email, 0, 3) . '***' . strstr($email, '@');

        return response()->json([
            'status'  => 'success',
            'message' => $sent
                ? "OTP berhasil dikirim ke {$maskedEmail}. Silakan cek Inbox atau folder Spam."
                : "OTP telah dibuat (mode log). Cek laravel.log karena Gmail belum dikonfigurasi di admin panel."
        ]);
    }

    public function updateSettings(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $activeCode = $this->getActiveReseller();
        
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

        $sessionOtp = Session::get('settings_otp');
        if (empty($sessionOtp) || $request->input('otp') !== $sessionOtp) {
            return back()->withInput()->withErrors(['otp' => 'Kode OTP salah atau telah kedaluwarsa!']);
        }

        // Clear OTP once verified
        Session::forget('settings_otp');
        Session::forget('settings_otp_email');

        // Prepare updates
        $updateData = ['kode' => $activeCode];
        if (!empty($validated['pin'])) {
            $updateData['pin'] = $validated['pin'];
        }
        if (isset($validated['alamat_ip'])) {
            $updateData['alamat_ip'] = $validated['alamat_ip'];
        }
        if (!empty($validated['password_ip'])) {
            $updateData['password_ip'] = $validated['password_ip'];
        }
        if (isset($validated['url_report'])) {
            $updateData['url_report'] = $validated['url_report'];
        }

        $res = $this->otomaXService->call('UpdateRs', $updateData);

        if ($res['ok']) {
            return redirect()->route('webreport.settings')->with('success', 'Pengaturan keamanan berhasil diperbarui.');
        }

        return back()->withInput()->with('error', $res['desc'] ?? 'Gagal memperbarui pengaturan keamanan.');
    }
}
