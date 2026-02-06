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
