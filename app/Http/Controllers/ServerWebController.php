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
        $currentTeam = auth()->user()->currentTeam();
        
        $servers = Server::where('team_id', $currentTeam?->id)
            ->latest()
            ->paginate(10);
            
        return view('servers.index', compact('servers'));
    }

    public function create()
    {
        return view('servers.create');
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
        ]);

        // Pegar primeiro usuário ou criar um de teste
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        $validated['user_id'] = auth()->id();
        $validated['team_id'] = auth()->user()->currentTeam()?->id;
        $validated['status'] = 'provisioning';

        $server = Server::create($validated);
        
        // Log activity
        ActivityLog::log('created', auth()->user()->name . ' criou o servidor ' . $server->name, $server);

        return redirect()->route('servers.index')
            ->with('success', 'Servidor criado com sucesso!')
            ->with('success', 'Servidor criado com sucesso!');
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
