<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\TerminalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TerminalController extends Controller
{
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
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to connect to server'
                ], 500);
            }

            $output = $terminal->execute($request->command);
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

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
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
