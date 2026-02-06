<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\BillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TrackUsageCommand extends Command
{
    protected $signature = 'usage:track';
    protected $description = 'Track server usage for billing';

    public function handle(BillingService $billingService)
    {
        $this->info('Tracking server usage...');

        $servers = Server::where('status', 'online')->get();
        $tracked = 0;

        foreach ($servers as $server) {
            try {
                $billingService->trackUsage($server);
                $tracked++;
                $this->info("✓ Tracked usage for server: {$server->name}");
            } catch (\Exception $e) {
                $this->error("✗ Error tracking server {$server->name}: {$e->getMessage()}");
                Log::error("Usage tracking failed for server {$server->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("\nTracked {$tracked} out of {$servers->count()} servers.");
        return Command::SUCCESS;
    }
}
