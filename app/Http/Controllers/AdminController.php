<?php

namespace App\Http\Controllers;


use App\Services\OtomaXService;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminController extends Controller
{
    use \App\Traits\ValidatesRecaptcha;

    protected OtomaXService $otomaXService;

    public function __construct(OtomaXService $otomaXService)
    {
        $this->otomaXService = $otomaXService;
    }

    // ── Auth ──────────────────────────────────────────

    public function loginView()
    {
        if (Session::has('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        $rcEnabled = Cache::get('recaptcha_enabled');
        if ($rcEnabled === null) {
            $rcEnabled = (\App\Models\Setting::get('recaptcha_enabled') === '1' || \App\Models\Setting::get('recaptcha_enabled') === true);
        } else {
            $rcEnabled = filter_var($rcEnabled, FILTER_VALIDATE_BOOLEAN);
        }
        $rcSiteKey = Cache::get('recaptcha_site_key') ?: \App\Models\Setting::get('recaptcha_site_key', '');
        $showRecaptcha = $rcEnabled && !empty($rcSiteKey);

        return view('admin.login', compact('showRecaptcha', 'rcSiteKey'));
    }

    public function login(Request $request)
    {
        $throttleKey = 'admin_login_' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            \Illuminate\Support\Facades\Log::warning("Admin login locked by RateLimiter from IP {$request->ip()}");
            return back()->with('error', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
        }

        if (!$this->validateRecaptcha($request)) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
            \Illuminate\Support\Facades\Log::warning("Admin login failed CAPTCHA from IP {$request->ip()}");
            return back()->with('error', 'Validasi CAPTCHA gagal.')->withInput();
        }

        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:100',
        ]);

        $username = strip_tags(trim($request->input('username')));
        $password = (string)$request->input('password');

        $credentials = $this->getAdminCredentials();
        $expectedUsername = (string)$credentials['username'];
        $expectedHash = (string)$credentials['password_hash'];

        // Timing-safe username comparison
        $isUserValid = hash_equals(strtolower($expectedUsername), strtolower($username));

        // Support both Bcrypt/Argon2 hashes and timing-safe plain comparison
        $isPasswordValid = false;
        if (str_starts_with($expectedHash, '$2y$') || str_starts_with($expectedHash, '$argon2')) {
            $isPasswordValid = Hash::check($password, $expectedHash);
        } else {
            $isPasswordValid = hash_equals($expectedHash, $password);
        }

        if ($isUserValid && $isPasswordValid) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $request->session()->regenerate(); // Prevent Session Fixation
            Session::put('admin_logged_in', true);
            Session::put('admin_username', $expectedUsername);
            Session::put('admin_ua_hash', hash('sha256', (string)$request->userAgent()));

            \Illuminate\Support\Facades\Log::info("AUDIT: Admin login SUCCESS for [{$username}] from IP: {$request->ip()}");
            return redirect()->route('admin.dashboard');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
        \Illuminate\Support\Facades\Log::warning("AUDIT: Admin login FAILED for [{$username}] from IP: {$request->ip()}");
        return back()->with('error', 'Username atau password salah.');
    }

    /**
     * Mengambil kredensial login admin aktif secara dinamis.
     * Prioritas:
     * 1. Database tabel users (jika ada)
     * 2. Cache persistent (jika pernah diubah via admin UI)
     * 3. Fallback default awal (.env atau admin / admin123)
     */
    public function getAdminCredentials(): array
    {
        // 1. Ambil kredensial admin dari database tabel users (terenkripsi Hash Bcrypt)
        try {
            $user = \App\Models\User::first();
            if (!$user) {
                // Otomatis buat admin di database jika belum ada
                $user = new \App\Models\User();
                $user->name = 'admin';
                $user->email = 'admin@nawasena.id';
                $user->password = Hash::make('admin123');
                $user->save();
            }

            return [
                'username' => $user->name ?: ($user->email ?: 'admin'),
                'password_hash' => $user->password,
                'source' => 'database',
                'user_id' => $user->id,
            ];
        } catch (\Throwable $e) {
            // Fallback aman dari Cache persistent jika terjadi kendala sementara pada koneksi DB
            $cachedUser = Cache::get('admin_account_username', 'admin');
            $cachedHash = Cache::get('admin_account_password_hash', Hash::make('admin123'));
            return [
                'username' => $cachedUser,
                'password_hash' => $cachedHash,
                'source' => 'database_cache',
                'user_id' => null,
            ];
        }
    }

    public function logout(Request $request)
    {
        $adminUser = Session::get('admin_username', 'unknown');
        \Illuminate\Support\Facades\Log::info("AUDIT: Admin logout for [{$adminUser}] from IP: {$request->ip()}");

        Session::forget(['admin_logged_in', 'admin_username']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // ── Middleware check ──────────────────────────────

    protected function guardAdmin()
    {
        if (!Session::has('admin_logged_in')) {
            return redirect()->route('admin.login');
        }
        $expectedUa = Session::get('admin_ua_hash');
        $currentUa = hash('sha256', (string)request()->userAgent());
        if (!empty($expectedUa) && !hash_equals($expectedUa, $currentUa)) {
            Log::warning("SECURITY: Admin session User-Agent mismatch for [" . Session::get('admin_username') . "] from IP: " . request()->ip());
            Session::forget(['admin_logged_in', 'admin_username', 'admin_ua_hash']);
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('admin.login')->with('error', 'Sesi administrator berakhir karena perubahan perangkat/browser.');
        }
        if (!app(\App\Services\LicenseService::class)->isValid()) {
            return redirect()->route('license.show');
        }
        return null;
    }

    /**
     * Neutralize CSV Formula Injection (Excel / LibreOffice macro execution via =, +, -, @, Tab, CR)
     */
    protected function sanitizeCsvRow(array $row): array
    {
        return array_map(function ($cell) {
            if (!is_string($cell) || $cell === '') {
                return $cell;
            }
            $firstChar = $cell[0];
            if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
                return "'" . $cell;
            }
            return $cell;
        }, $row);
    }

    /**
     * Sanitize SVG content to strip embedded scripts, event handlers, or foreignObject tags
     */
    protected function isSafeSvgContent(string $content): bool
    {
        $lower = strtolower($content);
        $dangerousPatterns = ['<script', 'javascript:', 'vbscript:', 'data:text/html', '<foreignobject', '<iframe', '<embed', '<object', 'onload=', 'onerror=', 'onclick=', 'onmouseover='];
        foreach ($dangerousPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return false;
            }
        }
        return true;
    }

    // ── Dashboard Ringkasan & KPI ─────────────────────────────
    public function dashboard(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $statsRes = $this->otomaXService->call('GetAdminDashboardStats');
        $stats = ($statsRes['ok'] ?? false) ? ($statsRes['result'] ?? []) : [];

        $active_tab = 'dashboard';
        return view('admin.dashboard', compact('stats', 'active_tab'));
    }

    // ── Laporan Pemakaian & Profit Agen ────────────────────────
    public function resellerProfitView(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $tgl_awal = $request->input('tgl_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tgl_akhir = $request->input('tgl_akhir', Carbon::now()->format('Y-m-d'));
        $rawMember = trim($request->input('kode_reseller', ''));
        $kode_reseller = $rawMember;

        // Jika format berasal dari autocomplete "KODE - NAMA"
        if (str_contains($rawMember, ' - ')) {
            $kode_reseller = trim(explode(' - ', $rawMember, 2)[0]);
        }

        $resellersRes = $this->otomaXService->call('GetAllResellers');
        $allResellers = ($resellersRes['ok'] ?? false) ? ($resellersRes['result'] ?? []) : [];

        // Cocokkan jika user mengetik nama lengkap reseller
        if (!empty($kode_reseller) && !empty($allResellers)) {
            $matched = null;
            foreach ($allResellers as $r) {
                if (strcasecmp($r['kode'] ?? '', $kode_reseller) === 0) {
                    $matched = $r['kode'];
                    break;
                }
            }
            if (!$matched) {
                foreach ($allResellers as $r) {
                    if (strcasecmp($r['nama'] ?? '', $kode_reseller) === 0) {
                        $matched = $r['kode'];
                        break;
                    }
                }
            }
            if ($matched) {
                $kode_reseller = $matched;
            }
        }

        $profit_filter = $request->input('profit_filter', 'all');
        $search = trim($request->input('search', ''));

        $profitRes = $this->otomaXService->call('GetAdminResellerProfit', [
            'tgl_awal' => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
            'kode_reseller' => $kode_reseller,
            'profit_filter' => $profit_filter,
            'search' => $search
        ]);

        $data = ($profitRes['ok'] ?? false) ? ($profitRes['result'] ?? []) : [
            'items' => [],
            'summary' => [
                'total_omset' => 0, 'total_modal' => 0, 'total_profit' => 0, 'avg_margin' => 0,
                'total_trx_sukses' => 0, 'total_trx_gagal' => 0, 'count_reseller' => 0
            ],
            'filter' => [
                'tgl_awal' => $tgl_awal, 'tgl_akhir' => $tgl_akhir, 'kode_reseller' => $kode_reseller,
                'profit_filter' => $profit_filter, 'search' => $search
            ]
        ];

        $items = $data['items'] ?? [];
        $summary = $data['summary'] ?? [];
        $active_tab = 'reseller-profit';

        return view('admin.reseller-profit', compact('items', 'summary', 'allResellers', 'tgl_awal', 'tgl_akhir', 'kode_reseller', 'profit_filter', 'search', 'active_tab'));
    }

    // ── Rincian Performa & Transaksi Agen (AJAX Popup) ────────
    public function resellerProfitDetailAjax(Request $request, $kode)
    {
        if ($r = $this->guardAdmin()) return response()->json(['ok' => false, 'desc' => 'Unauthorized'], 401);

        $tgl_awal = $request->input('tgl_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tgl_akhir = $request->input('tgl_akhir', Carbon::now()->format('Y-m-d'));

        $detailRes = $this->otomaXService->call('GetAdminResellerDetail', [
            'kode_reseller' => $kode,
            'tgl_awal' => $tgl_awal,
            'tgl_akhir' => $tgl_akhir
        ]);

        if (!($detailRes['ok'] ?? false)) {
            return response()->json(['ok' => false, 'desc' => $detailRes['desc'] ?? 'Data tidak ditemukan'], 404);
        }

        return response()->json($detailRes);
    }

    // ── Ekspor Laporan Profit Agen ke CSV / Excel ──────────────
    public function resellerProfitExport(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $tgl_awal = $request->input('tgl_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tgl_akhir = $request->input('tgl_akhir', Carbon::now()->format('Y-m-d'));
        $rawMember = trim($request->input('kode_reseller', ''));
        $kode_reseller = $rawMember;

        if (str_contains($rawMember, ' - ')) {
            $kode_reseller = trim(explode(' - ', $rawMember, 2)[0]);
        }

        $resellersRes = $this->otomaXService->call('GetAllResellers');
        $allResellers = ($resellersRes['ok'] ?? false) ? ($resellersRes['result'] ?? []) : [];

        if (!empty($kode_reseller) && !empty($allResellers)) {
            $matched = null;
            foreach ($allResellers as $r) {
                if (strcasecmp($r['kode'] ?? '', $kode_reseller) === 0) {
                    $matched = $r['kode'];
                    break;
                }
            }
            if (!$matched) {
                foreach ($allResellers as $r) {
                    if (strcasecmp($r['nama'] ?? '', $kode_reseller) === 0) {
                        $matched = $r['kode'];
                        break;
                    }
                }
            }
            if ($matched) {
                $kode_reseller = $matched;
            }
        }

        $profit_filter = $request->input('profit_filter', 'all');
        $search = trim($request->input('search', ''));

        $profitRes = $this->otomaXService->call('GetAdminResellerProfit', [
            'tgl_awal' => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
            'kode_reseller' => $kode_reseller,
            'profit_filter' => $profit_filter,
            'search' => $search
        ]);

        $items = ($profitRes['ok'] ?? false) ? ($profitRes['result']['items'] ?? []) : [];

        $filename = "laporan-profit-agen-{$tgl_awal}-sd-{$tgl_akhir}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($items, $tgl_awal, $tgl_akhir) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['LAPORAN PEMAKAIAN DAN PROFIT AGEN']);
            fputcsv($handle, ['Periode', "{$tgl_awal} s/d {$tgl_akhir}"]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'No',
                'Kode Reseller',
                'Nama Reseller',
                'Level',
                'Sisa Saldo (Rp)',
                'Total Trx',
                'Trx Sukses',
                'Trx Gagal',
                'Total Pemakaian/Omset (Rp)',
                'Modal Server (Rp)',
                'Profit/Laba Bersih (Rp)',
                'Margin (%)',
                'Status'
            ]);

            $no = 1;
            foreach ($items as $item) {
                fputcsv($handle, $this->sanitizeCsvRow([
                    $no++,
                    $item['kode'] ?? '',
                    $item['nama'] ?? '',
                    $item['kode_level'] ?? '',
                    $item['saldo'] ?? 0,
                    $item['total_trx'] ?? 0,
                    $item['trx_sukses'] ?? 0,
                    $item['trx_gagal'] ?? 0,
                    $item['omset'] ?? 0,
                    $item['modal'] ?? 0,
                    $item['profit'] ?? 0,
                    number_format($item['margin'] ?? 0, 2) . '%',
                    ($item['is_profit'] ?? false) ? 'Profit' : 'Rugi / Minus'
                ]));
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Manajemen Data Member & Agen ──────────────────────────
    public function resellersView(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');
        $level = trim($request->input('level', 'all'));
        $saldo_filter = $request->input('saldo_filter', 'all');
        $sort = $request->input('sort', 'saldo_desc');
        $page = max((int)$request->input('page', 1), 1);
        $per_page = min(max((int)$request->input('per_page', 25), 10), 100);

        $res = $this->otomaXService->call('GetAdminResellersList', [
            'search' => $search,
            'status' => $status,
            'level' => $level,
            'saldo_filter' => $saldo_filter,
            'sort' => $sort,
            'page' => $page,
            'per_page' => $per_page,
        ]);

        $data = ($res['ok'] ?? false) ? ($res['result'] ?? []) : [
            'items' => [], 'total' => 0, 'page' => 1, 'per_page' => $per_page, 'last_page' => 1,
            'kpi' => ['total_member' => 0, 'total_aktif' => 0, 'total_nonaktif' => 0, 'total_suspend' => 0, 'total_saldo' => 0, 'total_saldo_nol' => 0, 'total_saldo_kritis' => 0],
            'levels' => []
        ];

        $items = $data['items'] ?? [];
        $total = $data['total'] ?? 0;
        $last_page = $data['last_page'] ?? 1;
        $kpi = $data['kpi'] ?? [];
        $levels = $data['levels'] ?? [];
        $active_tab = 'resellers';

        return view('admin.resellers', compact('items', 'total', 'page', 'per_page', 'last_page', 'kpi', 'levels', 'search', 'status', 'level', 'saldo_filter', 'sort', 'active_tab'));
    }

    public function resellerProfileAjax(Request $request, $kode)
    {
        if ($r = $this->guardAdmin()) return response()->json(['ok' => false, 'desc' => 'Unauthorized'], 401);

        $res = $this->otomaXService->call('GetAdminResellerFullProfile', ['kode' => $kode]);
        if (!($res['ok'] ?? false)) {
            return response()->json(['ok' => false, 'desc' => $res['desc'] ?? 'Reseller tidak ditemukan'], 404);
        }

        return response()->json($res);
    }

    public function resellersExport(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');
        $level = trim($request->input('level', 'all'));
        $saldo_filter = $request->input('saldo_filter', 'all');
        $sort = $request->input('sort', 'saldo_desc');

        $res = $this->otomaXService->call('GetAdminResellersList', [
            'search' => $search,
            'status' => $status,
            'level' => $level,
            'saldo_filter' => $saldo_filter,
            'sort' => $sort,
            'page' => 1,
            'per_page' => 10000,
        ]);

        $items = ($res['ok'] ?? false) ? ($res['result']['items'] ?? []) : [];
        $filename = "daftar-member-agen-" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($items) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, ['DATA MEMBER DAN AGEN OTOMAX']);
            fputcsv($handle, ['Tanggal Ekspor', date('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'No', 'Kode Reseller', 'Nama Reseller', 'Nama Pemilik', 'Level',
                'Status', 'Nomor HP/WA', 'Saldo Saat Ini (Rp)', 'Komisi (Rp)', 'Poin',
                'Alamat', 'Tanggal Terdaftar'
            ]);

            $no = 1;
            foreach ($items as $r) {
                $statusText = 'Nonaktif';
                if (!empty($r['suspend'])) {
                    $statusText = 'Suspend';
                } elseif (!empty($r['aktif'])) {
                    $statusText = 'Aktif';
                }

                fputcsv($handle, $this->sanitizeCsvRow([
                    $no++,
                    $r['kode'] ?? '',
                    $r['nama'] ?? '',
                    $r['nama_pemilik'] ?? '',
                    $r['kode_level'] ?? '',
                    $statusText,
                    $r['nomor_hp'] ?? '',
                    $r['saldo'] ?? 0,
                    $r['komisi'] ?? 0,
                    $r['poin'] ?? 0,
                    $r['alamat'] ?? '',
                    $r['tgl_daftar'] ?? ''
                ]));
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Price Links Management ──────────────────────────────
    public function priceLinksView(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $links = Cache::get('generated_price_links', []);
        
        // Enrich links with live expiration status
        $links = array_map(function ($link) {
            $isExpired = false;
            if (!empty($link['expires_at'])) {
                $isExpired = Carbon::now()->greaterThan(Carbon::parse($link['expires_at']));
            }
            $link['is_expired'] = $isExpired;
            return $link;
        }, $links);

        $levelsRes = $this->otomaXService->call('GetLevels');
        $levels = ($levelsRes['ok'] ?? false) ? ($levelsRes['result'] ?? []) : [];

        return view('admin.price-links', compact('links', 'levels'));
    }

    public function createPriceLink(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $request->validate([
            'label'        => 'required|string|max:100',
            'level'        => 'nullable|string|max:30',
            'expiry_type'  => 'required|in:forever,1day,7days,14days,30days,90days,custom',
            'custom_date'  => 'nullable|date',
        ], [
            'label.required'       => 'Label atau nama tautan wajib diisi.',
            'expiry_type.required' => 'Masa berlaku tautan wajib ditentukan.',
        ]);

        $expiryType = $request->input('expiry_type');
        $expiresAt = null;

        if ($expiryType === '1day') {
            $expiresAt = Carbon::now()->addDay()->format('Y-m-d H:i:s');
        } elseif ($expiryType === '7days') {
            $expiresAt = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');
        } elseif ($expiryType === '14days') {
            $expiresAt = Carbon::now()->addDays(14)->format('Y-m-d H:i:s');
        } elseif ($expiryType === '30days') {
            $expiresAt = Carbon::now()->addDays(30)->format('Y-m-d H:i:s');
        } elseif ($expiryType === '90days') {
            $expiresAt = Carbon::now()->addDays(90)->format('Y-m-d H:i:s');
        } elseif ($expiryType === 'custom') {
            $custom = $request->input('custom_date');
            if ($custom) {
                $expiresAt = Carbon::parse($custom)->endOfDay()->format('Y-m-d H:i:s');
            }
        }

        $token = Str::random(8);
        $level = trim($request->input('level', ''));

        $newLink = [
            'id'          => uniqid(),
            'token'       => $token,
            'label'       => trim($request->input('label')),
            'level'       => $level,
            'url'         => url('/harga/' . $token),
            'created_at'  => Carbon::now()->format('Y-m-d H:i:s'),
            'expires_at'  => $expiresAt,
            'hits'        => 0,
        ];

        $links = Cache::get('generated_price_links', []);
        array_unshift($links, $newLink);
        Cache::forever('generated_price_links', $links);

        return redirect()->route('admin.price-links')->with('success', 'Tautan daftar harga publik berhasil di-generate dan disimpan.');
    }

    public function deletePriceLink(Request $request, $id)
    {
        if ($r = $this->guardAdmin()) return $r;

        $links = Cache::get('generated_price_links', []);
        $links = array_values(array_filter($links, fn($item) => ($item['id'] ?? '') !== $id));
        Cache::forever('generated_price_links', $links);

        return redirect()->route('admin.price-links')->with('success', 'Tautan daftar harga berhasil dihapus.');
    }



    // ── Upload PKS Template ────────────────────────────

    // ── System Logs ────────────────────────────────────

    public function clearSystemLogs(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
        }

        return back()->with('success', 'System logs telah dibersihkan.');
    }

    // ── Settings ───────────────────────────────────────
    
    public function uploadPks(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $request->validate([
            'pks_type' => 'required|in:perorangan,badan_usaha',
            'pks_file' => 'required|file|mimes:pdf,doc,docx,zip|max:5120',
        ], [
            'pks_type.required' => 'Tipe PKS wajib ditentukan.',
            'pks_type.in'       => 'Tipe PKS tidak valid.',
            'pks_file.required' => 'File PKS wajib dipilih.',
            'pks_file.mimes'    => 'File PKS harus berformat PDF, DOC, DOCX, atau ZIP.',
            'pks_file.max'      => 'Ukuran file PKS maksimal 5MB.',
        ]);

        $file = $request->file('pks_file');
        $type = $request->input('pks_type');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['pdf', 'doc', 'docx', 'zip'], true)) {
            return back()->withErrors(['pks_file' => 'Ekstensi file PKS tidak diizinkan.']);
        }
        $fileName = 'pks_agreement_' . $type . '.' . $ext;
        $path = $file->storeAs('pks', $fileName, 'local');

        \Illuminate\Support\Facades\Cache::forever('pks_template_path_' . $type, $path);
        \Illuminate\Support\Facades\Cache::forever('pks_template_name_' . $type, basename($file->getClientOriginalName()));

        $typeLabel = $type === 'badan_usaha' ? 'Badan Usaha / Badan Hukum' : 'Perorangan (Pribadi)';
        return back()->with('pks_success', 'File PKS ' . $typeLabel . ' berhasil diperbarui.');
    }

    // ── Update Portal Branding ─────────────────────────

    public function updateBranding(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $request->validate([
            'site_title'                     => 'required|string|max:100',
            'site_subtitle'                  => 'required|string|max:200',
            'whatsapp_number'                => 'nullable|string|max:30',
            'whatsapp_registration_message'  => 'nullable|string|max:500',
            'whatsapp_cs_message'            => 'nullable|string|max:500',
            'telegram_cs_username'           => 'nullable|string|max:100',
            'telegram_channel_url'           => 'nullable|string|max:200',
            'registration_mode'              => 'nullable|string|in:whatsapp,form,both',
            'hero_headline'                  => 'nullable|string|max:200',
            'hero_subheadline'               => 'nullable|string|max:500',
            'cs_working_hours'               => 'nullable|string|max:100',
            'site_logo'                      => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ], [
            'site_title.required'    => 'Nama portal wajib diisi.',
            'site_subtitle.required' => 'Deskripsi portal wajib diisi.',
            'site_logo.image'        => 'Logo harus berupa gambar (png, jpg, jpeg, svg).',
            'site_logo.max'          => 'Ukuran logo maksimal 2MB.',
        ]);

        \Illuminate\Support\Facades\Cache::forever('site_title', $request->input('site_title'));
        \Illuminate\Support\Facades\Cache::forever('site_subtitle', $request->input('site_subtitle'));
        \Illuminate\Support\Facades\Cache::forever('whatsapp_number', $request->input('whatsapp_number', ''));
        \Illuminate\Support\Facades\Cache::forever('whatsapp_registration_message', $request->input('whatsapp_registration_message', ''));
        \Illuminate\Support\Facades\Cache::forever('whatsapp_cs_message', $request->input('whatsapp_cs_message', ''));
        \Illuminate\Support\Facades\Cache::forever('telegram_cs_username', $request->input('telegram_cs_username', ''));
        \Illuminate\Support\Facades\Cache::forever('telegram_channel_url', $request->input('telegram_channel_url', ''));
        \Illuminate\Support\Facades\Cache::forever('registration_mode', $request->input('registration_mode', 'whatsapp'));
        \Illuminate\Support\Facades\Cache::forever('hero_headline', $request->input('hero_headline', ''));
        \Illuminate\Support\Facades\Cache::forever('hero_subheadline', $request->input('hero_subheadline', ''));
        \Illuminate\Support\Facades\Cache::forever('cs_working_hours', $request->input('cs_working_hours', ''));

        if ($request->has('remove_logo') && $request->input('remove_logo') == '1') {
            $oldPath = \Illuminate\Support\Facades\Cache::get('site_logo_path');
            if ($oldPath && \Illuminate\Support\Facades\Storage::disk('local')->exists($oldPath)) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($oldPath);
            }
            \Illuminate\Support\Facades\Cache::forget('site_logo_path');
            \Illuminate\Support\Facades\Cache::forget('site_logo_url');
        } elseif ($request->hasFile('site_logo')) {
            $file = $request->file('site_logo');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'svg', 'webp'], true)) {
                return back()->withErrors(['site_logo' => 'Ekstensi logo tidak diizinkan.']);
            }
            if ($ext === 'svg' && !$this->isSafeSvgContent((string)file_get_contents($file->getRealPath()))) {
                return back()->withErrors(['site_logo' => 'File SVG mengandung elemen skrip berbahaya (XSS).']);
            }
            $fileName = 'portal_logo.' . $ext;
            $path = $file->storeAs('branding', $fileName, 'local');
            
            \Illuminate\Support\Facades\Cache::forever('site_logo_path', $path);
            \Illuminate\Support\Facades\Cache::forever('site_logo_url', '/logo?t=' . time());
        }

        return back()->with('branding_success', 'Pengaturan branding, kontak WhatsApp & portal berhasil diperbarui.');
    }

    public function settingsView()
    {
        if ($r = $this->guardAdmin()) return $r;

        $otomaxDbConfig = [
            'host'         => \App\Models\Setting::get('otomax_db_host', '127.0.0.1'),
            'port'         => \App\Models\Setting::get('otomax_db_port', 1433),
            'database'     => \App\Models\Setting::get('otomax_db_database', 'otomax'),
            'username'     => \App\Models\Setting::get('otomax_db_username', 'sa'),
            'has_password' => !empty(\App\Models\Setting::get('otomax_db_password', '')),
        ];

        $mailConfig = [
            'from_address' => Cache::get('mail_from_address', env('MAIL_FROM_ADDRESS', '')),
            'from_name'    => Cache::get('mail_from_name', env('MAIL_FROM_NAME', '')),
            'username'     => Cache::get('mail_username', env('MAIL_USERNAME', '')),
            // password intentionally not returned to view for security
        ];

        $logPath = storage_path('logs/laravel.log');
        $logs = '';
        if (file_exists($logPath)) {
            $fileSize = filesize($logPath);
            $maxRead = 500 * 1024; // 500KB
            if ($fileSize > $maxRead) {
                $file = fopen($logPath, 'r');
                fseek($file, -($maxRead), SEEK_END);
                fgets($file); // skip first partial line
                while (!feof($file)) {
                    $logs .= fread($file, 8192);
                }
                fclose($file);
                $logs = "[LOG TRUNCATED - SHOWING LAST 500KB]\n\n" . $logs;
            } else {
                $logs = file_get_contents($logPath);
            }
        } else {
            $logs = 'Log file not found.';
        }

        $levelsRes = $this->otomaXService->call('GetLevels');
        $levels = ($levelsRes['ok'] ?? false) ? ($levelsRes['result'] ?? []) : [];

        $credentials = $this->getAdminCredentials();
        $currentAdminUsername = $credentials['username'];
        $adminCredSource = $credentials['source'];

        $rawRecaptchaSecret = Cache::get('recaptcha_secret_key');
        if (!$rawRecaptchaSecret) {
            $rawRecaptchaSecret = \App\Models\Setting::get('recaptcha_secret_key');
        }
        $recaptchaSecret = $rawRecaptchaSecret;
        if ($rawRecaptchaSecret) {
            while (is_string($recaptchaSecret) && str_starts_with($recaptchaSecret, 'eyJ')) {
                try {
                    $recaptchaSecret = \Illuminate\Support\Facades\Crypt::decryptString($recaptchaSecret);
                } catch (\Throwable $e) {
                    break;
                }
            }
        }

        $recaptchaEnabled = Cache::get('recaptcha_enabled');
        if ($recaptchaEnabled === null) {
            $recaptchaEnabled = (\App\Models\Setting::get('recaptcha_enabled') === '1' || \App\Models\Setting::get('recaptcha_enabled') === true);
        } else {
            $recaptchaEnabled = filter_var($recaptchaEnabled, FILTER_VALIDATE_BOOLEAN);
        }
        $recaptchaSiteKey = Cache::get('recaptcha_site_key') ?: \App\Models\Setting::get('recaptcha_site_key', '');

        $receiptService = app(\App\Services\ReceiptService::class);
        $receiptRules = $receiptService->getRules();
        $receiptStoreTitle = \App\Models\Setting::get('receipt_store_title', 'PAYMENT POINT ONLINE BANKING');
        $receiptFooterInfo = \App\Models\Setting::get('receipt_footer_info', '');
        $receiptCustomDomain = \App\Models\Setting::get('receipt_custom_domain', '');
        $receiptStealthMode = \App\Models\Setting::get('receipt_stealth_mode', '1') !== '0';

        return view('admin.settings', compact(
            'mailConfig', 'logs', 'levels', 'currentAdminUsername', 'adminCredSource', 
            'otomaxDbConfig', 'recaptchaEnabled', 'recaptchaSiteKey', 'recaptchaSecret', 
            'receiptRules', 'receiptStoreTitle', 'receiptFooterInfo', 'receiptCustomDomain', 'receiptStealthMode'
        ));
    }

    // ── Pengaturan Struk & Logo Produk ─────────────────────────
    public function updateReceiptSettings(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $rawCustomDomain = trim((string) $request->input('receipt_custom_domain', ''));
        $cleanDomain = '';
        if ($rawCustomDomain !== '') {
            if (!preg_match('#^https?://#i', $rawCustomDomain)) {
                $rawCustomDomain = 'https://' . $rawCustomDomain;
            }
            $parsed = parse_url($rawCustomDomain);
            if (empty($parsed['host'])) {
                return back()->withErrors(['target' => 'Format Domain Samaran Struk tidak valid (contoh yang benar: https://cetaklawdiaoja.id).'])->withInput();
            }
            $host = strtolower($parsed['host']);
            if ($host === strtolower((string) $request->getHost())) {
                return back()->withErrors(['target' => 'Domain Samaran Struk tidak boleh sama dengan domain utama Webreport yang sedang Anda gunakan. Gunakan domain berbeda.'])->withInput();
            }
            $scheme = strtolower($parsed['scheme'] ?? 'https');
            $port = isset($parsed['port']) ? (':' . $parsed['port']) : '';
            $cleanDomain = $scheme . '://' . $host . $port;
        }

        $stealthMode = $request->input('receipt_stealth_mode', '0') === '1' ? '1' : '0';

        \App\Models\Setting::set('receipt_store_title', trim($request->input('receipt_store_title', 'PAYMENT POINT ONLINE BANKING')));
        \App\Models\Setting::set('receipt_footer_info', trim($request->input('receipt_footer_info', '')));
        \App\Models\Setting::set('receipt_custom_domain', $cleanDomain);
        \App\Models\Setting::set('receipt_stealth_mode', $stealthMode);
        
        return back()->with('receipt_success', 'Pengaturan umum & domain samaran struk berhasil disimpan.');
    }

    public function storeReceiptRule(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $request->validate([
            'category'      => 'required|in:PRABAYAR,PASCA BAYAR,BANK',
            'name'          => 'nullable|string|max:100',
            'provider'      => 'nullable|string|max:100',
            'keywords'      => 'nullable|string|max:255',
            'bank_name'     => 'nullable|string|max:100',
            'default_admin' => 'nullable|numeric|min:0',
            'logo_file'     => 'nullable|image|max:2048',
            'parser_mode'   => 'nullable|string|in:auto,template,fields,delimiter,regex',
            'sn_template'   => 'nullable|string|max:1000',
            'sn_delimiter'  => 'nullable|string|max:10',
            'sn_pattern'    => 'nullable|string|max:255',
            'sn_regex'      => 'nullable|string|max:500',
            'sample_sn'     => 'nullable|string|max:1000',
            'regex_meter'   => 'nullable|string|max:255',
            'regex_idpel'   => 'nullable|string|max:255',
            'regex_nama'    => 'nullable|string|max:255',
            'regex_tarif'   => 'nullable|string|max:255',
            'regex_token'   => 'nullable|string|max:255',
            'regex_kwh'     => 'nullable|string|max:255',
            'regex_stand'   => 'nullable|string|max:255',
            'regex_blth'    => 'nullable|string|max:255',
            'regex_tagihan' => 'nullable|string|max:255',
            'regex_reff'    => 'nullable|string|max:255',
            'regex_norek'   => 'nullable|string|max:255',
        ]);

        $provider = trim($request->input('provider', ''));
        $keywords = trim($request->input('keywords', ''));
        $name     = trim($request->input('name', ''));

        if (empty($provider) && empty($keywords) && empty($name)) {
            return back()->withErrors(['target' => 'Mohon isi minimal salah satu: Provider / Operator atau Kode Produk OtomaX.'])->withInput();
        }

        if (empty($name)) {
            if (!empty($provider)) {
                $name = 'Provider ' . strtoupper($provider);
            } elseif (!empty($keywords)) {
                $firstKw = trim(explode(',', $keywords)[0]);
                $name = 'Produk ' . strtoupper($firstKw);
            } else {
                $name = 'Aturan ' . strtoupper($request->input('category'));
            }
        }

        $receiptService = app(\App\Services\ReceiptService::class);
        $rules = $receiptService->getRules();

        $ruleId = $request->input('rule_id') ?: ('rule_' . Str::slug($name) . '_' . uniqid());
        $logoUrl = $request->input('existing_logo_url', '/images/receipt/pln.svg');

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'svg', 'webp'], true)) {
                return back()->withErrors(['logo_file' => 'Ekstensi file logo tidak diizinkan (hanya PNG, JPG, JPEG, WEBP, SVG).'])->withInput();
            }
            if ($ext === 'svg' && !$this->isSafeSvgContent((string)file_get_contents($file->getRealPath()))) {
                return back()->withErrors(['logo_file' => 'File SVG mengandung skrip aktif berbahaya.'])->withInput();
            }
            $fileName = 'logo_' . Str::slug($name) . '_' . time() . '.' . $ext;
            $file->move(public_path('images/receipt'), $fileName);
            $logoUrl = '/images/receipt/' . $fileName;
        }

        $newRule = [
            'id'            => $ruleId,
            'name'          => $name,
            'category'      => $request->input('category'),
            'provider'      => $provider,
            'keywords'      => $keywords,
            'bank_name'     => trim($request->input('bank_name', '')),
            'logo_url'      => $logoUrl,
            'default_admin' => (float)($request->input('default_admin', 2500)),
            'remark'        => trim($request->input('remark', 'Transfer Uang')),
            'footer_info'   => trim($request->input('footer_info', '')),
            'parser_mode'   => $request->input('parser_mode', 'auto'),
            'sn_template'   => trim($request->input('sn_template', '')),
            'sn_delimiter'  => $request->input('sn_delimiter', '/'),
            'sn_pattern'    => trim($request->input('sn_pattern', '')),
            'sn_regex'      => trim($request->input('sn_regex', '')),
            'sample_sn'     => trim($request->input('sample_sn', '')),
            'regex_meter'   => trim($request->input('regex_meter', '')),
            'regex_idpel'   => trim($request->input('regex_idpel', '')),
            'regex_nama'    => trim($request->input('regex_nama', '')),
            'regex_tarif'   => trim($request->input('regex_tarif', '')),
            'regex_token'   => trim($request->input('regex_token', '')),
            'regex_kwh'     => trim($request->input('regex_kwh', '')),
            'regex_stand'   => trim($request->input('regex_stand', '')),
            'regex_blth'    => trim($request->input('regex_blth', '')),
            'regex_tagihan' => trim($request->input('regex_tagihan', '')),
            'regex_reff'    => trim($request->input('regex_reff', '')),
            'regex_norek'   => trim($request->input('regex_norek', '')),
            'is_default'    => false,
        ];

        // Replace if exists, else append
        $found = false;
        foreach ($rules as $idx => $r) {
            if (($r['id'] ?? '') === $ruleId) {
                $rules[$idx] = array_merge($r, $newRule);
                $found = true;
                break;
            }
        }
        if (!$found) {
            array_unshift($rules, $newRule);
        }

        $receiptService->saveRules($rules);

        return back()->with('receipt_success', "Aturan logo & struk '{$newRule['name']}' berhasil disimpan.");
    }

    public function deleteReceiptRule(Request $request, $id)
    {
        if ($r = $this->guardAdmin()) return $r;

        $receiptService = app(\App\Services\ReceiptService::class);
        $rules = $receiptService->getRules();
        $rules = array_values(array_filter($rules, fn($r) => ($r['id'] ?? '') !== $id));
        $receiptService->saveRules($rules);

        return back()->with('receipt_success', 'Aturan struk berhasil dihapus.');
    }

    public function resetReceiptRules(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $receiptService = app(\App\Services\ReceiptService::class);
        $receiptService->resetRules();

        return back()->with('receipt_success', 'Semua aturan & logo struk berhasil di-reset ke bawaan pabrik.');
    }

    public function updateAccountSettings(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $request->validate([
            'current_password'      => 'required|string',
            'new_username'          => 'required|string|min:3|max:50',
            'new_password'          => 'nullable|string|min:6|max:100|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi untuk verifikasi keamanan.',
            'new_username.required'     => 'Username baru wajib diisi.',
            'new_username.min'          => 'Username minimal 3 karakter.',
            'new_password.min'          => 'Password baru minimal 6 karakter.',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        $credentials = $this->getAdminCredentials();
        $currentPassword = (string)$request->input('current_password');

        // Verifikasi password saat ini
        $isValidCurrent = false;
        if (str_starts_with($credentials['password_hash'], '$2y$') || str_starts_with($credentials['password_hash'], '$argon2')) {
            $isValidCurrent = Hash::check($currentPassword, $credentials['password_hash']);
        } else {
            $isValidCurrent = hash_equals($credentials['password_hash'], $currentPassword);
        }

        if (!$isValidCurrent) {
            return back()->with('account_error', 'Password saat ini salah! Perubahan kredensial dibatalkan.');
        }

        $newUsername = strip_tags(trim($request->input('new_username')));
        $newPassword = $request->input('new_password');

        $newHash = !empty($newPassword)
            ? Hash::make($newPassword)
            : $credentials['password_hash'];

        // 1. Simpan di Cache persistent agar selalu aktif
        Cache::forever('admin_account_username', $newUsername);
        Cache::forever('admin_account_password_hash', $newHash);

        // 2. Simpan / Sinkronkan ke tabel users jika tersedia
        try {
            $user = \App\Models\User::first();
            if (!$user) {
                $user = new \App\Models\User();
                $user->email = str_contains($newUsername, '@') ? $newUsername : $newUsername . '@admin.local';
            }
            $user->name = $newUsername;
            if (!empty($newPassword)) {
                $user->password = $newHash;
            }
            $user->save();
        } catch (\Throwable $e) {
            Log::warning("Could not sync admin to users table: " . $e->getMessage());
        }

        // 3. Update sesi admin saat ini
        Session::put('admin_username', $newUsername);

        Log::info("AUDIT: Admin credentials updated for [{$newUsername}] from IP: {$request->ip()}");

        return back()->with('account_success', 'Kredensial administrator (username/password) berhasil diperbarui dan disimpan secara terenkripsi.');
    }

    public function updateMailSettings(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $request->validate([
            'gmail_address'  => 'required|email',
            'gmail_password' => 'required|string|min:8',
            'from_name'      => 'required|string|max:100',
        ], [
            'gmail_address.required'  => 'Alamat Gmail wajib diisi.',
            'gmail_address.email'     => 'Alamat Gmail tidak valid.',
            'gmail_password.required' => 'App Password Gmail wajib diisi.',
            'gmail_password.min'      => 'App Password Gmail minimal 8 karakter.',
            'from_name.required'      => 'Nama pengirim wajib diisi.',
        ]);

        // Store in cache (persistent) — write to .env would require file write permissions
        Cache::forever('mail_from_address', $request->input('gmail_address'));
        Cache::forever('mail_from_name', $request->input('from_name'));
        Cache::forever('mail_username', $request->input('gmail_address'));
        Cache::forever('mail_password', \Illuminate\Support\Facades\Crypt::encryptString($request->input('gmail_password')));

        return back()->with('mail_success', 'Konfigurasi Gmail berhasil disimpan.');
    }

    public function testMailSettings(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $request->validate([
            'test_email' => 'required|email',
        ]);

        if (!MailService::isConfigured()) {
            return response()->json(['status' => 'error', 'message' => 'Konfigurasi Gmail belum disimpan. Isi form dan klik Simpan terlebih dahulu.']);
        }

        $toEmail = $request->input('test_email');
        $sent = MailService::send(
            $toEmail,
            'Uji Coba Email - Portal Reseller',
            "Halo,\n\nIni adalah email uji coba dari Portal Reseller Anda.\nJika Anda menerima email ini, konfigurasi Gmail SMTP berhasil!\n\nSalam,\n" . Cache::get('mail_from_name', 'Admin Portal')
        );

        if ($sent) {
            return response()->json(['status' => 'success', 'message' => "Email uji coba berhasil dikirim ke {$toEmail}. Silakan cek Inbox atau folder Spam."]);
        }
        return response()->json(['status' => 'error', 'message' => 'Gagal mengirim email. Cek laravel.log untuk detail error.']);
    }

    public function updateBankSettings(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $banks = $request->input('banks', []);
        
        // Clean array
        $validBanks = [];
        foreach ($banks as $bank) {
            if (!empty($bank['bank']) && !empty($bank['rekening']) && !empty($bank['atas_nama'])) {
                $validBanks[] = [
                    'bank' => trim($bank['bank']),
                    'rekening' => trim($bank['rekening']),
                    'atas_nama' => trim($bank['atas_nama'])
                ];
            }
        }

        Cache::forever('deposit_banks', $validBanks);

        return back()->with('bank_success', 'Daftar rekening bank berhasil diperbarui.');
    }

    public function updateSecuritySettings(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $enabled = $request->input('recaptcha_enabled') === '1';
        Cache::forever('recaptcha_enabled', $enabled);
        \App\Models\Setting::set('recaptcha_enabled', $enabled ? '1' : '0');
        
        $siteKey = trim($request->input('recaptcha_site_key', ''));
        if (!empty($siteKey)) {
            Cache::forever('recaptcha_site_key', $siteKey);
            \App\Models\Setting::set('recaptcha_site_key', $siteKey);
        } else {
            Cache::forget('recaptcha_site_key');
            \App\Models\Setting::set('recaptcha_site_key', '');
        }

        $secretInput = trim($request->input('recaptcha_secret_key', ''));
        if (!empty($secretInput)) {
            // Decode if user re-submitted already encrypted string
            while (str_starts_with($secretInput, 'eyJ')) {
                try {
                    $secretInput = \Illuminate\Support\Facades\Crypt::decryptString($secretInput);
                } catch (\Throwable $e) {
                    break;
                }
            }
            Cache::forever('recaptcha_secret_key', \Illuminate\Support\Facades\Crypt::encryptString($secretInput));
            \App\Models\Setting::set('recaptcha_secret_key', $secretInput, true);
        } else {
            Cache::forget('recaptcha_secret_key');
            \App\Models\Setting::set('recaptcha_secret_key', '');
        }

        return back()->with('security_success', 'Pengaturan Keamanan (reCAPTCHA) berhasil diperbarui.');
    }

    public function updateApiCenterSettings(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $request->validate([
            'api_endpoint'  => 'required|string|max:255',
            'regex_success' => 'required|string|max:500',
            'regex_failed'  => 'required|string|max:500',
            'regex_pending' => 'required|string|max:500',
        ], [
            'api_endpoint.required'  => 'IP Center / Endpoint URL wajib diisi.',
            'regex_success.required' => 'Regex Sukses wajib diisi.',
            'regex_failed.required'  => 'Regex Gagal wajib diisi.',
            'regex_pending.required' => 'Regex Menunggu/Proses wajib diisi.',
        ]);

        Cache::forever('api_endpoint', trim($request->input('api_endpoint')));
        Cache::forever('api_regex_success', trim($request->input('regex_success')));
        Cache::forever('api_regex_failed', trim($request->input('regex_failed')));
        Cache::forever('api_regex_pending', trim($request->input('regex_pending')));

        \Illuminate\Support\Facades\Log::info("AUDIT: Admin updated IP Center and Regex API settings from IP: {$request->ip()}");

        return back()->with('apicenter_success', 'Pengaturan IP Center & Regex Balasan berhasil diperbarui.');
    }

    public function updateOtomaxSettings(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        \App\Models\Setting::set('otomax_db_host', trim($request->input('db_host')), true);
        \App\Models\Setting::set('otomax_db_port', trim($request->input('db_port')), false);
        \App\Models\Setting::set('otomax_db_database', trim($request->input('db_database')), true);
        \App\Models\Setting::set('otomax_db_username', trim($request->input('db_username')), true);

        // Hanya update password jika diisi pengguna (agar password terenkripsi tidak tertimpa kosong)
        if ($request->filled('db_password')) {
            \App\Models\Setting::set('otomax_db_password', $request->input('db_password'), true);
        }

        // Terapkan langsung ke runtime konfigurasi
        config([
            'database.connections.otomax.host' => \App\Models\Setting::get('otomax_db_host'),
            'database.connections.otomax.port' => (int)\App\Models\Setting::get('otomax_db_port'),
            'database.connections.otomax.database' => \App\Models\Setting::get('otomax_db_database'),
            'database.connections.otomax.username' => \App\Models\Setting::get('otomax_db_username'),
            'database.connections.otomax.password' => \App\Models\Setting::get('otomax_db_password', ''),
        ]);

        \Illuminate\Support\Facades\DB::purge('otomax');

        \Illuminate\Support\Facades\Log::info("AUDIT: Admin updated SQL Server OtomaX credentials encrypted in SQLite database from IP: {$request->ip()}");

        return back()->with('otomax_success', 'Konfigurasi SQL Server OtomaX berhasil disimpan terenkripsi di database.');
    }

    public function testOtomaxConnection(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $hasDriver = extension_loaded('pdo_sqlsrv') || extension_loaded('sqlsrv');
        if (!$hasDriver) {
            return response()->json([
                'status' => 'error',
                'message' => 'Driver SQL Server untuk PHP (pdo_sqlsrv / sqlsrv) belum terpasang atau belum aktif di php.ini server ini. Silakan aktifkan extension=pdo_sqlsrv dan extension=sqlsrv terlebih dahulu.'
            ]);
        }

        $host = $request->input('db_host', \App\Models\Setting::get('otomax_db_host', '127.0.0.1'));
        $port = (int)($request->input('db_port', \App\Models\Setting::get('otomax_db_port', 1433)));
        $database = $request->input('db_database', \App\Models\Setting::get('otomax_db_database', 'otomax'));
        $username = $request->input('db_username', \App\Models\Setting::get('otomax_db_username', 'sa'));
        $password = $request->input('db_password');
        if ($password === null || $password === '') {
            $password = \App\Models\Setting::get('otomax_db_password', '');
        }

        try {
            // Konfigurasi koneksi temporer untuk pengujian dinamis
            config([
                'database.connections.otomax_tester' => [
                    'driver'                   => 'sqlsrv',
                    'host'                     => $host,
                    'port'                     => $port,
                    'database'                 => $database,
                    'username'                 => $username,
                    'password'                 => $password,
                    'charset'                  => 'utf8',
                    'prefix'                   => '',
                    'prefix_indexes'           => true,
                    'encrypt'                  => 'yes',
                    'trust_server_certificate' => 'true',
                ]
            ]);

            \Illuminate\Support\Facades\DB::purge('otomax_tester');
            $db = \Illuminate\Support\Facades\DB::connection('otomax_tester');
            
            // Trigger handshake/koneksi
            $db->getPdo();

            // Cek versi SQL Server
            $verQuery = $db->select("SELECT @@VERSION as ver");
            $verText = $verQuery[0]->ver ?? 'Microsoft SQL Server';
            $firstLineVer = explode("\n", trim($verText))[0];

            // Cek keberadaan tabel inti OtomaX & Hak Akses Baca (SELECT Permission)
            $tables = ['reseller', 'produk', 'transaksi'];
            $foundTables = [];
            $deniedTables = [];
            foreach ($tables as $t) {
                if ($db->getSchemaBuilder()->hasTable($t)) {
                    $foundTables[] = $t;
                    try {
                        $db->select("SELECT TOP 1 * FROM [{$t}]");
                    } catch (\Throwable $pe) {
                        $pMsg = $pe->getMessage();
                        if (str_contains($pMsg, 'permission was denied') || str_contains($pMsg, 'SELECT permission')) {
                            $deniedTables[] = $t;
                        }
                    }
                }
            }

            if (!empty($deniedTables)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Koneksi ke SQL Server BERHASIL, TETAPI user database [{$username}] TIDAK MEMILIKI HAK BACA (SELECT permission denied) pada tabel: [" . implode(', ', $deniedTables) . "]. Inilah penyebab admin panel masih menampilkan data dummy (simulasi). Solusi: Di SQL Server Management Studio (SSMS), buka database [{$database}] -> Security -> Users -> klik user [{$username}] -> beri role 'db_owner' atau 'db_datareader'."
                ]);
            }

            $tableMsg = !empty($foundTables) 
                ? "Tabel OtomaX terdeteksi dan dapat dibaca: [" . implode(', ', $foundTables) . "]."
                : "Tabel standar OtomaX belum terdeteksi di database ini.";

            return response()->json([
                'status'  => 'success',
                'message' => "Koneksi ke SQL Server BERHASIL! Terhubung ke database [{$database}] di {$host}:{$port}. ({$firstLineVer}). {$tableMsg}"
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal terhubung ke SQL Server: ' . $e->getMessage()
            ]);
        }
    }
}
