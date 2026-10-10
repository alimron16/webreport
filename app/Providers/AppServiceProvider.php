<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dynamically load OtomaX SQL Server configuration from encrypted database settings
        try {
            if (\App\Models\Setting::has('otomax_db_host')) {
                config([
                    'database.connections.otomax.host' => \App\Models\Setting::get('otomax_db_host', '127.0.0.1'),
                    'database.connections.otomax.port' => (int)\App\Models\Setting::get('otomax_db_port', 1433),
                    'database.connections.otomax.database' => \App\Models\Setting::get('otomax_db_database', 'otomax'),
                    'database.connections.otomax.username' => \App\Models\Setting::get('otomax_db_username', 'sa'),
                    'database.connections.otomax.password' => \App\Models\Setting::get('otomax_db_password', ''),
                ]);
            }
        } catch (\Throwable $e) {
            // Table may not exist yet during migration
        }

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $logoUrl = \Illuminate\Support\Facades\Cache::get('site_logo_url');
            if (!$logoUrl) {
                $logoPath = \Illuminate\Support\Facades\Cache::get('site_logo_path');
                if ($logoPath && \Illuminate\Support\Facades\Storage::disk('local')->exists($logoPath)) {
                    $logoUrl = '/logo';
                } else {
                    $files = \Illuminate\Support\Facades\Storage::disk('local')->files('branding');
                    if (!empty($files)) {
                        $logoUrl = '/logo';
                    }
                }
            }

            $siteTitle = \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX');
            $siteSubtitle = \Illuminate\Support\Facades\Cache::get('site_subtitle', 'Portal Reseller Digital');
            $waNumber = \Illuminate\Support\Facades\Cache::get('whatsapp_number', '');
            
            $waRegistrationUrl = null;
            $waCsUrl = null;

            if (!empty($waNumber)) {
                $cleanWa = preg_replace('/[^0-9]/', '', (string)$waNumber);
                if (str_starts_with($cleanWa, '0')) {
                    $cleanWa = '62' . substr($cleanWa, 1);
                } elseif (str_starts_with($cleanWa, '8')) {
                    $cleanWa = '62' . $cleanWa;
                }

                $defaultRegMsg = "Halo Admin, saya ingin mendaftar menjadi agen/reseller di {site_title}. Mohon informasikan syarat dan cara pendaftarannya. Terima kasih!";
                $regMsg = \Illuminate\Support\Facades\Cache::get('whatsapp_registration_message', $defaultRegMsg);
                $regMsgParsed = str_replace('{site_title}', $siteTitle, $regMsg);
                $waRegistrationUrl = 'https://wa.me/' . $cleanWa . '?text=' . rawurlencode($regMsgParsed);

                $defaultCsMsg = "Halo Customer Service {site_title}, saya membutuhkan informasi/bantuan seputar transaksi Webreport. Terima kasih.";
                $csMsg = \Illuminate\Support\Facades\Cache::get('whatsapp_cs_message', $defaultCsMsg);
                $csMsgParsed = str_replace('{site_title}', $siteTitle, $csMsg);
                $waCsUrl = 'https://wa.me/' . $cleanWa . '?text=' . rawurlencode($csMsgParsed);
            }

            $view->with([
                'portal_site_title'        => $siteTitle,
                'portal_site_subtitle'     => $siteSubtitle,
                'portal_logo_url'          => $logoUrl,
                'portal_whatsapp_number'   => $waNumber,
                'portal_whatsapp_reg_url'  => $waRegistrationUrl,
                'portal_whatsapp_cs_url'   => $waCsUrl,
                'portal_whatsapp_reg_msg'  => \Illuminate\Support\Facades\Cache::get('whatsapp_registration_message', ''),
                'portal_whatsapp_cs_msg'   => \Illuminate\Support\Facades\Cache::get('whatsapp_cs_message', ''),
                'portal_telegram_cs'       => \Illuminate\Support\Facades\Cache::get('telegram_cs_username', ''),
                'portal_telegram_channel'  => \Illuminate\Support\Facades\Cache::get('telegram_channel_url', ''),
                'portal_registration_mode' => \Illuminate\Support\Facades\Cache::get('registration_mode', 'whatsapp'),
                'portal_hero_headline'     => \Illuminate\Support\Facades\Cache::get('hero_headline', ''),
                'portal_hero_subheadline'  => \Illuminate\Support\Facades\Cache::get('hero_subheadline', ''),
                'portal_cs_hours'          => \Illuminate\Support\Facades\Cache::get('cs_working_hours', '24 Jam Nonstop'),
            ]);
        });
    }
}
