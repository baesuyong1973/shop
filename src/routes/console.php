<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Requires the scheduler's cron entry on the server (see docs/deploy-aws.md).
Schedule::command('orders:settle-pending')->everyFiveMinutes()->withoutOverlapping();
