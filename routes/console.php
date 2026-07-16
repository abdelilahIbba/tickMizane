<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('archive:monthly --queue --cutoff-months=12 --batch=2000')
    ->monthlyOn(1, '02:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('archive:financial-monthly --queue --cutoff-months=18 --batch=1000')
    ->monthlyOn(1, '03:00')
    ->withoutOverlapping()
    ->onOneServer();
