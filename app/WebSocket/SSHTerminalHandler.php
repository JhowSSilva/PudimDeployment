<?php

namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Services\SSHTerminalService;
use App\Models\SSHConnectionLog;
use App\Models\Server;

class SSHTerminalHandler implements MessageComponentInterface
{
    protected \SplObjectStorage $clients;
    protected array $sshSessions = [];
    protected array $userData = [];

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "[" . date('Y-m-d H:i:s') . "] Nova conexão: {$conn->resourceId}\n";

        $conn->send(json_encode([
            'type' => 'info',
            'message' => 'Conectado ao servidor WebSocket'
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $data = json_decode($msg, true);

            if (!isset($data['action'])) {
                $this->sendError($from, 'Ação não especificada');
                return;
            }

            switch ($data['action']) {
                case 'auth':
                    $this->handleAuth($from, $data);
                    break;

                case 'connect':
                    $this->handleConnect($from, $data);
                    break;

                case 'input':
                    $this->handleInput($from, $data);
                    break;

                case 'resize':
                    $this->handleResize($from, $data);
                    break;

                case 'disconnect':
                    $this->handleDisconnect($from);
                    break;

                default:
                    $this->sendError($from, 'Ação desconhecida: ' . $data['action']);
            }
        } catch (\Exception $e) {
            $this->sendError($from, $e->getMessage());
            echo "[ERROR] {$e->getMessage()}\n";
        }
    }

    private function handleAuth(ConnectionInterface $conn, array $data)
    {
        if (!isset($data['user_id']) || !isset($data['token'])) {
            $this->sendError($conn, 'Autenticação inválida');
            return;
        }

        // Aqui você deve validar o token do usuário
        // Por simplicidade, vamos armazenar diretamente
        $this->userData[$conn->resourceId] = [
            'user_id' => $data['user_id'],
            'token' => $data['token']
        ];

        $conn->send(json_encode([
            'type' => 'authenticated',
            'message' => 'Autenticação realizada'
        ]));

        echo "[AUTH] Usuário {$data['user_id']} autenticado\n";
    }

    private function handleConnect(ConnectionInterface $conn, array $data)
    {
        if (!isset($this->userData[$conn->resourceId])) {
            $this->sendError($conn, 'Não autenticado');
            return;
        }

        $userId = $this->userData[$conn->resourceId]['user_id'];
        $serverId = $data['server_id'] ?? null;
        $keyId = $data['key_id'] ?? null;
        $password = $data['password'] ?? null;

        if (!$serverId) {
            $this->sendError($conn, 'ID do servidor não fornecido');
            return;
        }

        try {
            $ssh = new SSHTerminalService();
            $ssh->connect($serverId, $userId, $keyId, $password);

            $this->sshSessions[$conn->resourceId] = $ssh;

            // Criar log de conexão
            $server = Server::find($serverId);
            $ipAddress = $this->getClientIp($conn);
            
            $log = SSHConnectionLog::logConnection($userId, $serverId, $keyId, $ipAddress);
            $this->userData[$conn->resourceId]['log_id'] = $log->id;

            $conn->send(json_encode([
                'type' => 'connected',
                'message' => "Conectado ao servidor: {$server->name}"
            ]));

            echo "[SSH] Conexão SSH estabelecida para usuário {$userId} no servidor {$serverId}\n";

            // Iniciar leitura contínua do output
            $this->startReading($conn);

        } catch (\Exception $e) {
            // Log de falha
            if (isset($log)) {
                $log->markAsFailed($e->getMessage());
            }

            $this->sendError($conn, 'Falha ao conectar: ' . $e->getMessage());
        }
    }

    private function handleInput(ConnectionInterface $conn, array $data)
    {
        if (!isset($this->sshSessions[$conn->resourceId])) {
            $this->sendError($conn, 'Não conectado ao servidor SSH');
            return;
        }

        $input = $data['data'] ?? '';

        try {
            $ssh = $this->sshSessions[$conn->resourceId];
            $ssh->write($input);

            // Ler output imediatamente
            $this->readAndSend($conn);

        } catch (\Exception $e) {
            $this->sendError($conn, 'Erro ao enviar comando: ' . $e->getMessage());
        }
    }

    private function handleResize(ConnectionInterface $conn, array $data)
    {
        // Implementar redimensionamento de terminal se necessário
        $cols = $data['cols'] ?? 80;
        $rows = $data['rows'] ?? 24;

        // phpseclib3 não suporta redimensionamento dinâmico facilmente
        // Esta é uma funcionalidade avançada que requer implementação customizada
    }

    private function handleDisconnect(ConnectionInterface $conn)
    {
        if (isset($this->sshSessions[$conn->resourceId])) {
            $this->sshSessions[$conn->resourceId]->disconnect();
            unset($this->sshSessions[$conn->resourceId]);

            // Marcar log como desconectado
            if (isset($this->userData[$conn->resourceId]['log_id'])) {
                $log = SSHConnectionLog::find($this->userData[$conn->resourceId]['log_id']);
                if ($log) {
                    $log->markAsDisconnected();
                }
            }

            $conn->send(json_encode([
                'type' => 'disconnected',
                'message' => 'Desconectado do servidor'
            ]));

            echo "[SSH] Desconectado: {$conn->resourceId}\n";
        }
    }

    private function startReading(ConnectionInterface $conn)
    {
        // Ler output inicial
        $this->readAndSend($conn);
    }

    private function readAndSend(ConnectionInterface $conn)
    {
        if (!isset($this->sshSessions[$conn->resourceId])) {
            return;
        }

        try {
            $ssh = $this->sshSessions[$conn->resourceId];
            $output = $ssh->read(0.5); // Timeout de 0.5s

            if (!empty($output)) {
                $conn->send(json_encode([
                    'type' => 'output',
                    'data' => $output
                ]));
            }
        } catch (\Exception $e) {
            // Ignorar erros de timeout
        }
    }

    private function sendError(ConnectionInterface $conn, string $message)
    {
        $conn->send(json_encode([
            'type' => 'error',
            'message' => $message
        ]));
    }

    private function getClientIp(ConnectionInterface $conn): ?string
    {
        // Tentar obter IP do cliente
        try {
            return $conn->remoteAddress ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->handleDisconnect($conn);
        $this->clients->detach($conn);
        
        unset($this->userData[$conn->resourceId]);
        
        echo "[" . date('Y-m-d H:i:s') . "] Conexão fechada: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "[ERROR] {$e->getMessage()}\n";
        $this->sendError($conn, 'Erro no servidor: ' . $e->getMessage());
        $conn->close();
    }
}
