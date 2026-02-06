<?php

namespace App\Console\Commands;

use App\Models\SSLCertificate;
use App\Services\SSLService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckSSLCertificates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ssl:check-expiring {--days=30 : Check certificates expiring within this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring SSL certificates and schedule renewals';

    /**
     * Execute the console command.
     */
    public function handle(SSLService $sslService): int
    {
        $days = (int) $this->option('days');
        
        $this->info("Checking for certificates expiring within {$days} days...");
        
        $expiringCerts = SSLCertificate::expiring($days)->get();
        
        if ($expiringCerts->isEmpty()) {
            $this->info('No certificates are expiring soon.');
            return Command::SUCCESS;
        }
        
        $this->warn("Found {$expiringCerts->count()} expiring certificates:");
        
        $headers = ['Domain', 'Provider', 'Expires At', 'Days Left', 'Status'];
        $rows = [];
        
        foreach ($expiringCerts as $cert) {
            $daysLeft = $cert->expires_at ? $cert->expires_at->diffInDays(now()) : 0;
            $rows[] = [
                $cert->primary_domain,
                $cert->provider,
                $cert->expires_at?->format('Y-m-d H:i:s') ?? 'Unknown',
                $daysLeft,
                $cert->status,
            ];
        }
        
        $this->table($headers, $rows);
        
        // Auto-renew Let's Encrypt certificates
        $letsEncryptCerts = $expiringCerts->where('provider', 'letsencrypt')->where('status', 'active');
        
        if ($letsEncryptCerts->isNotEmpty()) {
            $this->info("Scheduling renewal for {$letsEncryptCerts->count()} Let's Encrypt certificates...");
            
            foreach ($letsEncryptCerts as $cert) {
                $this->line("Renewing certificate for: {$cert->primary_domain}");
                
                $result = $sslService->renewCertificate($cert);
                
                if ($result['success']) {
                    $this->info("✓ Successfully renewed: {$cert->primary_domain}");
                } else {
                    $this->error("✗ Failed to renew: {$cert->primary_domain} - {$result['message']}");
                }
            }
        }
        
        // Report custom certificates that need manual renewal
        $customCerts = $expiringCerts->where('provider', 'custom');
        
        if ($customCerts->isNotEmpty()) {
            $this->warn("Manual renewal required for {$customCerts->count()} custom certificates:");
            foreach ($customCerts as $cert) {
                $this->line("- {$cert->primary_domain} (expires: {$cert->expires_at?->format('Y-m-d')})");
            }
        }
        
        return Command::SUCCESS;
    }
}
