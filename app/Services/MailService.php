<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Configure Laravel mailer at runtime using Gmail credentials
     * stored in persistent cache (set by admin panel).
     */
    public static function configureGmail(): bool
    {
        $username = Cache::get('mail_username');
        $password = Cache::get('mail_password');
        if ($password) {
            try {
                $password = \Illuminate\Support\Facades\Crypt::decryptString($password);
            } catch (\Exception $e) {}
        }
        $from     = Cache::get('mail_from_address');
        $name     = Cache::get('mail_from_name', 'Portal Reseller');

        if (empty($username) || empty($password) || empty($from)) {
            return false; // Not configured yet
        }

        config([
            'mail.default'                   => 'smtp',
            'mail.mailers.smtp.host'         => 'smtp.gmail.com',
            'mail.mailers.smtp.port'         => 587,
            'mail.mailers.smtp.encryption'   => 'tls',
            'mail.mailers.smtp.username'     => $username,
            'mail.mailers.smtp.password'     => $password,
            'mail.from.address'              => $from,
            'mail.from.name'                 => $name,
        ]);

        return true;
    }

    /**
     * Send an OTP email. Uses Gmail if configured, falls back to log.
     *
     * @param string $toEmail  Recipient email address
     * @param string $otp      6-digit OTP code
     * @param string $context  Brief context for subject line (e.g. "Registrasi", "Pengaturan")
     * @return bool            true if sent via SMTP, false if fallback to log
     */
    public static function sendOtp(string $toEmail, string $otp, string $context = 'Verifikasi'): bool
    {
        $fromName = Cache::get('mail_from_name', 'Portal Reseller');
        $isConfigured = self::configureGmail();

        $body = "Halo,\n\n"
              . "Kode OTP {$context} Anda adalah:\n\n"
              . "  {$otp}\n\n"
              . "Kode ini berlaku selama 10 menit. Jangan bagikan kode ini kepada siapa pun.\n\n"
              . "Salam,\n{$fromName}";

        $subject = "Kode OTP {$context} - {$fromName}";

        try {
            Mail::raw($body, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)->subject($subject);
            });

            Log::info("[MailService] OTP {$context} berhasil dikirim ke {$toEmail}.");
            return true;
        } catch (\Exception $e) {
            Log::error("[MailService] Gagal kirim OTP ke {$toEmail}: " . $e->getMessage());

            if (!$isConfigured) {
                Log::info("[MailService] Gmail belum dikonfigurasi. OTP {$context} untuk {$toEmail}: {$otp}");
            } else {
                Log::info("[MailService] SMTP Error fallback. OTP {$context} untuk {$toEmail}: {$otp}");
            }

            return false;
        }
    }

    /**
     * Send a general email. Uses Gmail if configured.
     */
    public static function send(string $toEmail, string $subject, string $body): bool
    {
        self::configureGmail();

        try {
            Mail::raw($body, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)->subject($subject);
            });
            return true;
        } catch (\Exception $e) {
            Log::error("[MailService] Gagal kirim email ke {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check whether Gmail is configured (cached credentials exist).
     */
    public static function isConfigured(): bool
    {
        return !empty(Cache::get('mail_username')) && !empty(Cache::get('mail_password'));
    }
}
