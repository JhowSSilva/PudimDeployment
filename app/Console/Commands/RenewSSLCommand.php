<?php

namespace App\Console\Commands;

use App\Jobs\RenewSSLCertificatesJob;
use App\Models\Site;
use App\Services\SSLService;
use Illuminate\Console\Command;

class RenewSSLCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ssl:renew 
                            {--check : Only check which certificates need renewal}
                            {--force : Force renewal even if not expired}
                            {--site= : Renew specific site by domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew SSL certificates for sites';

    /**
     * Execute the console command.
     */
    public function handle(SSLService $ssl)
    {
        $this->info('🔒 SSL Certificate Renewal');
        $this->newLine();

        // Get sites based on options
        $query = Site::where('ssl_enabled', true)
            ->whereNotNull('ssl_expires_at');

        if ($this->option('site')) {
            $query->where('domain', $this->option('site'));
        }

        if (!$this->option('force')) {
            $query->where('ssl_expires_at', '<=', now()->addDays(30));
        }

        $sites = $query->get();

        if ($sites->isEmpty()) {
            $this->info('✅ No certificates need renewal');
            return self::SUCCESS;
        }

        $this->info("Found {$sites->count()} certificate(s) to process");
        $this->newLine();

        if ($this->option('check')) {
            // Just display information
            $table = [];
            foreach ($sites as $site) {
                $check = $ssl->checkExpiration($site);
                $table[] = [
                    $site->domain,
                    $site->ssl_type,
                    $check['days_remaining'],
                    $check['expired'] ? '❌ Expired' : ($check['should_renew'] ? '⚠️  Soon' : '✅ Valid'),
                    $check['expires_at']->format('Y-m-d'),
                ];
            }

            $this->table(
                ['Domain', 'Type', 'Days Left', 'Status', 'Expires'],
                $table
            );

            return self::SUCCESS;
        }

        // Renew certificates
        $progressBar = $this->output->createProgressBar($sites->count());
        $progressBar->start();

        $renewed = 0;
        $failed = 0;

        foreach ($sites as $site) {
            try {
                $result = $ssl->renewCertificate($site);
                
                if ($result) {
                    $renewed++;
                    $this->newLine();
                    $this->info("✅ Renewed: {$site->domain}");
                } else {
                    $failed++;
                    $this->newLine();
                    $this->error("❌ Failed: {$site->domain}");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("❌ Error renewing {$site->domain}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("📊 Summary:");
        $this->info("   Renewed: {$renewed}");
        $this->info("   Failed: {$failed}");
        $this->info("   Total: {$sites->count()}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
