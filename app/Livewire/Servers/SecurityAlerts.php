<?php

namespace App\Livewire\Servers;

use App\Models\Server;
use App\Services\FirewallService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SecurityAlerts extends Component
{
    public Server $server;
    public $threats = [];
    public $blockedIps = [];

    public function mount(Server $server)
    {
        $this->server = $server;
        $this->loadAlerts();
    }

    public function loadAlerts()
    {
        // Load recent security threats
        $this->threats = DB::table('security_threats')
            ->where('server_id', $this->server->id)
            ->where('detected_at', '>', now()->subDay())
            ->orderBy('detected_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        // Load blocked IPs
        $this->blockedIps = DB::table('blocked_ips')
            ->where('server_id', $this->server->id)
            ->where('status', 'active')
            ->orderBy('blocked_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function unblockIp($ipId)
    {
        try {
            $firewallService = app(FirewallService::class);
            $ip = DB::table('blocked_ips')->find($ipId);
            
            if ($ip) {
                $firewallService->unblockIP($this->server, $ip->ip_address);
                $this->loadAlerts();
                $this->dispatch('alert', ['message' => 'IP unblocked successfully', 'type' => 'success']);
            }
        } catch (\Exception $e) {
            $this->dispatch('alert', ['message' => 'Failed to unblock IP: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function refresh()
    {
        $this->loadAlerts();
    }

    public function render()
    {
        return view('livewire.servers.security-alerts');
    }
}
