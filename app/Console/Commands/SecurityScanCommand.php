<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\FirewallService;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SecurityScanCommand extends Command
{
    protected $signature = 'security:scan {--server_id=}';
    protected $description = 'Run security scan on servers';

    public function handle(FirewallService $firewallService, NotificationService $notificationService)
    {
        $this->info('Running security scan...');

        $query = Server::where('status', 'online');
        
        if ($serverId = $this->option('server_id')) {
            $query->where('id', $serverId);
        }

        $servers = $query->get();
        $scanned = 0;

        foreach ($servers as $server) {
            try {
                $this->info("\nScanning server: {$server->name}");
                $issuesFound = false;
                $issuesDetails = [];
                
                // Rootkit scan
                $this->line('  • Checking for rootkits...');
                $rootkits = $firewallService->scanForRootkits($server);
                if (count($rootkits) > 0) {
                    $this->warn("    ⚠ Found " . count($rootkits) . " potential rootkits!");
                    $issuesFound = true;
                    $issuesDetails[] = count($rootkits) . " rootkits detectados";
                } else {
                    $this->info('    ✓ No rootkits detected');
                }
                
                // Malware scan
                $this->line('  • Scanning for malware...');
                $malware = $firewallService->scanForMalware($server);
                if (count($malware) > 0) {
                    $this->warn("    ⚠ Found " . count($malware) . " potential malware files!");
                    $issuesFound = true;
                    $issuesDetails[] = count($malware) . " arquivos de malware encontrados";
                } else {
                    $this->info('    ✓ No malware detected');
                }
                
                // Get banned IPs
                $this->line('  • Checking Fail2ban status...');
                $bannedIPs = $firewallService->getBannedIPs($server);
                $this->info("    ℹ Currently " . count($bannedIPs) . " IPs are banned");
                
                // Send notification if security issues were found
                if ($issuesFound) {
                    $owner = $server->team->owner;
                    $notificationService->security(
                        user: $owner,
                        team: $server->team,
                        server: $server,
                        issue: 'Security scan encontrou problemas: ' . implode(', ', $issuesDetails),
                        severity: 'high',
                        actionUrl: route('servers.show', $server)
                    );
                }
                
                $scanned++;
            } catch (\Exception $e) {
                $this->error("✗ Error scanning server {$server->name}: {$e->getMessage()}");
                Log::error("Security scan failed for server {$server->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("\n\nScanned {$scanned} out of {$servers->count()} servers.");
        return Command::SUCCESS;
    }
}
