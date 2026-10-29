<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

trait ValidatesRecaptcha
{
    /**
     * Validate Google reCAPTCHA v2 response
     *
     * @param Request $request
     * @return bool
     */
    protected function validateRecaptcha(Request $request)
    {
        $enabled = Cache::get('recaptcha_enabled');
        if ($enabled === null) {
            $enabled = (\App\Models\Setting::get('recaptcha_enabled') === '1' || \App\Models\Setting::get('recaptcha_enabled') === true);
        } else {
            $enabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
        }

        if (!$enabled) {
            return true; // Auto-pass if not enabled
        }

        $siteKey = Cache::get('recaptcha_site_key') ?: \App\Models\Setting::get('recaptcha_site_key', '');
        $secret = Cache::get('recaptcha_secret_key');
        if (!$secret) {
            $secret = \App\Models\Setting::get('recaptcha_secret_key');
        }
        if ($secret) {
            // Unravel potential multi-encryption safely
            while (is_string($secret) && str_starts_with($secret, 'eyJ')) {
                try {
                    $secret = \Illuminate\Support\Facades\Crypt::decryptString($secret);
                } catch (\Throwable $e) {
                    break;
                }
            }
        }

        if (empty($siteKey) || empty($secret)) {
            Log::warning("reCAPTCHA is enabled but Site Key or Secret Key is empty. Auto-passing to avoid locking users out.");
            return true;
        }

        $token = $request->input('g-recaptcha-response');
        if (!$token) {
            Log::warning("reCAPTCHA validation failed: token 'g-recaptcha-response' is empty from IP: {$request->ip()}");
            return false;
        }

        try {
            // Gunakan withoutVerifying() agar tidak gagal karena missing CA certificate bundle di Windows
            $response = Http::asForm()
                ->withoutVerifying()
                ->timeout(10)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => trim($secret),
                    'response' => trim($token),
                    'remoteip' => $request->ip(),
                ]);

            if (!$response->successful()) {
                Log::error("reCAPTCHA HTTP request failed with status: {$response->status()}");
                return false;
            }

            $body = $response->json();
            $isSuccess = isset($body['success']) && $body['success'] === true;

            if (!$isSuccess) {
                $errorCodes = isset($body['error-codes']) ? implode(', ', (array)$body['error-codes']) : 'no-error-code';
                Log::warning("reCAPTCHA verification rejected by Google API. Errors: [{$errorCodes}], IP: {$request->ip()}");
            }

            return $isSuccess;
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification exception: ' . $e->getMessage());
            return false;
        }
    }
}
