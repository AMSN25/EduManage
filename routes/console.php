<?php

use App\Jobs\ExpireTrialSubscriptions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run trial expiry check daily at midnight
Schedule::job(new ExpireTrialSubscriptions)->dailyAt('00:00');

// Database backup daily at 2:00 AM
Schedule::command('backup:database')->dailyAt('02:00');
