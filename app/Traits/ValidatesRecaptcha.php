<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
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
        if (!Cache::get('recaptcha_enabled', false)) {
            return true; // Auto-pass if not enabled
        }

        $token = $request->input('g-recaptcha-response');
        if (!$token) {
            return false;
        }

        $secret = Cache::get('recaptcha_secret_key');
        if ($secret) {
            try {
                $secret = \Illuminate\Support\Facades\Crypt::decryptString($secret);
            } catch (\Exception $e) {}
        }
        if (!$secret) {
            return true; // Skip validation if no secret is configured
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            $body = $response->json();
            return isset($body['success']) && $body['success'] === true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('reCAPTCHA verification failed: ' . $e->getMessage());
            return false;
        }
    }
}
