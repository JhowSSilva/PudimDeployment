<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

class TerminalService
{
    private SSH2 $ssh;
    private Server $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Connect to server via SSH
     */
    public function connect(): bool
    {
        try {
            $this->ssh = new SSH2($this->server->ip_address, $this->server->ssh_port ?? 22);
            $this->ssh->setTimeout(30);

            if ($this->server->auth_type === 'key' || $this->server->ssh_key_private) {
                // Use SSH key authentication
                $privateKey = $this->server->ssh_key_private ?? $this->server->ssh_key;
                if (!$privateKey) {
                    Log::error('SSH key not found for server', ['server_id' => $this->server->id]);
                    return false;
                }

                $key = PublicKeyLoader::load($privateKey);
                $success = $this->ssh->login($this->server->ssh_user ?? 'root', $key);
            } else {
                // Use password authentication
                $success = $this->ssh->login(
                    $this->server->ssh_user ?? 'root',
                    $this->server->ssh_password ?? ''
                );
            }

            if (!$success) {
                Log::error('SSH login failed', ['server_id' => $this->server->id]);
                return false;
            }

            // Enable PTY for interactive terminal
            $this->ssh->enablePTY();
            
            return true;

        } catch (\Exception $e) {
            Log::error('SSH connection error', [
                'server_id' => $this->server->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Execute command
     */
    public function execute(string $command): string
    {
        if (!isset($this->ssh) || !$this->ssh->isConnected()) {
            if (!$this->connect()) {
                return "Error: Could not connect to server";
            }
        }

        try {
            $output = $this->ssh->exec($command);
            return $output ?: '';
        } catch (\Exception $e) {
            Log::error('Command execution error', [
                'server_id' => $this->server->id,
                'command' => $command,
                'error' => $e->getMessage()
            ]);
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Start interactive shell
     */
    public function startShell(): void
    {
        if (!isset($this->ssh) || !$this->ssh->isConnected()) {
            if (!$this->connect()) {
                throw new \Exception("Could not connect to server");
            }
        }

        $this->ssh->exec('bash');
    }

    /**
     * Write to terminal
     */
    public function write(string $input): void
    {
        if (isset($this->ssh) && $this->ssh->isConnected()) {
            $this->ssh->write($input);
        }
    }

    /**
     * Read from terminal
     */
    public function read(int $maxBytes = 4096): string
    {
        if (isset($this->ssh) && $this->ssh->isConnected()) {
            return $this->ssh->read('', SSH2::READ_SIMPLE) ?: '';
        }
        return '';
    }

    /**
     * Disconnect
     */
    public function disconnect(): void
    {
        if (isset($this->ssh)) {
            $this->ssh->disconnect();
        }
    }

    /**
     * Check if connected
     */
    public function isConnected(): bool
    {
        return isset($this->ssh) && $this->ssh->isConnected();
    }

    /**
     * Get server info for terminal header
     */
    public function getServerInfo(): array
    {
        return [
            'name' => $this->server->name,
            'ip' => $this->server->ip_address,
            'os' => $this->server->os_type . ' ' . ($this->server->os_version ?? ''),
            'user' => $this->server->ssh_user ?? 'root',
        ];
    }
}
