<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:cancel-expired')->everyFiveMinutes();
Schedule::command('security:check-alerts')->everyFiveMinutes();

// پاکسازی لاگ‌های قدیمی (هر روز ساعت ۳ صبح)
Schedule::command('audit-log:purge')->dailyAt('03:00');
