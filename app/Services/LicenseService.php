<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien lisensi: memverifikasi license key ke server lisensi penjual.
 *
 * Alur:
 *  - Key disimpan terenkripsi di tabel settings (license_key).
 *  - Hasil verifikasi di-cache supaya tidak memanggil server di setiap request.
 *  - Jika server lisensi tidak terjangkau, aplikasi tetap jalan selama
 *    masa toleransi (GRACE_SECONDS) sejak verifikasi sukses terakhir.
 */
class LicenseService
{
    /** Endpoint server lisensi. */
    private const API_URL = 'http://103.150.197.121:4000/api/license/verify';

    /** Nama produk yang dikunci di server lisensi. UBAH sesuai nama produk di server. */
    private const PRODUCT = 'WEBREPORT';

    /** Interval verifikasi ulang ke server saat lisensi valid (6 jam). */
    private const RECHECK_SECONDS = 21600;

    /** Toleransi offline sejak verifikasi sukses terakhir (72 jam). */
    private const GRACE_SECONDS = 259200;

    /** Lama cache saat lisensi ditolak (mencegah memanggil server di tiap request). */
    private const FAIL_CACHE_SECONDS = 60;

    private const CACHE_KEY = 'license_state';

    /**
     * HWID perangkat. Sama dengan contoh integrasi di server lisensi (hostname).
     */
    public function hwid(): string
    {
        return (string) gethostname();
    }

    public function getKey(): string
    {
        $key = (string) Setting::get('license_key', '');
        if ($key === '') {
            $key = (string) env('LICENSE_KEY', '');
        }
        return trim($key);
    }

    /**
     * Cek cepat dipakai middleware (memakai cache, verifikasi ulang bila kadaluarsa).
     */
    public function isValid(): bool
    {
        $key = $this->getKey();
        if ($key === '') {
            return false;
        }

        $state = Cache::get(self::CACHE_KEY);
        if (is_array($state)
            && ($state['key_hash'] ?? '') === md5($key)
            && ($state['expires_at'] ?? 0) > time()) {
            return (bool) $state['valid'];
        }

        return $this->refresh()['valid'];
    }

    /**
     * Verifikasi ke server lisensi dan perbarui state.
     *
     * @return array{valid: bool, message: string, expiry: ?string}
     */
    public function refresh(): array
    {
        $key = $this->getKey();
        if ($key === '') {
            return $this->storeState('', false, 'License key belum diisi.', null, self::FAIL_CACHE_SECONDS);
        }

        $remote = $this->verifyRemote($key);

        if ($remote['ok']) {
            Setting::set('license_last_ok', (string) time());
            Setting::set('license_expiry', (string) ($remote['expiry'] ?? ''));
            return $this->storeState($key, true, 'Lisensi aktif.', $remote['expiry'], self::RECHECK_SECONDS);
        }

        if ($remote['network_error']) {
            $lastOk = (int) Setting::get('license_last_ok', 0);
            $withinGrace = $lastOk > 0 && (time() - $lastOk) < self::GRACE_SECONDS;
            if ($withinGrace) {
                // Server tidak terjangkau tapi masih dalam masa toleransi.
                return $this->storeState(
                    $key,
                    true,
                    'Server lisensi tidak terjangkau, memakai masa toleransi offline.',
                    (string) Setting::get('license_expiry', ''),
                    600
                );
            }
            return $this->storeState(
                $key,
                false,
                'Server lisensi tidak dapat dihubungi dan masa toleransi offline habis.',
                null,
                self::FAIL_CACHE_SECONDS
            );
        }

        // Ditolak server (key salah, kedaluwarsa, HWID tidak cocok, dst).
        Setting::set('license_last_ok', '0');
        return $this->storeState($key, false, $remote['message'], null, self::FAIL_CACHE_SECONDS);
    }

    /**
     * Aktivasi key baru. Key disimpan hanya jika valid.
     *
     * @return array{valid: bool, message: string, expiry: ?string}
     */
    public function activate(string $key): array
    {
        $key = trim($key);
        $remote = $this->verifyRemote($key);

        if (!$remote['ok']) {
            Log::warning('LICENSE: aktivasi ditolak - ' . $remote['message']);
            return ['valid' => false, 'message' => $remote['message'], 'expiry' => null];
        }

        Setting::set('license_key', $key, true);
        Setting::set('license_last_ok', (string) time());
        Setting::set('license_expiry', (string) ($remote['expiry'] ?? ''));
        Log::info('LICENSE: aktivasi berhasil, berlaku hingga ' . ($remote['expiry'] ?? '-'));

        return $this->storeState($key, true, 'Lisensi berhasil diaktifkan.', $remote['expiry'], self::RECHECK_SECONDS);
    }

    /**
     * Informasi untuk halaman lisensi.
     */
    public function info(): array
    {
        $key = $this->getKey();
        $state = Cache::get(self::CACHE_KEY);

        return [
            'has_key'  => $key !== '',
            'key_mask' => $this->maskKey($key),
            'hwid'     => $this->hwid(),
            'product'  => self::PRODUCT,
            'valid'    => $key !== '' ? $this->isValid() : false,
            'expiry'   => (string) Setting::get('license_expiry', ''),
            'message'  => is_array($state) ? ($state['message'] ?? '') : '',
        ];
    }

    /**
     * Panggil server lisensi.
     *
     * @return array{ok: bool, network_error: bool, message: string, expiry: ?string}
     */
    private function verifyRemote(string $key): array
    {
        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->asJson()
                ->post(self::API_URL, [
                    'key'     => $key,
                    'hwid'    => $this->hwid(),
                    'product' => self::PRODUCT,
                ]);

            $data = $response->json();

            if (!is_array($data)) {
                return [
                    'ok' => false,
                    'network_error' => true,
                    'message' => 'Respon server lisensi tidak valid.',
                    'expiry' => null,
                ];
            }

            if (!empty($data['status'])) {
                return [
                    'ok' => true,
                    'network_error' => false,
                    'message' => 'OK',
                    'expiry' => isset($data['expiry']) ? (string) $data['expiry'] : null,
                ];
            }

            return [
                'ok' => false,
                'network_error' => false,
                'message' => (string) ($data['message'] ?? 'Lisensi tidak valid.'),
                'expiry' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('LICENSE: gagal menghubungi server lisensi - ' . $e->getMessage());
            return [
                'ok' => false,
                'network_error' => true,
                'message' => 'Server lisensi tidak dapat dihubungi.',
                'expiry' => null,
            ];
        }
    }

    private function storeState(string $key, bool $valid, string $message, ?string $expiry, int $ttl): array
    {
        Cache::put(self::CACHE_KEY, [
            'key_hash'   => md5($key),
            'valid'      => $valid,
            'message'    => $message,
            'expires_at' => time() + $ttl,
        ], $ttl + 60);

        return ['valid' => $valid, 'message' => $message, 'expiry' => $expiry];
    }

    private function maskKey(string $key): string
    {
        if ($key === '') {
            return '';
        }
        $len = strlen($key);
        if ($len <= 8) {
            return str_repeat('*', $len);
        }
        return substr($key, 0, 4) . str_repeat('*', $len - 8) . substr($key, -4);
    }
}
