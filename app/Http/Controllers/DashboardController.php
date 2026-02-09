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
        $teamId = $currentTeam?->id;
        
        // Aggregate counts via DB queries (avoids loading all records)
        $serverQuery = Server::where('team_id', $teamId);
        $totalServers = (clone $serverQuery)->count();
        $serversOnline = (clone $serverQuery)->where('status', 'online')->count();
        $serversOffline = (clone $serverQuery)->where('status', 'offline')->count();
        $serversProvisioning = (clone $serverQuery)->where('provision_status', 'provisioning')->count();
        $totalSites = Site::where('team_id', $teamId)->count();
        
        // Load only 5 servers/sites for display with eager loading
        $servers = Server::where('team_id', $teamId)
            ->with(['latestMetric', 'sites'])
            ->latest()
            ->limit(5)
            ->get();
        
        $sites = Site::where('team_id', $teamId)
            ->with(['server', 'latestDeployment', 'activeSslCertificate'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Recent team activities
        $recentActivities = ActivityLog::where('team_id', $teamId)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();
        
        // Language stats only need a few columns
        $allServers = Server::where('team_id', $teamId)
            ->select('id', 'team_id', 'programming_language', 'language_version', 'status', 'provision_status')
            ->get();
        $languageStats = $this->generateLanguageStats($allServers);
        
        // Get site IDs for deployments without loading full site models
        $siteIds = Site::where('team_id', $teamId)->pluck('id');
        
        $data = [
            'currentTeam' => $currentTeam,
            'totalServers' => $totalServers,
            'serversOnline' => $serversOnline,
            'serversOffline' => $serversOffline,
            'serversProvisioning' => $serversProvisioning,
            'totalSites' => $totalSites,
            'servers' => $servers,
            'sites' => $sites,
            'recentActivities' => $recentActivities,
            'languageStats' => $languageStats,
            'recentDeployments' => Deployment::whereIn('site_id', $siteIds)
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
