<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Server;
use App\Jobs\CollectServerMetrics;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule metrics collection for all servers every minute
Schedule::call(function () {
    Server::where('status', '!=', 'offline')
        ->chunk(10, function ($servers) {
            foreach ($servers as $server) {
                CollectServerMetrics::dispatch($server);
            }
        });
})->everyMinute()->name('collect-server-metrics');

// Schedule SSL certificate expiration check daily at 2 AM
Schedule::command('ssl:check-expiring --days=30')
    ->daily()
    ->at('02:00')
    ->name('check-ssl-certificates');

// Schedule SSL certificate renewal check daily at 2 AM
Schedule::job(new \App\Jobs\RenewSSLCertificatesJob)
    ->daily()
    ->at('02:00')
    ->name('renew-ssl-certificates');

// Backup System Scheduler
// Check for due backups every minute
Schedule::command('backups:schedule')
    ->everyMinute()
    ->name('schedule-backups')
    ->withoutOverlapping();

// Cleanup old backups daily at 3 AM (based on retention policies)
Schedule::command('backups:cleanup')
    ->daily()
    ->at('03:00')
    ->name('cleanup-old-backups')
    ->onOneServer();

// Cleanup old backup job records weekly on Sunday at 4 AM
Schedule::command('backups:cleanup-records')
    ->weekly()
    ->sundays()
    ->at('04:00')
    ->name('cleanup-backup-records')
    ->onOneServer();
