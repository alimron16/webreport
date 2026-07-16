<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
