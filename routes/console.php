<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Inbound sync is webhook-driven. Manual Sync now remains available.
// Optional rare catch-up (disabled by default). Uncomment if you want a backup poll:
// Schedule::command('qbo:sync-checks')->dailyAt('02:00')->withoutOverlapping();
