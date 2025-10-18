<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () { $this->comment(Inspiring::quote()); })->purpose('Display an inspiring quote');

Schedule::command('whatsapp:init-active')->everyFifteenMinutes();
Schedule::command('whatsapp:process-scheduled')->everyFiveMinutes();
