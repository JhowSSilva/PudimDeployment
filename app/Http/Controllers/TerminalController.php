<?php

namespace App\Http\Controllers;

use App\Events\TerminalOutput;
use App\Models\Server;
use App\Services\TerminalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TerminalController extends Controller
{
    /**
     * List all servers for terminal access
     */
    public function index()
    {
        $servers = Server::query()
            ->when(auth()->user()->getCurrentTeam(), function ($query, $team) {
                $query->where('team_id', $team->id);
            })
            ->orderBy('name')
            ->get();

        return view('terminal.index', compact('servers'));
    }

    /**
     * Show terminal page
     */
    public function show(Server $server)
    {
        return view('servers.terminal', compact('server'));
    }

    /**
     * Execute command via API
     */
    public function execute(Server $server, Request $request)
    {
        $request->validate([
            'command' => 'required|string|max:5000',
        ]);

        try {
            $terminal = new TerminalService($server);
            
            if (!$terminal->connect()) {
                $error = 'Failed to connect to server';
                broadcast(new TerminalOutput($server->id, $error, 'error'));
                
                return response()->json([
                    'success' => false,
                    'error' => $error
                ], 500);
            }

            // Broadcast command echo
            broadcast(new TerminalOutput($server->id, '$ ' . $request->command, 'command'));
            
            $output = $terminal->execute($request->command);
            
            // Broadcast command output
            broadcast(new TerminalOutput($server->id, $output, 'output'));
            
            $terminal->disconnect();

            return response()->json([
                'success' => true,
                'output' => $output
            ]);

        } catch (\Exception $e) {
            Log::error('Terminal execute error', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);

            broadcast(new TerminalOutput($server->id, 'Error: ' . $e->getMessage(), 'error'));

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute command with real-time streaming (WebSocket)
     */
    public function stream(Server $server, Request $request)
    {
        $request->validate([
            'command' => 'required|string|max:5000',
        ]);
        
        try {
            $terminal = new TerminalService($server);
            
            if (!$terminal->connect()) {
                broadcast(new TerminalOutput($server->id, 'Failed to connect to server', 'error'));
                return response()->json(['success' => false], 500);
            }

            // Echo the command
            broadcast(new TerminalOutput($server->id, '$ ' . $request->command, 'command'));
            
            // Execute and stream output
            $output = $terminal->execute($request->command);
            broadcast(new TerminalOutput($server->id, $output, 'output'));
            
            $terminal->disconnect();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            broadcast(new TerminalOutput($server->id, 'Error: ' . $e->getMessage(), 'error'));
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Get server info
     */
    public function info(Server $server)
    {
        $terminal = new TerminalService($server);
        return response()->json($terminal->getServerInfo());
    }
}
