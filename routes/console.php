<?php

use App\Models\InvestmentSetting;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$settings = InvestmentSetting::get();

if ($settings->auto_profit_enabled) {
    if ($settings->auto_profit_frequency === 'daily') {
        Schedule::command('investments:process-profits')->daily();
    } elseif ($settings->auto_profit_frequency === 'weekly') {
        Schedule::command('investments:process-profits')->weekly();
    } else {
        Schedule::command('investments:process-profits')->monthly();
    }
}
