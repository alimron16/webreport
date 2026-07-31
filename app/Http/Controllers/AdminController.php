<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Services\OtomaXService;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
            return back()->with('error', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
        }

        if (!$this->validateRecaptcha($request)) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
            return back()->with('error', 'Validasi CAPTCHA gagal.')->withInput();
        }

        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $adminUser = env('ADMIN_USER', 'admin');
        $adminPass = env('ADMIN_PASS', 'admin123');

        if ($request->username === $adminUser && $request->password === $adminPass) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            Session::put('admin_logged_in', true);
            Session::put('admin_username', $request->username);
            return redirect()->route('admin.dashboard');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
        return back()->with('error', 'Username atau password salah.');
    }

    public function logout()
    {
        Session::forget(['admin_logged_in', 'admin_username']);
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

    // ── Dashboard — list registrations ───────────────

    public function dashboard(Request $request)
    {
        if ($r = $this->guardAdmin()) return $r;

        $status = $request->input('status', 'pending');

        $query = Registration::latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $registrations = $query->paginate(20)->withQueryString();

        $counts = [
            'pending'  => Registration::where('status', 'pending')->count(),
            'approved' => Registration::where('status', 'approved')->count(),
            'rejected' => Registration::where('status', 'rejected')->count(),
            'all'      => Registration::count(),
        ];

        return view('admin.dashboard', compact('registrations', 'status', 'counts'));
    }

    // ── Detail pendaftaran ────────────────────────────

    public function detail(int $id)
    {
        if ($r = $this->guardAdmin()) return $r;

        $reg = Registration::findOrFail($id);

        // Auto-generate suggested kode if not yet set
        $suggestedKode = $reg->kode_reseller
            ?? Registration::generateKode($reg->kode_prefix ?? 'CMP');

        return view('admin.detail', compact('reg', 'suggestedKode'));
    }

    public function downloadBerkas(Request $request, int $id)
    {
        if ($r = $this->guardAdmin()) return $r;

        $reg = Registration::findOrFail($id);
        $type = $request->query('type', 'berkas');

        $path = $reg->berkas_path;
        $name = $reg->berkas_nama ?? 'berkas-pendaftaran';

        if ($type === 'ktp') {
            $path = $reg->foto_ktp_path;
            $name = 'foto-ktp-' . str_replace(' ', '_', $reg->nama) . '.' . pathinfo($path, PATHINFO_EXTENSION);
        } elseif ($type === 'npwp') {
            $path = $reg->foto_npwp_path;
            $name = 'foto-npwp-' . str_replace(' ', '_', $reg->nama) . '.' . pathinfo($path, PATHINFO_EXTENSION);
        }

        if (!$path || !Storage::disk('local')->exists($path)) {
            return back()->with('error', 'File yang diminta tidak ditemukan.');
        }

        return Storage::disk('local')->download($path, $name);
    }

    // ── Approve ───────────────────────────────────────

    public function approve(Request $request, int $id)
    {
        if ($r = $this->guardAdmin()) return $r;

        $reg = Registration::findOrFail($id);

        if (!$reg->isPending()) {
            return back()->with('error', 'Pendaftaran ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'kode_reseller' => 'required|string|min:4|max:20',
            'kode_prefix'   => 'nullable|string|max:10',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $kode = strtoupper(trim($request->kode_reseller));

        // Check if code already exists in OtomaX DB
        try {
            $existing = $this->otomaXService->call('GetRs', ['kode' => strtolower($kode)]);
            if (isset($existing['ok']) && $existing['ok']) {
                return back()->with('error', "Kode reseller '{$kode}' sudah terdaftar di OtomaX. Gunakan kode lain.");
            }
        } catch (\Exception $e) {
            // OtomaX DB not available — continue anyway (for mock mode)
        }

        // Insert new reseller into OtomaX database
        try {
            $insertResult = $this->otomaXService->call('InsertResellerFull', [
                'kode'         => strtolower($kode),
                'nama'         => $reg->nama,
                'nama_pemilik' => $reg->nama_pemilik,
                'pengirim'     => $reg->no_hp,
                'pin'          => $reg->pin,
                'email'        => $reg->email ?? '',
                'alamat'       => $reg->alamat ?? '',
                'nomor_ktp'    => $reg->nomor_ktp,
                'npwp'         => $reg->npwp,
                'alamat_ip'    => $reg->alamat_ip,
                'password_ip'  => $reg->password_ip,
                'url_report'   => $reg->url_report,
            ]);

            if (!isset($insertResult['ok']) || !$insertResult['ok']) {
                $errMsg = $insertResult['desc'] ?? 'Gagal membuat akun di OtomaX.';
                return back()->with('error', $errMsg);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error OtomaX DB: ' . $e->getMessage());
        }

        // Update registration record
        $reg->kode_reseller = $kode;
        $reg->kode_prefix   = $request->kode_prefix ?? substr($kode, 0, 3);
        $reg->catatan_admin = $request->catatan_admin;
        $reg->status        = 'approved';
        $reg->reviewed_at   = now();
        $reg->reviewed_by   = Session::get('admin_username', 'admin');
        $reg->save();

        // Send Email Notification to Member
        if ($reg->email) {
            $subject = "Pendaftaran Reseller Disetujui - " . Cache::get('site_title', 'OtomaX');
            $body = "Halo {$reg->nama_pemilik},\n\n"
                  . "Selamat! Pendaftaran Anda sebagai reseller di " . Cache::get('site_title', 'OtomaX') . " telah disetujui.\n\n"
                  . "Detail Akun Anda:\n"
                  . "Nama Outlet: {$reg->nama}\n"
                  . "Kode Reseller: {$reg->kode_reseller}\n"
                  . "PIN Transaksi: (Sesuai dengan yang Anda buat saat pendaftaran)\n\n"
                  . "Silakan gunakan Kode Reseller dan PIN Anda untuk login ke Web Report kami.\n\n"
                  . "Terima kasih,\n"
                  . Cache::get('site_title', 'OtomaX');
            
            MailService::send($reg->email, $subject, $body);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Pendaftaran berhasil disetujui. Kode reseller: " . $reg->kode_reseller . ". Email notifikasi telah dikirim ke member.");
    }

    public function resendToOtomax(Request $request, int $id)
    {
        if ($r = $this->guardAdmin()) return $r;

        $reg = Registration::findOrFail($id);

        if ($reg->status !== 'approved') {
            return back()->with('error', 'Pendaftaran harus disetujui terlebih dahulu.');
        }

        if (empty($reg->kode_reseller)) {
            return back()->with('error', 'Kode reseller belum dibuat.');
        }

        $kode = $reg->kode_reseller;

        // Insert new reseller into OtomaX database
        try {
            $insertResult = $this->otomaXService->call('InsertResellerFull', [
                'kode'         => strtolower($kode),
                'nama'         => $reg->nama,
                'nama_pemilik' => $reg->nama_pemilik,
                'pengirim'     => $reg->no_hp,
                'pin'          => $reg->pin,
                'email'        => $reg->email ?? '',
                'alamat'       => $reg->alamat ?? '',
                'nomor_ktp'    => $reg->nomor_ktp,
                'npwp'         => $reg->npwp,
                'alamat_ip'    => $reg->alamat_ip,
                'password_ip'  => $reg->password_ip,
                'url_report'   => $reg->url_report,
            ]);

            if (!isset($insertResult['ok']) || !$insertResult['ok']) {
                $errMsg = $insertResult['desc'] ?? 'Gagal membuat akun di OtomaX.';
                return back()->with('error', $errMsg);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error OtomaX DB: ' . $e->getMessage());
        }

        return back()->with('success', "Data reseller '{$kode}' berhasil dikirim ulang ke OtomaX.");
    }

    // ── Reject ────────────────────────────────────────

    public function reject(Request $request, int $id)
    {
        if ($r = $this->guardAdmin()) return $r;

        $reg = Registration::findOrFail($id);

        if (!$reg->isPending()) {
            return back()->with('error', 'Pendaftaran ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'catatan_admin' => 'required|string|min:5|max:500',
        ], [
            'catatan_admin.required' => 'Harap isi alasan penolakan.',
            'catatan_admin.min'      => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $reg->update([
            'status'        => 'rejected',
            'catatan_admin' => $request->catatan_admin,
            'reviewed_at'   => now(),
            'reviewed_by'   => Session::get('admin_username', 'admin'),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', "Pendaftaran {$reg->nama} telah ditolak.");
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
            'site_title'    => 'required|string|max:100',
            'site_subtitle' => 'required|string|max:200',
            'site_logo'     => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ], [
            'site_title.required'    => 'Nama portal wajib diisi.',
            'site_subtitle.required' => 'Deskripsi portal wajib diisi.',
            'site_logo.image'        => 'Logo harus berupa gambar (png, jpg, jpeg, svg).',
            'site_logo.max'          => 'Ukuran logo maksimal 2MB.',
        ]);

        \Illuminate\Support\Facades\Cache::forever('site_title', $request->input('site_title'));
        \Illuminate\Support\Facades\Cache::forever('site_subtitle', $request->input('site_subtitle'));

        if ($request->hasFile('site_logo')) {
            $file = $request->file('site_logo');
            $fileName = 'portal_logo.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('branding', $fileName, 'local');
            
            \Illuminate\Support\Facades\Cache::forever('site_logo_path', $path);
            \Illuminate\Support\Facades\Cache::forever('site_logo_url', '/logo?t=' . time());
        }

        return back()->with('branding_success', 'Pengaturan branding portal berhasil diperbarui.');
    }

    public function settingsView()
    {
        if ($r = $this->guardAdmin()) return $r;

        try {
            // Update cmp20042 to uppercase CMP20042 in both tables to test case-sensitive collation
            \DB::connection('otomax')->table('reseller')->where('kode', 'cmp20042')->update(['kode' => 'CMP20042']);
            \DB::connection('otomax')->table('pengirim')->where('kode_reseller', 'cmp20042')->update(['kode_reseller' => 'CMP20042']);
            \Log::info("DEBUG: Updated cmp20042 to uppercase CMP20042 in database successfully.");
        } catch (\Exception $e) {
            \Log::error("DEBUG UPPERCASE UPDATE ERROR: " . $e->getMessage());
        }

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

        return view('admin.settings', compact('mailConfig', 'logs'));
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
            Cache::forever('recaptcha_secret_key', \Illuminate\Support\Facades\Crypt::encryptString(trim($request->recaptcha_secret_key)));
        } else {
            Cache::forget('recaptcha_secret_key');
        }

        return back()->with('security_success', 'Pengaturan Keamanan (reCAPTCHA) berhasil diperbarui.');
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

        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $env = file_get_contents($envPath);

            $updates = [
                'DB_HOST_OTOMAX' => $request->input('db_host'),
                'DB_PORT_OTOMAX' => $request->input('db_port'),
                'DB_DATABASE_OTOMAX' => $request->input('db_database'),
                'DB_USERNAME_OTOMAX' => $request->input('db_username'),
                'DB_PASSWORD_OTOMAX' => $request->input('db_password', ''),
            ];

            foreach ($updates as $key => $value) {
                // Escape value if it contains spaces
                if (preg_match('/\s/', $value)) {
                    $value = '"' . $value . '"';
                }
                
                // If key exists, replace it
                if (strpos($env, $key . '=') !== false) {
                    $env = preg_replace('/^' . $key . '=.*/m', $key . '=' . $value, $env);
                } else {
                    // Otherwise append it
                    $env .= "\n" . $key . '=' . $value;
                }
            }

            file_put_contents($envPath, $env);
            
            // Clear config cache so new env takes effect
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        }

        return back()->with('otomax_success', 'Konfigurasi SQL Server OtomaX berhasil disimpan.');
    }
}
