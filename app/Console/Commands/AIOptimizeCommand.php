<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\AIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AIOptimizeCommand extends Command
{
    protected $signature = 'ai:optimize {--server_id=}';
    protected $description = 'Run AI optimization on servers';

    public function handle()
    {
        $this->info('Running AI optimization...');

        $query = Server::where('status', 'online');
        
        if ($serverId = $this->option('server_id')) {
            $query->where('id', $serverId);
        }

        $servers = $query->get();
        $optimized = 0;

        foreach ($servers as $server) {
            try {
                $this->info("\nOptimizing server: {$server->name}");
                
                // Create AI service instance for this server
                $aiService = new AIService($server);
                
                // Predict server load
                $this->line('  • Predicting server load...');
                $prediction = $aiService->predictServerLoad();
                if ($prediction['success']) {
                    $preds = $prediction['predictions'];
                    if (count($preds) > 0) {
                        $nextHour = round($preds[0]['cpu'] ?? 0, 1);
                        $nextDay = isset($preds[23]) ? round($preds[23]['cpu'], 1) : 'N/A';
                        $this->info("    Next hour: {$nextHour}% | Next day: {$nextDay}%");
                    }
                    $anomalyCount = count($prediction['anomalies']);
                    if ($anomalyCount > 0) {
                        $this->warn("    ⚠ {$anomalyCount} anomalies detected!");
                    }
                } else {
                    $this->warn("    ⚠ {$prediction['message']}");
                }
                
                // Resource optimization
                $this->line('  • Analyzing resource optimization...');
                $recommendations = $aiService->optimizeResources();
                foreach ($recommendations as $rec) {
                    $this->line("    • {$rec['type']}: {$rec['recommendation']} (Priority: {$rec['priority']})");
                }
                
                // Security threats
                $this->line('  • Detecting security threats...');
                $threats = $aiService->detectSecurityThreats();
                if (count($threats) > 0) {
                    $this->warn("    ⚠ Detected " . count($threats) . " potential threats!");
                    foreach ($threats as $threat) {
                        $this->warn("      - {$threat['type']}: {$threat['description']} (Severity: {$threat['severity']})");
                    }
                } else {
                    $this->info('    ✓ No threats detected');
                }
                
                // Upgrade recommendations
                $this->line('  • Checking upgrade recommendations...');
                $upgrades = $aiService->recommendUpgrades();
                if (!empty($upgrades['upgrade_recommended'])) {
                    $this->warn("    ⚠ Upgrade recommended!");
                    $this->line("      Current plan: {$upgrades['current_plan']}");
                    $this->line("      Recommended plan: {$upgrades['recommended_plan']}");
                    $this->line("      Reason: {$upgrades['reason']}");
                } else {
                    $this->info('    ✓ Current plan is adequate');
                }
                
                $optimized++;
            } catch (\Exception $e) {
                $this->error("✗ Error optimizing server {$server->name}: {$e->getMessage()}");
                Log::error("AI optimization failed for server {$server->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("\n\nOptimized {$optimized} out of {$servers->count()} servers.");
        return Command::SUCCESS;
    }
}
