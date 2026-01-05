<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Http::get('http://host.docker.internal:8095/all-stocks');
})->weekdays()->twiceDaily(10, 15);

Schedule::call(function () {
    Http::get('http://host.docker.internal:8095/insert-stock-daily-data');
})->weekdays()
    ->twiceDaily(10, 16)
    // ->at('11:00')
    // ->at('16:30')
;

Schedule::call(function () {
    Http::get('http://host.docker.internal:8095/get-corporate-info');
})->weekdays()
    ->at('11:00')
    ->at('16:30');
Schedule::call(function () {
    Http::get('http://host.docker.internal:8095/update-all-index');
})->weekdays()
    ->at('11:00')
    ->at('16:30');


// MethodDescription->everyMinute();Run the task every minute.
// ->everyTwoMinutes();Run the task every two minutes.
// ->everyFiveMinutes();Run the task every five minutes.
// ->everyTenMinutes();Run the task every ten minutes.
// ->everyFifteenMinutes();Run the task every fifteen minutes.
// ->everyThirtyMinutes();Run the task every thirty minutes.
// ->hourly();Run the task every hour (at the beginning of the hour).
// ->hourlyAt(17);Run the task every hour at 17 minutes past the hour.
// ->daily(); — Runs at midnight every day ($00:00$).
// ->dailyAt('13:00'); — Runs daily at $13:00$.
// ->twiceDaily(1, 13); — Runs at $1:00$ and $13:00$ daily.
// ->weekly(); — Runs once a week (Sunday at $00:00$).
// ->weeklyOn(1, '8:00'); — Runs every Monday at $8:00$.
// ->monthly(); — Runs on the first day of every month at $00:00$.
// ->weekdays(); — Limit to Monday–Friday.
// ->weekends(); — Limit to Saturday–Sunday.
// ->sundays(); — (And so on for every day of the week).
// ->between($start, $end); — Limit the task to run only between specific times.
// ->withoutOverlapping() to each call to ensure one task finishes before the next one starts.
