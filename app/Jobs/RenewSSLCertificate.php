<?php

namespace App\Jobs;

use App\Models\SSLCertificate;
use App\Services\SSLService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RenewSSLCertificate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;
    public array $backoff = [30, 60, 120];

    public function __construct(
        public SSLCertificate $certificate
    ) {}

    public function handle(): void
    {
        $sslService = new SSLService();
        
        Log::info("Starting SSL certificate renewal", [
            'certificate_id' => $this->certificate->id,
            'domains' => $this->certificate->domains,
        ]);
        
        $result = $sslService->renewCertificate($this->certificate);
        
        if (!$result['success']) {
            Log::error("SSL certificate renewal failed", [
                'certificate_id' => $this->certificate->id,
                'error' => $result['message'],
            ]);
            
            // Optionally notify admins about renewal failure
            // NotifyAdminsOfSSLRenewalFailure::dispatch($this->certificate, $result['message']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SSL certificate renewal job failed", [
            'certificate_id' => $this->certificate->id,
            'exception' => $exception->getMessage(),
        ]);
        
        $this->certificate->update(['status' => 'renewal_failed']);
    }
}