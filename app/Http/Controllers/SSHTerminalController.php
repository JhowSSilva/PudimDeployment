<?php

namespace App\Http\Controllers;

use App\Models\SSHConnectionLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SSHTerminalController extends Controller
{
    /**
     * Renderizar página do terminal SSH
     */
    public function index()
    {
        return view('ssh.terminal');
    }

    /**
     * Renderizar página de gerenciamento de chaves SSH
     */
    public function keys()
    {
        return view('ssh.keys');
    }

    /**
     * Obter logs de conexão SSH
     */
    public function getLogs(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $limit = $request->input('limit', 50);
            
            $logs = SSHConnectionLog::getByUserId($userId, $limit);

            return response()->json([
                'success' => true,
                'logs' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter logs: ' . $e->getMessage()
            ], 500);
        }
    }
}
