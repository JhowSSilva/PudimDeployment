<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Site;
use App\Models\Deployment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $currentTeam = auth()->user()->getCurrentTeam();
        
        // Team servers
        $servers = Server::where('team_id', $currentTeam?->id)->latest()->get();
        
        // Team sites
        $sites = Site::where('team_id', $currentTeam?->id)->latest()->get();
        
        // Recent team activities
        $recentActivities = ActivityLog::where('team_id', $currentTeam?->id)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();
        
        $data = [
            'currentTeam' => $currentTeam,
            'totalServers' => $servers->count(),
            'serversOnline' => $servers->where('status', 'online')->count(),
            'serversOffline' => $servers->where('status', 'offline')->count(),
            'serversProvisioning' => $servers->where('provision_status', 'provisioning')->count(),
            'totalSites' => $sites->count(),
            'servers' => $servers->take(5),
            'sites' => $sites->take(5),
            'recentActivities' => $recentActivities,
            'recentDeployments' => Deployment::whereIn('site_id', $sites->pluck('id'))
                ->with(['site', 'user'])
                ->latest()
                ->limit(5)
                ->get(),
        ];
        
        return view('dashboard', $data);
    }
}
