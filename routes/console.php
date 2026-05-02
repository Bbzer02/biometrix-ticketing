<?php

use App\Models\LoginLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('login-logs:clear', function () {
    $count = LoginLog::count();
    LoginLog::query()->delete();
    $this->info("Deleted {$count} login log record(s).");
})->purpose('Delete all login_logs records');
