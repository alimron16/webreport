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
        return view('admin.login');
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
        return null;
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
        $kode_reseller = trim($request->input('kode_reseller', ''));
        $profit_filter = $request->input('profit_filter', 'all');
        $search = trim($request->input('search', ''));

        $resellersRes = $this->otomaXService->call('GetAllResellers');
        $allResellers = ($resellersRes['ok'] ?? false) ? ($resellersRes['result'] ?? []) : [];

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
        $kode_reseller = trim($request->input('kode_reseller', ''));
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
                fputcsv($handle, [
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
                ]);
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
        $fileName = 'pks_agreement_' . $type . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('pks', $fileName, 'local');

        \Illuminate\Support\Facades\Cache::forever('pks_template_path_' . $type, $path);
        \Illuminate\Support\Facades\Cache::forever('pks_template_name_' . $type, $file->getClientOriginalName());

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
            $fileName = 'portal_logo.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('branding', $fileName, 'local');
            
            \Illuminate\Support\Facades\Cache::forever('site_logo_path', $path);
            \Illuminate\Support\Facades\Cache::forever('site_logo_url', '/logo?t=' . time());
        }

        return back()->with('branding_success', 'Pengaturan branding, kontak WhatsApp & portal berhasil diperbarui.');
    }

    public function settingsView()
    {
        if ($r = $this->guardAdmin()) return $r;



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

        return view('admin.settings', compact('mailConfig', 'logs', 'levels', 'currentAdminUsername', 'adminCredSource', 'otomaxDbConfig', 'recaptchaSecret'));
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
        
        if ($request->filled('recaptcha_site_key')) {
            Cache::forever('recaptcha_site_key', trim($request->recaptcha_site_key));
        } else {
            Cache::forget('recaptcha_site_key');
        }

        if ($request->filled('recaptcha_secret_key')) {
            $secretInput = trim($request->recaptcha_secret_key);
            // Decode if user re-submitted already encrypted string
            while (str_starts_with($secretInput, 'eyJ')) {
                try {
                    $secretInput = \Illuminate\Support\Facades\Crypt::decryptString($secretInput);
                } catch (\Throwable $e) {
                    break;
                }
            }
            Cache::forever('recaptcha_secret_key', \Illuminate\Support\Facades\Crypt::encryptString($secretInput));
        } else {
            Cache::forget('recaptcha_secret_key');
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

        \App\Models\Setting::set('otomax_db_host', trim($request->input('db_host')), false);
        \App\Models\Setting::set('otomax_db_port', trim($request->input('db_port')), false);
        \App\Models\Setting::set('otomax_db_database', trim($request->input('db_database')), false);
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

            // Cek keberadaan tabel inti OtomaX
            $tables = ['reseller', 'produk', 'transaksi'];
            $foundTables = [];
            foreach ($tables as $t) {
                if ($db->getSchemaBuilder()->hasTable($t)) {
                    $foundTables[] = $t;
                }
            }

            $tableMsg = !empty($foundTables) 
                ? "Tabel OtomaX terdeteksi: [" . implode(', ', $foundTables) . "]."
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
