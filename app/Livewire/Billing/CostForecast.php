<?php

namespace App\Livewire\Billing;

use App\Models\Team;
use App\Services\BillingService;
use Livewire\Component;

class CostForecast extends Component
{
    public Team $team;
    public array $forecast = [];
    public float $currentMonthCost = 0;
    public float $forecastedCost = 0;

    public function mount(Team $team)
    {
        $this->team = $team;
        $this->loadForecast();
    }

    public function loadForecast()
    {
        try {
            $billingService = app(BillingService::class);
            
            // Get servers for this team
            $servers = $this->team->servers;
            
            // Calculate current month cost
            $this->currentMonthCost = $servers->sum(function ($server) use ($billingService) {
                try {
                    $costs = $billingService->calculateServerCosts($server);
                    return $costs['total_cost'];
                } catch (\Exception $e) {
                    return 0;
                }
            });
            
            // Get forecast for next month
            $this->forecast = $billingService->forecastCosts($this->team);
            $this->forecastedCost = $this->forecast['forecasted_cost'] ?? 0;
            
        } catch (\Exception $e) {
            $this->forecast = ['error' => $e->getMessage()];
            $this->currentMonthCost = 0;
            $this->forecastedCost = 0;
        }
    }

    public function refresh()
    {
        $this->loadForecast();
    }

    public function render()
    {
        return view('livewire.billing.cost-forecast');
    }
}
