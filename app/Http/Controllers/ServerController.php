<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\SSHService;
use App\Services\StackInstallationService;
use App\Jobs\ProvisionServerJob;
use App\Jobs\InstallServerStackJob;
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
    public function create(SSHService $sshService, StackInstallationService $stackService)
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
        
        // Get available languages and their configurations
        $availableLanguages = $stackService->getAvailableLanguages();
        
        return view('servers.create', [
            'ssh_public_key' => $keys['public'],
            'suggested_name' => $suggestedName,
            'current_step' => 1,
            'available_languages' => $availableLanguages,
        ]);
    }

    /**
     * Store server details (Step 2)
     */
    public function store(Request $request, StackInstallationService $stackService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'os' => 'required|in:ubuntu-20.04,ubuntu-22.04,ubuntu-24.04',
            'type' => 'required|in:server,database,cache,load_balancer',
            
            // Multi-language stack configuration
            'programming_language' => 'required|in:php,nodejs,python,ruby,go,java,dotnet,rust,elixir,static',
            'language_version' => 'required|string',
            'webserver' => 'nullable|in:nginx,apache,openlitespeed,caddy,none',
            'webserver_version' => 'nullable|string',
            'database_type' => 'nullable|in:mysql,mariadb,postgresql,mongodb,none',
            'database_version' => 'nullable|string',
            'cache_service' => 'nullable|in:redis,memcached,none',
            
            // Language-specific configurations
            // PHP
            'install_composer' => 'nullable|boolean',
            'php_extensions' => 'nullable|array',
            
            // Node.js
            'install_yarn' => 'nullable|boolean',
            'install_pm2' => 'nullable|boolean',
            'global_packages' => 'nullable|array',
            
            // Python
            'install_poetry' => 'nullable|boolean',
            'install_pipenv' => 'nullable|boolean',
            'python_packages' => 'nullable|array',
        ]);
        
        // Validate language version
        $supportedVersions = $stackService->getSupportedVersions($validated['programming_language']);
        if (!in_array($validated['language_version'], $supportedVersions)) {
            return back()->withErrors(['language_version' => 'Invalid version for selected language.']);
        }
        
        // Prepare stack configuration
        $stackConfig = $this->prepareStackConfig($validated);
        
        // Create server
        $server = Server::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'ip_address' => $validated['ip_address'],
            'ssh_port' => $validated['ssh_port'],
            'os' => $validated['os'],
            'type' => $validated['type'],
            'programming_language' => $validated['programming_language'],
            'language_version' => $validated['language_version'],
            'webserver' => $validated['webserver'] ?? null,
            'webserver_version' => $validated['webserver_version'] ?? null,
            'database_type' => $validated['database_type'] ?? null,
            'database_version_new' => $validated['database_version'] ?? null,
            'cache_service' => $validated['cache_service'] ?? null,
            'stack_config' => $stackConfig,
            'ssh_key_private' => encrypt(session('ssh_private_key')),
            'ssh_key_public' => session('ssh_public_key'),
            'deploy_user' => 'admin_agile',
            'status' => 'pending',
            'provision_status' => 'pending',
        ]);
        
        // Clear session keys
        session()->forget(['ssh_private_key', 'ssh_public_key']);
        
        // Dispatch appropriate provisioning job
        if ($validated['programming_language'] === 'php') {
            // Use legacy system for now
            ProvisionServerJob::dispatch($server, $stackConfig);
        } else {
            // Use new multi-language system
            InstallServerStackJob::dispatch($server, $stackConfig);
        }
        
        return redirect()->route('servers.show', $server)
            ->with('success', 'Server is being provisioned! This may take 10-15 minutes.');
    }
    
    /**
     * Prepare stack configuration based on validated input
     */
    protected function prepareStackConfig(array $validated): array
    {
        $config = [
            'version' => $validated['language_version'],
        ];
        
        // Language-specific configurations
        switch ($validated['programming_language']) {
            case 'php':
                $config['install_composer'] = $validated['install_composer'] ?? true;
                if (!empty($validated['php_extensions'])) {
                    $config['install_extensions'] = $validated['php_extensions'];
                }
                break;
                
            case 'nodejs':
                $config['install_yarn'] = $validated['install_yarn'] ?? true;
                $config['install_pm2'] = $validated['install_pm2'] ?? true;
                if (!empty($validated['global_packages'])) {
                    $config['global_packages'] = $validated['global_packages'];
                }
                break;
                
            case 'python':
                $config['install_poetry'] = $validated['install_poetry'] ?? false;
                $config['install_pipenv'] = $validated['install_pipenv'] ?? false;
                if (!empty($validated['python_packages'])) {
                    $config['global_packages'] = $validated['python_packages'];
                }
                break;
        }
        
        return $config;
    }
        
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
    
    /**
     * Get supported versions for a programming language (AJAX)
     */
    public function getVersions(Request $request, StackInstallationService $stackService)
    {
        $language = $request->get('language');
        
        if (!$language) {
            return response()->json(['error' => 'Language parameter is required'], 400);
        }
        
        $versions = $stackService->getSupportedVersions($language);
        $defaultConfig = $stackService->getDefaultConfig($language);
        
        return response()->json([
            'versions' => $versions,
            'latest_version' => $stackService->getLatestVersion($language),
            'default_config' => $defaultConfig,
        ]);
    }
    
    /**
     * Get installation progress for server (AJAX)
     */
    public function getInstallationProgress(Server $server, StackInstallationService $stackService)
    {
        $this->authorize('view', $server);
        
        $progress = $stackService->getInstallationProgress($server);
        
        return response()->json($progress);
    }
    
    /**
     * Validate server installation
     */
    public function validateInstallation(Server $server, StackInstallationService $stackService)
    {
        $this->authorize('manage', $server);
        
        try {
            $isValid = $stackService->validate($server);
            
            return response()->json([
                'success' => true,
                'is_valid' => $isValid,
                'message' => $isValid ? 'Installation is valid' : 'Installation validation failed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
