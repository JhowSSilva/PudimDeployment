<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ServerWebController extends Controller
{
    public function index()
    {
        $currentTeam = auth()->user()->getCurrentTeam();
        
        $servers = Server::where('team_id', $currentTeam?->id)
            ->latest()
            ->paginate(10);
            
        return view('servers.index', compact('servers'));
    }

    public function create()
    {
        return view('servers.create-multi-language', [
            'languages' => [
                'php' => [
                    'name' => 'PHP',
                    'icon' => 'php',
                    'versions' => ['8.3', '8.2', '8.1', '8.0']
                ],
                'nodejs' => [
                    'name' => 'Node.js',
                    'icon' => 'nodejs', 
                    'versions' => ['20', '18', '16', '14']
                ],
                'python' => [
                    'name' => 'Python',
                    'icon' => 'python',
                    'versions' => ['3.12', '3.11', '3.10', '3.9']
                ]
            ]
        ]);
    }

    public function createMultiLanguage()
    {
        return view('servers.create-multi-language', [
            'languages' => [
                'php' => [
                    'name' => 'PHP',
                    'icon' => 'php',
                    'versions' => ['8.3', '8.2', '8.1', '8.0']
                ],
                'nodejs' => [
                    'name' => 'Node.js',
                    'icon' => 'nodejs',
                    'versions' => ['20', '18', '16', '14']
                ],
                'python' => [
                    'name' => 'Python',
                    'icon' => 'python',
                    'versions' => ['3.12', '3.11', '3.10', '3.9']
                ]
            ]
        ]);
    }

    public function getLanguageVersions($language)
    {
        $versions = [
            'php' => ['8.3', '8.2', '8.1', '8.0'],
            'nodejs' => ['20', '18', '16', '14'], 
            'python' => ['3.12', '3.11', '3.10', '3.9']
        ];

        return response()->json([
            'versions' => $versions[$language] ?? []
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'ssh_user' => 'required|string|max:255',
            'auth_type' => 'required|in:password,key',
            'ssh_password' => 'required_if:auth_type,password|nullable|string',
            'ssh_key' => 'required_if:auth_type,key|nullable|string',
            // Multi-language fields
            'programming_language' => 'required|string|in:php,nodejs,python',
            'language_version' => 'required|string',
            'webserver' => 'required|string|in:nginx,apache',
            'database' => 'nullable|string|in:mysql,postgresql,mariadb',
            'cache' => 'nullable|string|in:redis,memcached',
            // Cloud provider fields (optional)
            'provider' => 'nullable|string',
            'region' => 'nullable|string',
            'size' => 'nullable|string',
        ]);

        // Set defaults for multi-language support
        $validated['user_id'] = auth()->id();
        $validated['team_id'] = auth()->user()->currentTeam?->id;
        $validated['status'] = 'provisioning';
        
        // Set webserver version based on selection
        $validated['webserver_version'] = $validated['webserver'] === 'nginx' ? '1.18+' : '2.4+';
        
        // Initialize installed_tools as empty array
        $validated['installed_tools'] = json_encode([]);

        $server = Server::create($validated);
        
        // Dispatch the new multi-language installation job
        \App\Jobs\InstallServerStackJob::dispatch($server);
        
        // Log activity
        ActivityLog::log('created', auth()->user()->name . ' criou o servidor ' . $server->name, $server);

        return redirect()->route('servers.index')
            ->with('success', 'Servidor criado com sucesso! A instalação do stack ' . strtoupper($validated['programming_language']) . ' foi iniciada.');
    }

    public function show(Server $server)
    {
        $server->load(['metrics' => fn($q) => $q->latest()->limit(60)]);
        return view('servers.show', compact('server'));
    }

    public function edit(Server $server)
    {
        return view('servers.edit', compact('server'));
    }

    public function update(Request $request, Server $server)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'ssh_user' => 'required|string|max:255',
            'auth_type' => 'required|in:password,key',
            'ssh_password' => 'nullable|string',
            'ssh_key' => 'nullable|string',
        ]);

        // Remove campos vazios de autenticação
        if (empty($validated['ssh_password'])) {
            unset($validated['ssh_password']);
        }
        if (empty($validated['ssh_key'])) {
            unset($validated['ssh_key']);
        }

        $server->update($validated);

        return redirect()->route('servers.index')
            ->with('success', 'Servidor atualizado com sucesso!')
            ->with('success', 'Servidor atualizado com sucesso!');
    }

    public function destroy(Server $server)
    {
        $server->delete();

        return redirect()->route('servers.index')
            ->with('success', 'Servidor deletado com sucesso!')
            ->with('success', 'Servidor deletado com sucesso!');
    }
}
