<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;

class LicenseController extends Controller
{
    public function show(LicenseService $license)
    {
        return view('license', [
            'info'    => $license->info(),
            'isAdmin' => Session::has('admin_logged_in'),
        ]);
    }

    public function activate(Request $request, LicenseService $license)
    {
        $throttleKey = 'license_activate_' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
        }

        $request->validate([
            'license_key' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9\-_]+$/'],
        ], [
            'license_key.required' => 'License key wajib diisi.',
            'license_key.regex'    => 'Format license key tidak valid.',
        ]);

        // Jika sudah berlisensi, hanya admin yang boleh mengganti key.
        if ($license->isValid() && !Session::has('admin_logged_in')) {
            return back()->with('error', 'Lisensi sudah aktif. Login sebagai admin untuk mengganti license key.');
        }

        RateLimiter::hit($throttleKey, 300);

        $result = $license->activate((string) $request->input('license_key'));

        if (!$result['valid']) {
            return back()->with('error', $result['message'])->withInput();
        }

        RateLimiter::clear($throttleKey);
        return redirect()->route('license.show')->with('success', $result['message']);
    }
}
