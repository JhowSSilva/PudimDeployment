<?php

namespace App\Livewire\Servers;

use App\Models\Server;
use App\Services\APMService;
use Livewire\Component;

class ServerMetrics extends Component
{
    public Server $server;
    public array $metrics = [];
    public bool $loading = true;

    public function mount(Server $server)
    {
        $this->server = $server;
        $this->loadMetrics();
    }

    public function loadMetrics()
    {
        $this->loading = true;
        
        try {
            $apmService = app(APMService::class);
            $this->metrics = $apmService->getRealTimeMetrics($this->server);
        } catch (\Exception $e) {
            $this->metrics = [
                'error' => 'Failed to load metrics: ' . $e->getMessage()
            ];
        }
        
        $this->loading = false;
    }

    public function refresh()
    {
        $this->loadMetrics();
    }

    public function render()
    {
        return view('livewire.servers.server-metrics');
    }
}
