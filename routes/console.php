<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Task;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('libur:sync')->yearly();
Schedule::command('task:reset-daily')
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta');
Schedule::command('crm:reset-follow-up-today')->dailyAt('00:00')->timezone('Asia/Jakarta');
Schedule::command('finance:carry-forward-opening-balances')
    ->monthlyOn(1, '00:05')
    ->timezone('Asia/Jakarta');
