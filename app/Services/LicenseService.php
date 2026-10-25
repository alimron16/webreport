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

    /** Nama produk yang dikunci di server lisensi. */
    private const PRODUCT = 'WEBREPORT';

    /** Interval verifikasi ulang ke server saat lisensi valid (6 jam). */
    private const RECHECK_SECONDS = 21600;

    /** Toleransi offline sejak verifikasi sukses terakhir (72 jam). */
    private const GRACE_SECONDS = 259200;

    /** Lama cache saat lisensi ditolak (mencegah memanggil server di tiap request). */
    private const FAIL_CACHE_SECONDS = 60;

    private const CACHE_KEY = 'license_state';

    /**
     * HWID perangkat kuat: gabungan Hostname + Windows MachineGuid.
     * Mengunci perangkat ke instalasi fisik Windows sehingga tidak bisa dicuri hanya dengan menyamakan nama PC.
     */
    public function hwid(): string
    {
        static $cachedHwid = null;
        if ($cachedHwid !== null) {
            return $cachedHwid;
        }

        $hostname = strtoupper(trim((string) gethostname()));
        $guid = $this->getMachineGuid();

        if ($guid !== '') {
            $cleanGuid = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $guid), 0, 12));
            $cachedHwid = $hostname . '-' . $cleanGuid;
        } else {
            $cachedHwid = $hostname;
        }

        return $cachedHwid;
    }

    /**
     * Ambil MachineGuid resmi dari Windows Registry.
     */
    private function getMachineGuid(): string
    {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = @shell_exec('reg query "HKLM\SOFTWARE\Microsoft\Cryptography" /v MachineGuid 2>nul');
                if ($output && preg_match('/MachineGuid\s+REG_SZ\s+([a-zA-Z0-9\-]+)/i', $output, $matches)) {
                    return trim($matches[1]);
                }
            }
        } catch (\Throwable $e) {
            // Abaikan kesalahan pembacaan registry
        }

        return '';
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
     * Pengecekan cepat untuk middleware & multi-point guard.
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
     * Multi-Point Assertion: Melemparkan exception jika lisensi tidak aktif.
     * Diselipkan di controller dan service transaksi inti agar tidak bisa dibobol dengan sekadar bypass middleware.
     */
    public function assertValid(): void
    {
        if (!$this->isValid()) {
            throw new \RuntimeException('Akses ditolak: Lisensi sistem Web Report tidak aktif atau belum terverifikasi.');
        }
    }

    /**
     * Hitung tanda tangan HMAC untuk mengunci data license_last_ok di SQLite agar tidak bisa diedit manual.
     */
    private function signLastOk(int $timestamp, string $key): string
    {
        $secret = (string) config('app.key', 'moonbyte-guard-salt-2026');
        return hash_hmac('sha256', $timestamp . '|' . $key . '|' . $this->hwid(), $secret . '#moonbyte_tamper_shield');
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
        $now = time();

        if ($remote['ok']) {
            Setting::set('license_last_ok', (string) $now);
            Setting::set('license_last_ok_sig', $this->signLastOk($now, $key));
            Setting::set('license_clock_seq', (string) $now);
            Setting::set('license_expiry', (string) ($remote['expiry'] ?? ''));
            return $this->storeState($key, true, 'Lisensi aktif.', $remote['expiry'], self::RECHECK_SECONDS);
        }

        if ($remote['network_error']) {
            $lastOk = (int) Setting::get('license_last_ok', 0);
            $sig = (string) Setting::get('license_last_ok_sig', '');
            $expectedSig = $this->signLastOk($lastOk, $key);

            // 1. Cek Integritas SQLite (Anti-Edit manual file database)
            $isSigValid = ($lastOk > 0) && ($sig !== '') && hash_equals($expectedSig, $sig);

            // 2. Anti-Future Clock (Waktu last_ok tidak boleh berada di masa depan > 5 menit)
            $isNotFuture = $lastOk <= ($now + 300);

            // 3. Anti-Rollback (Deteksi jika jam PC dimundurkan ke belakang lebih dari 1 jam dari sequence terakhir)
            $lastClock = (int) Setting::get('license_clock_seq', 0);
            $isClockMonotonic = ($lastClock === 0) || ($now >= ($lastClock - 3600));

            $withinGrace = $isSigValid && $isNotFuture && $isClockMonotonic && ($now - $lastOk) < self::GRACE_SECONDS;

            if ($withinGrace) {
                if ($now > $lastClock) {
                    Setting::set('license_clock_seq', (string) $now);
                }
                return $this->storeState(
                    $key,
                    true,
                    'Server lisensi tidak terjangkau, memakai masa toleransi offline.',
                    (string) Setting::get('license_expiry', ''),
                    300
                );
            }

            return $this->storeState(
                $key,
                false,
                'Server lisensi tidak dapat dihubungi dan masa toleransi offline habis atau tidak valid.',
                null,
                self::FAIL_CACHE_SECONDS
            );
        }

        // Ditolak server (key salah, kedaluwarsa, HWID tidak cocok, dst).
        Setting::set('license_last_ok', '0');
        Setting::set('license_last_ok_sig', '');
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
        $now = time();

        if (!$remote['ok']) {
            Log::warning('LICENSE: aktivasi ditolak - ' . $remote['message']);
            return ['valid' => false, 'message' => $remote['message'], 'expiry' => null];
        }

        Setting::set('license_key', $key, true);
        Setting::set('license_last_ok', (string) $now);
        Setting::set('license_last_ok_sig', $this->signLastOk($now, $key));
        Setting::set('license_clock_seq', (string) $now);
        Setting::set('license_expiry', (string) ($remote['expiry'] ?? ''));
        Log::info('LICENSE: aktivasi berhasil, berlaku hingga ' . ($remote['expiry'] ?? '-'));

        return $this->storeState($key, true, 'Lisensi berhasil diaktifkan.', $remote['expiry'], self::RECHECK_SECONDS);
    }

    /**
     * Perhitungan durasi masa aktif dan label status untuk Admin Panel.
     */
    public function getDurationInfo(): array
    {
        $key = $this->getKey();
        $isValid = $key !== '' ? $this->isValid() : false;
        $expiryRaw = trim((string) Setting::get('license_expiry', ''));
        $state = Cache::get(self::CACHE_KEY);

        $remainingDays = null;
        $remainingHours = null;
        $humanDuration = 'Tidak Aktif';
        $badgeClass = 'bg-label-danger';
        $badgeText = 'Lisensi: Tidak Aktif';
        $statusLabel = 'Tidak Aktif';

        if ($key === '') {
            $humanDuration = 'Belum Ada Key';
            $statusLabel = 'Belum Diisi';
            $badgeText = 'Lisensi: Belum Aktif';
        } elseif ($isValid) {
            $isGrace = is_array($state) && str_contains(strtolower($state['message'] ?? ''), 'toleransi');

            if ($isGrace) {
                $lastOk = (int) Setting::get('license_last_ok', 0);
                $remainingGrace = max(0, self::GRACE_SECONDS - (time() - $lastOk));
                $remainingHours = (int) ceil($remainingGrace / 3600);
                $statusLabel = 'Masa Toleransi Offline';
                $humanDuration = $remainingHours . ' Jam Tersisa (Offline)';
                $badgeClass = 'bg-label-warning';
                $badgeText = 'Toleransi Offline: ' . $remainingHours . ' Jam';
            } elseif ($expiryRaw === '' || strtolower($expiryRaw) === 'lifetime' || str_starts_with($expiryRaw, '0000')) {
                $statusLabel = 'Aktif (Permanen)';
                $humanDuration = 'Seumur Hidup / Permanen';
                $badgeClass = 'bg-label-success';
                $badgeText = 'Lisensi: Permanen';
            } else {
                try {
                    $expDate = \Carbon\Carbon::parse($expiryRaw);
                    $now = \Carbon\Carbon::now();

                    if ($now->greaterThan($expDate)) {
                        $statusLabel = 'Kedaluwarsa';
                        $humanDuration = 'Masa Aktif Habis';
                        $badgeClass = 'bg-label-danger';
                        $badgeText = 'Lisensi: Kedaluwarsa';
                    } else {
                        $diffDays = (int) $now->diffInDays($expDate);
                        $remainingDays = $diffDays;
                        $statusLabel = 'Aktif';

                        if ($diffDays > 1) {
                            $humanDuration = $diffDays . ' Hari Tersisa';
                            $badgeText = 'Lisensi: ' . $diffDays . ' Hari Lagi';
                            $badgeClass = $diffDays > 7 ? 'bg-label-success' : 'bg-label-warning';
                        } elseif ($diffDays === 1) {
                            $humanDuration = '1 Hari Lagi (Besok)';
                            $badgeText = 'Lisensi: 1 Hari Lagi';
                            $badgeClass = 'bg-label-warning';
                        } else {
                            $diffHours = (int) max(1, $now->diffInHours($expDate));
                            $remainingHours = $diffHours;
                            $humanDuration = $diffHours . ' Jam Tersisa';
                            $badgeText = 'Lisensi: ' . $diffHours . ' Jam Lagi';
                            $badgeClass = 'bg-label-danger';
                        }
                    }
                } catch (\Throwable $e) {
                    $statusLabel = 'Aktif';
                    $humanDuration = $expiryRaw;
                    $badgeClass = 'bg-label-success';
                    $badgeText = 'Lisensi: Aktif';
                }
            }
        }

        return [
            'has_key'        => $key !== '',
            'key_mask'       => $this->maskKey($key),
            'hwid'           => $this->hwid(),
            'product'        => self::PRODUCT,
            'valid'          => $isValid,
            'status_label'   => $statusLabel,
            'human_duration' => $humanDuration,
            'remaining_days' => $remainingDays,
            'remaining_hours'=> $remainingHours,
            'badge_class'    => $badgeClass,
            'badge_text'     => $badgeText,
            'expiry'         => $expiryRaw,
            'expiry_raw'     => $expiryRaw,
            'message'        => is_array($state) ? ($state['message'] ?? '') : '',
        ];
    }

    /**
     * Informasi untuk halaman aktivasi lisensi & admin panel.
     */
    public function info(): array
    {
        return $this->getDurationInfo();
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
