<?php

namespace App\Services;

use App\Models\Server;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use Illuminate\Support\Facades\Log;

class SSHConnectionService
{
    private ?SSH2 $connection = null;
    private Server $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Connect to server via SSH
     * 
     * @throws \Exception
     */
    public function connect(): bool
    {
        try {
            $this->connection = new SSH2($this->server->ip_address, $this->server->ssh_port);
            
            // Authenticate based on auth type
            if ($this->server->auth_type === 'key') {
                $key = PublicKeyLoader::load($this->server->ssh_key);
                $authenticated = $this->connection->login($this->server->ssh_user, $key);
            } else {
                $authenticated = $this->connection->login(
                    $this->server->ssh_user,
                    $this->server->ssh_password
                );
            }

            if (!$authenticated) {
                throw new \Exception('SSH authentication failed');
            }

            Log::info("SSH connection established to server {$this->server->name}");
            return true;

        } catch (\Exception $e) {
            Log::error("SSH connection failed for server {$this->server->name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute a command on the remote server
     * 
     * @param string $command Command to execute
     * @param bool $pty Enable pseudo-terminal (useful for interactive commands)
     * @return array ['output' => string, 'exit_code' => int]
     * @throws \Exception
     */
    public function execute(string $command, bool $pty = false): array
    {
        if (!$this->connection) {
            $this->connect();
        }

        // Validate command to prevent injection (basic validation)
        if (preg_match('/[;&|`$]/', $command) && !$this->isWhitelisted($command)) {
            throw new \Exception('Potentially unsafe command detected');
        }

        if ($pty) {
            $this->connection->enablePTY();
        }

        try {
            $output = $this->connection->exec($command);
            $exitCode = $this->connection->getExitStatus();

            Log::debug("Executed command on {$this->server->name}: {$command}");

            return [
                'output' => $output,
                'exit_code' => $exitCode,
            ];

        } catch (\Exception $e) {
            Log::error("Command execution failed on {$this->server->name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute multiple commands in sequence
     * 
     * @param array $commands Array of commands to execute
     * @return array Array of results
     */
    public function executeBatch(array $commands): array
    {
        $results = [];
        
        foreach ($commands as $command) {
            $results[] = $this->execute($command);
            
            // Stop on first error
            if (end($results)['exit_code'] !== 0) {
                break;
            }
        }

        return $results;
    }

    /**
     * Test server connectivity
     * 
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $this->connect();
            $result = $this->execute('echo "OK"');
            return $result['exit_code'] === 0 && trim($result['output']) === 'OK';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Detect server operating system
     * 
     * @return array ['os_type' => string, 'os_version' => string]
     */
    public function detectOS(): array
    {
        try {
            // Get OS release info
            $result = $this->execute('cat /etc/os-release');
            
            $os_type = 'Unknown';
            $os_version = 'Unknown';

            if (preg_match('/^NAME="?([^"\n]+)"?/m', $result['output'], $matches)) {
                $os_type = trim($matches[1]);
            }

            if (preg_match('/^VERSION_ID="?([^"\n]+)"?/m', $result['output'], $matches)) {
                $os_version = trim($matches[1]);
            }

            return [
                'os_type' => $os_type,
                'os_version' => $os_version,
            ];

        } catch (\Exception $e) {
            Log::error("OS detection failed: " . $e->getMessage());
            return ['os_type' => 'Unknown', 'os_version' => 'Unknown'];
        }
    }

    /**
     * Upload a file to the server
     * 
     * @param string $localPath Local file path
     * @param string $remotePath Remote file path
     * @return bool
     */
    public function uploadFile(string $localPath, string $remotePath): bool
    {
        if (!$this->connection) {
            $this->connect();
        }

        try {
            $sftp = $this->connection->getConnection();
            return $sftp->put($remotePath, file_get_contents($localPath));
        } catch (\Exception $e) {
            Log::error("File upload failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Download a file from the server
     * 
     * @param string $remotePath Remote file path
     * @param string $localPath Local file path
     * @return bool
     */
    public function downloadFile(string $remotePath, string $localPath): bool
    {
        if (!$this->connection) {
            $this->connect();
        }

        try {
            $sftp = $this->connection->getConnection();
            $content = $sftp->get($remotePath);
            return file_put_contents($localPath, $content) !== false;
        } catch (\Exception $e) {
            Log::error("File download failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if command is whitelisted (contains safe patterns)
     * 
     * @param string $command
     * @return bool
     */
    private function isWhitelisted(string $command): bool
    {
        $whitelistedPatterns = [
            '/^git /',
            '/^composer /',
            '/^php artisan /',
            '/^npm /',
            '/^yarn /',
            '/^cd .+ && /',
        ];

        foreach ($whitelistedPatterns as $pattern) {
            if (preg_match($pattern, $command)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Disconnect from server
     */
    public function disconnect(): void
    {
        if ($this->connection) {
            $this->connection->disconnect();
            $this->connection = null;
            Log::info("SSH connection closed for server {$this->server->name}");
        }
    }

    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
