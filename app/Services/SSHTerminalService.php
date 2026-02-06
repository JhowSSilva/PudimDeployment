<?php

namespace App\Services;

use phpseclib3\Net\SSH2;
use App\Models\SSHKey;
use App\Models\Server;
use App\Helpers\EncryptionHelper;

class SSHTerminalService
{
    private ?SSH2 $connection = null;
    private EncryptionHelper $encryption;

    public function __construct()
    {
        $this->encryption = new EncryptionHelper();
    }

    /**
     * Conectar ao servidor SSH para terminal interativo
     *
     * @param int $serverId
     * @param int $userId
     * @param int|null $keyId
     * @param string|null $password
     * @return bool
     */
    public function connect(int $serverId, int $userId, ?int $keyId = null, ?string $password = null): bool
    {
        // Buscar dados do servidor
        $server = Server::where('id', $serverId)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('team', function ($q) use ($userId) {
                        $q->whereHas('users', function ($qu) use ($userId) {
                            $qu->where('users.id', $userId);
                        });
                    });
            })
            ->first();

        if (!$server) {
            throw new \Exception('Servidor não encontrado');
        }

        $host = $server->ip_address ?? $server->public_ip;
        $port = $server->ssh_port ?? 22;
        $username = $server->ssh_user ?? 'root';

        if (!$host) {
            throw new \Exception('Host do servidor não configurado');
        }

        // Criar conexão SSH com timeout
        $this->connection = new SSH2($host, $port, 10);
        
        // Desabilitar verificação de host key (para desenvolvimento)
        // Em produção, implementar verificação adequada
        $this->connection->getServerPublicHostKey();

        // Autenticação
        $authenticated = false;

        if ($keyId) {
            // Autenticação por chave SSH
            $sshKey = SSHKey::where('id', $keyId)
                ->where('user_id', $userId)
                ->first();

            if (!$sshKey) {
                throw new \Exception('Chave SSH não encontrada');
            }

            // Descriptografar chave privada
            $privateKey = $this->encryption->decrypt($sshKey->private_key_encrypted);

            $keyObj = \phpseclib3\Crypt\PublicKeyLoader::load($privateKey);

            $authenticated = $this->connection->login($username, $keyObj);
        } elseif ($password) {
            // Autenticação por senha
            $authenticated = $this->connection->login($username, $password);
        } elseif ($server->default_key_id) {
            // Usar chave padrão do servidor
            $sshKey = SSHKey::find($server->default_key_id);
            
            if ($sshKey) {
                $privateKey = $this->encryption->decrypt($sshKey->private_key_encrypted);
                $keyObj = \phpseclib3\Crypt\PublicKeyLoader::load($privateKey);
                $authenticated = $this->connection->login($username, $keyObj);
            }
        } else {
            throw new \Exception('Credenciais não fornecidas');
        }

        if (!$authenticated) {
            throw new \Exception('Falha na autenticação SSH');
        }

        // Habilitar PTY para shell interativo
        $this->connection->enablePTY();
        
        return true;
    }

    /**
     * Executar comando
     *
     * @param string $command
     * @return string
     */
    public function executeCommand(string $command): string
    {
        if (!$this->connection || !$this->connection->isConnected()) {
            throw new \Exception('Não conectado ao servidor');
        }

        return $this->connection->exec($command);
    }

    /**
     * Escrever no shell interativo
     *
     * @param string $data
     * @return void
     */
    public function write(string $data): void
    {
        if (!$this->connection || !$this->connection->isConnected()) {
            throw new \Exception('Não conectado ao servidor');
        }

        $this->connection->write($data);
    }

    /**
     * Ler output do shell
     *
     * @param int $timeout
     * @return string
     */
    public function read(int $timeout = 1): string
    {
        if (!$this->connection || !$this->connection->isConnected()) {
            throw new \Exception('Não conectado ao servidor');
        }

        $this->connection->setTimeout($timeout);
        return $this->connection->read();
    }

    /**
     * Desconectar
     *
     * @return void
     */
    public function disconnect(): void
    {
        if ($this->connection) {
            $this->connection->disconnect();
            $this->connection = null;
        }
    }

    /**
     * Verificar se está conectado
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connection && $this->connection->isConnected();
    }

    /**
     * Obter conexão SSH
     *
     * @return SSH2|null
     */
    public function getConnection(): ?SSH2
    {
        return $this->connection;
    }
}
