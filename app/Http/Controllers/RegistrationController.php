<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class RegistrationController extends Controller
{
    use \App\Traits\ValidatesRecaptcha;
    /**
     * Show the registration form
     */
    public function show()
    {
        if (!app(\App\Services\LicenseService::class)->isValid()) {
            return redirect()->route('license.show');
        }
        return view('webreport.register');
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

    /**
     * Handle registration form submission
     */
    public function store(Request $request)
    {
        if (!app(\App\Services\LicenseService::class)->isValid()) {
            return redirect()->route('license.show');
        }

        $throttleKey = 'register_submit_' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Terlalu banyak percobaan registrasi. Silakan coba lagi dalam {$seconds} detik.")->withInput();
        }

        if (!$this->validateRecaptcha($request)) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
            return back()->with('error', 'Validasi CAPTCHA gagal. Silakan centang kotak "I\'m not a robot".')->withInput();
        }

        $validated = $request->validate([
            'nama'         => 'required|string|min:3|max:100',
            'nama_pemilik' => 'required|string|min:3|max:100',
            'no_hp'        => 'required|string|min:8|max:20',
            'email'        => 'required|email|max:100',
            'otp'          => 'required|string|size:6',
            'alamat'       => 'nullable|string|max:500',
            'pin'          => 'required|digits_between:4,8',
            'pin_confirm'  => 'required|same:pin',
            'nomor_ktp'    => 'nullable|string|max:50',
            'npwp'         => 'nullable|string|max:50',
            'alamat_ip'    => 'nullable|string|max:100',
            'password_ip'  => 'nullable|string|max:100',
            'url_report'   => 'nullable|url|max:255',
        ], [
            'nama.required'         => 'Nama outlet / reseller wajib diisi.',
            'nama_pemilik.required' => 'Nama pemilik wajib diisi.',
            'no_hp.required'        => 'Nomor HP/WhatsApp wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'otp.required'          => 'Kode OTP wajib diisi.',
            'otp.size'              => 'Kode OTP harus berupa 6 karakter.',
            'pin.required'          => 'PIN wajib diisi.',
            'pin.digits_between'    => 'PIN harus 4–8 digit angka.',
            'pin_confirm.same'      => 'Konfirmasi PIN tidak cocok.',
            'url_report.url'        => 'Format URL Callback tidak valid.',
        ]);

        if (!$this->isSafeCallbackUrl($validated['url_report'] ?? null)) {
            return back()->withInput()->withErrors(['url_report' => 'URL Callback tidak diizinkan mengarah ke jaringan lokal / internal (SSRF Protection).']);
        }

        // Verify OTP from session with expiry & max-attempts protection
        $sessionOtp = (string)Session::get('register_otp', '');
        $sessionEmail = (string)Session::get('register_otp_email', '');
        $otpExpires = (int)Session::get('register_otp_expires', 0);
        $otpAttempts = (int)Session::get('register_otp_attempts', 0);

        if (empty($sessionOtp) || time() > $otpExpires || $otpAttempts >= 5) {
            Session::forget(['register_otp', 'register_otp_email', 'register_otp_expires', 'register_otp_attempts']);
            return back()->withInput()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa atau melebihi batas percobaan. Silakan minta OTP baru!']);
        }

        if (!hash_equals($sessionOtp, (string)$request->input('otp')) || strcasecmp($validated['email'], $sessionEmail) !== 0) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
            Session::put('register_otp_attempts', $otpAttempts + 1);
            return back()->withInput()->withErrors(['otp' => 'Kode OTP salah atau alamat email tidak cocok dengan penerima OTP!']);
        }

        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

        // Clear OTP session once successfully verified
        Session::forget(['register_otp', 'register_otp_email', 'register_otp_expires', 'register_otp_attempts']);

        Registration::create([
            'nama'           => strip_tags(trim($validated['nama'])),
            'nama_pemilik'   => strip_tags(trim($validated['nama_pemilik'])),
            'no_hp'          => strip_tags(trim($validated['no_hp'])),
            'email'          => isset($validated['email']) ? trim($validated['email']) : null,
            'alamat'         => isset($validated['alamat']) ? strip_tags(trim($validated['alamat'])) : null,
            'pin'            => $validated['pin'],
            'nomor_ktp'      => isset($validated['nomor_ktp']) ? strip_tags(trim($validated['nomor_ktp'])) : null,
            'npwp'           => isset($validated['npwp']) ? strip_tags(trim($validated['npwp'])) : null,
            'alamat_ip'      => isset($validated['alamat_ip']) ? strip_tags(trim($validated['alamat_ip'])) : null,
            'password_ip'    => $validated['password_ip'] ?? null,
            'url_report'     => isset($validated['url_report']) ? trim($validated['url_report']) : null,
            'berkas_path'    => null,
            'berkas_nama'    => null,
            'foto_ktp_path'  => null,
            'foto_npwp_path' => null,
            'status'         => 'pending',
        ]);

        return redirect()->route('webreport.login')
            ->with('success', 'Silahkan login dan lengkapi persyaratannya.');
    }

    public function sendOtp(Request $request)
    {
        $throttleKey = 'register_otp_send_' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return response()->json([
                'status'  => 'error',
                'message' => "Terlalu banyak permintaan OTP. Silakan tunggu {$seconds} detik."
            ], 429);
        }

        $request->validate([
            'email' => 'required|email|max:100'
        ]);

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);

        $email = trim((string)$request->input('email'));
        $otp = (string)random_int(100000, 999999);

        // Store OTP details in Session with 10-minute expiration
        Session::put('register_otp', $otp);
        Session::put('register_otp_email', $email);
        Session::put('register_otp_expires', time() + 600);
        Session::put('register_otp_attempts', 0);

        $sent = MailService::sendOtp($email, $otp, 'Registrasi');

        return response()->json([
            'status' => 'success',
            'message' => $sent
                ? 'OTP berhasil dikirim ke email Anda. Silakan cek Inbox atau folder Spam.'
                : 'OTP telah dibuat (mode log). Cek laravel.log karena Gmail belum dikonfigurasi di admin panel.'
        ]);
    }

    public function downloadTemplate(Request $request)
    {
        $type = $request->query('type', 'perorangan');
        if (!in_array($type, ['perorangan', 'badan_usaha'])) {
            $type = 'perorangan';
        }

        $path = \Illuminate\Support\Facades\Cache::get('pks_template_path_' . $type);
        $name = \Illuminate\Support\Facades\Cache::get('pks_template_name_' . $type);

        // Fallback to legacy single PKS key if type-specific cache is empty
        if (!$path) {
            $path = \Illuminate\Support\Facades\Cache::get('pks_template_path');
            $name = \Illuminate\Support\Facades\Cache::get('pks_template_name');
        }

        if ($path && Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path, $name);
        }

        // Fallback if Admin has not uploaded any customized PKS template yet
        $html = view('webreport.template-pendaftaran')->render();
        return response($html, 200, [
            'Content-Type'        => 'text/html',
            'Content-Disposition' => 'attachment; filename="formulir-pendaftaran-otomax.html"',
        ]);
    }
}
