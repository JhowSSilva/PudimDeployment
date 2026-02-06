<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\SSHService;
use App\Jobs\ProvisionServerJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServerController extends Controller
{
    /**
     * Display a listing of servers
     */
    public function index()
    {
        $servers = auth()->user()->servers()
            ->latest()
            ->paginate(12);
        
        return view('servers.index', compact('servers'));
    }

    /**
     * Show the form for creating a new server (Step 1: SSH Key)
     */
    public function create(SSHService $sshService)
    {
        // Generate SSH key pair
        $keys = $sshService->generateKeyPair();
        
        session([
            'ssh_public_key' => $keys['public'],
            'ssh_private_key' => $keys['private']
        ]);
        
        // Generate random server name (adjective + noun)
        $adjectives = ['glittering', 'sparkling', 'radiant', 'shimmering', 'brilliant', 'vibrant', 'cosmic', 'stellar', 'ethereal', 'luminous'];
        $nouns = ['archipelago', 'mountain', 'ocean', 'forest', 'volcano', 'glacier', 'canyon', 'delta', 'plateau', 'reef'];
        $suggestedName = $adjectives[array_rand($adjectives)] . '-' . $nouns[array_rand($nouns)];
        
        return view('servers.create', [
            'ssh_public_key' => $keys['public'],
            'suggested_name' => $suggestedName,
            'current_step' => 1
        ]);
    }

    /**
     * Store server details (Step 2)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'os' => 'required|in:ubuntu-20.04,ubuntu-22.04,ubuntu-24.04',
            'type' => 'required|in:server,database,cache,load_balancer',
            
            // Stack configuration (Step 3)
            'webserver' => 'nullable|in:nginx,apache,openlitespeed,caddy',
            'php_versions' => 'nullable|array',
            'php_versions.*' => 'string|in:8.1,8.2,8.3,8.4,8.5',
            'database_type' => 'nullable|in:mysql,mariadb,postgresql,mongodb',
            'database_version' => 'nullable|string',
            'cache_service' => 'nullable|in:redis,memcached',
            'nodejs_version' => 'nullable|in:18,20,22,23',
            'installed_software' => 'nullable|array',
        ]);
        
        // Create server
        $server = Server::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'ip_address' => $validated['ip_address'],
            'ssh_port' => $validated['ssh_port'],
            'os' => $validated['os'],
            'type' => $validated['type'],
            'webserver' => $validated['webserver'] ?? null,
            'php_versions' => $validated['php_versions'] ?? [],
            'database_type' => $validated['database_type'] ?? null,
            'database_version' => $validated['database_version'] ?? null,
            'cache_service' => $validated['cache_service'] ?? null,
            'nodejs_version' => $validated['nodejs_version'] ?? null,
            'installed_software' => $validated['installed_software'] ?? [],
            'ssh_key_private' => encrypt(session('ssh_private_key')),
            'ssh_key_public' => session('ssh_public_key'),
            'deploy_user' => 'admin_agile',
            'status' => 'pending',
            'provision_status' => 'pending',
        ]);
        
        // Clear session keys
        session()->forget(['ssh_private_key', 'ssh_public_key']);
        
        // Dispatch provisioning job
        ProvisionServerJob::dispatch($server);
        
        return redirect()->route('servers.show', $server)
            ->with('success', 'Server is being provisioned! This may take 10-15 minutes.');
    }

    /**
     * Display the specified server
     */
    public function show(Server $server)
    {
        $this->authorize('view', $server);
        
        $server->load('sites');
        
        return view('servers.show', compact('server'));
    }

    /**
     * Show the form for editing the specified server
     */
    public function edit(Server $server)
    {
        $this->authorize('update', $server);
        
        return view('servers.edit', compact('server'));
    }

    /**
     * Update the specified server
     */
    public function update(Request $request, Server $server)
    {
        $this->authorize('update', $server);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $server->update($validated);
        
        return redirect()->route('servers.show', $server)
            ->with('success', 'Server updated successfully!');
    }

    /**
     * Remove the specified server
     */
    public function destroy(Server $server)
    {
        $this->authorize('delete', $server);
        
        // Check if server has sites
        if ($server->sites()->count() > 0) {
            return back()->with('error', 'Cannot delete server with active sites. Please delete all sites first.');
        }
        
        $serverName = $server->name;
        $server->delete();
        
        return redirect()->route('servers.index')
            ->with('success', "Server '{$serverName}' deleted successfully!");
    }
}
