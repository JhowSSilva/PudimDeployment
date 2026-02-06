<?php

namespace App\Livewire\Servers;

use App\Models\Server;
use App\Services\AIService;
use Livewire\Component;

class PerformanceChart extends Component
{
    public Server $server;
    public array $chartData = [];
    public string $period = '24h';

    public function mount(Server $server)
    {
        $this->server = $server;
        $this->loadChartData();
    }

    public function updatedPeriod()
    {
        $this->loadChartData();
    }

    public function loadChartData()
    {
        try {
            $aiService = app(AIService::class);
            $prediction = $aiService->predictServerLoad($this->server);
            
            // Build chart data
            $this->chartData = [
                'labels' => $this->getLabelsForPeriod(),
                'datasets' => [
                    [
                        'label' => 'CPU Usage',
                        'data' => $prediction['historical_data'] ?? [],
                        'borderColor' => 'rgb(59, 130, 246)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Predicted',
                        'data' => $prediction['forecast'] ?? [],
                        'borderColor' => 'rgb(239, 68, 68)',
                        'borderDash' => [5, 5],
                        'tension' => 0.1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            $this->chartData = [];
        }
    }

    private function getLabelsForPeriod(): array
    {
        $labels = [];
        $points = $this->period === '24h' ? 24 : 168;
        
        for ($i = 0; $i < $points; $i++) {
            $labels[] = now()->subHours($points - $i)->format('H:i');
        }
        
        return $labels;
    }

    public function render()
    {
        return view('livewire.servers.performance-chart');
    }
}
