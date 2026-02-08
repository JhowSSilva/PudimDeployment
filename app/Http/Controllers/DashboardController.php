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
        
        // Generate language statistics
        $languageStats = $this->generateLanguageStats($servers);
        
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
            'languageStats' => $languageStats,
            'recentDeployments' => Deployment::whereIn('site_id', $sites->pluck('id'))
                ->with(['site', 'user'])
                ->latest()
                ->limit(5)
                ->get(),
        ];
        
        return view('dashboard', $data);
    }

    protected function generateLanguageStats($servers)
    {
        $stats = [];
        $totalServers = $servers->count();
        
        if ($totalServers === 0) {
            return $stats;
        }
        
        // Group servers by programming language
        $languageGroups = $servers->groupBy('programming_language');
        
        foreach ($languageGroups as $language => $serversInLanguage) {
            // Default to 'php' if no language specified (backward compatibility)
            $language = $language ?: 'php';
            
            $count = $serversInLanguage->count();
            $percentage = ($count / $totalServers) * 100;
            
            // Count by status
            $online = $serversInLanguage->where('status', 'online')->count();
            $provisioning = $serversInLanguage->where('status', 'provisioning')->count();
            
            // Group by language version
            $versions = $serversInLanguage->groupBy('language_version')
                ->map(function ($versionGroup) {
                    return $versionGroup->count();
                })
                ->filter(function ($count, $version) {
                    return !empty($version); // Only include versions that are set
                });
            
            $stats[$language] = [
                'count' => $count,
                'percentage' => $percentage,
                'online' => $online,
                'provisioning' => $provisioning,
                'versions' => $versions->toArray()
            ];
        }
        
        // Sort by count descending
        uasort($stats, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        
        return $stats;
    }
}
