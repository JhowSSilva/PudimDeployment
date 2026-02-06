<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Services\BillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateInvoicesCommand extends Command
{
    protected $signature = 'invoices:generate {--team_id=}';
    protected $description = 'Generate monthly invoices for teams';

    public function handle(BillingService $billingService)
    {
        $this->info('Generating invoices...');

        $query = Team::query();
        
        if ($teamId = $this->option('team_id')) {
            $query->where('id', $teamId);
        }

        $teams = $query->get();
        $generated = 0;

        foreach ($teams as $team) {
            try {
                $invoice = $billingService->generateInvoice($team);
                $generated++;
                $this->info("✓ Generated invoice #{$invoice->invoice_number} for team: {$team->name} - Total: \${$invoice->total}");
            } catch (\Exception $e) {
                $this->error("✗ Error generating invoice for team {$team->name}: {$e->getMessage()}");
                Log::error("Invoice generation failed for team {$team->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("\nGenerated {$generated} out of {$teams->count()} invoices.");
        return Command::SUCCESS;
    }
}
