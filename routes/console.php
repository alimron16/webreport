<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('recaptcha:off', function () {
    \Illuminate\Support\Facades\Cache::forever('recaptcha_enabled', false);
    \App\Models\Setting::set('recaptcha_enabled', '0');
    $this->info('Fitur reCAPTCHA berhasil dinonaktifkan.');
})->purpose('Nonaktifkan verifikasi reCAPTCHA pada form login');

Artisan::command('recaptcha:on', function () {
    \Illuminate\Support\Facades\Cache::forever('recaptcha_enabled', true);
    \App\Models\Setting::set('recaptcha_enabled', '1');
    $this->info('Fitur reCAPTCHA berhasil diaktifkan.');
})->purpose('Aktifkan verifikasi reCAPTCHA pada form login');

Artisan::command('recaptcha:status', function () {
    $cEn = \Illuminate\Support\Facades\Cache::get('recaptcha_enabled');
    $cKey = \Illuminate\Support\Facades\Cache::get('recaptcha_site_key');
    $sEn = \App\Models\Setting::get('recaptcha_enabled');
    $sKey = \App\Models\Setting::get('recaptcha_site_key');
    $this->table(
        ['Key', 'Cache Value', 'Setting (SQLite) Value'],
        [
            ['Enabled', var_export($cEn, true), var_export($sEn, true)],
            ['Site Key', var_export($cKey, true), var_export($sKey, true)],
        ]
    );
})->purpose('Cek status dan kunci reCAPTCHA');

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Schedule::call(function () {
    try {
        $affected = DB::connection('otomax')->table('tiket_deposit')
            ->where('status', 'O')
            ->where('waktu', '<', now()->subHours(24))
            ->update([
                'status' => 'C',
                'tgl_status' => now()
            ]);
            
        if ($affected > 0) {
            Log::info("Auto-cancelled {$affected} expired deposit tickets.");
        }
    } catch (\Exception $e) {
        Log::error("Failed to auto-cancel old tickets: " . $e->getMessage());
    }
})->everyMinute();
