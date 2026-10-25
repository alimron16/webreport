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
     * Handle registration form submission
     */
    public function store(Request $request)
    {
        if (!app(\App\Services\LicenseService::class)->isValid()) {
            return redirect()->route('license.show');
        }

        if (!$this->validateRecaptcha($request)) {
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

        // Verify OTP from session
        $sessionOtp = Session::get('register_otp');
        $sessionEmail = Session::get('register_otp_email');

        if (empty($sessionOtp) || $request->input('otp') !== $sessionOtp || $validated['email'] !== $sessionEmail) {
            return back()->withInput()->withErrors(['otp' => 'Kode OTP salah atau alamat email tidak cocok dengan penerima OTP!']);
        }

        // Clear OTP session once successfully verified
        Session::forget('register_otp');
        Session::forget('register_otp_email');

        Registration::create([
            'nama'           => $validated['nama'],
            'nama_pemilik'   => $validated['nama_pemilik'],
            'no_hp'          => $validated['no_hp'],
            'email'          => $validated['email'] ?? null,
            'alamat'         => $validated['alamat'] ?? null,
            'pin'            => $validated['pin'],
            'nomor_ktp'      => $validated['nomor_ktp'] ?? null,
            'npwp'           => $validated['npwp'] ?? null,
            'alamat_ip'      => $validated['alamat_ip'] ?? null,
            'password_ip'    => $validated['password_ip'] ?? null,
            'url_report'     => $validated['url_report'] ?? null,
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
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');
        $otp = (string)rand(100000, 999999);

        // Store OTP details in Session
        Session::put('register_otp', $otp);
        Session::put('register_otp_email', $email);

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
