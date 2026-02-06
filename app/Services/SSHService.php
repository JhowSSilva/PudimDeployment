<?php

namespace App\Services;

use App\Models\Server;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class SSHService
{
    /**
     * Generate SSH key pair (4096-bit RSA)
     */
    public function generateKeyPair(): array
    {
        $key = RSA::createKey(4096);
        
        return [
            'private' => $key->toString('PKCS8'),
            'public' => $key->getPublicKey()->toString('OpenSSH', [
                'comment' => config('app.name') . '-worker@' . config('app.url')
            ])
        ];
    }
    
    /**
     * Connect to server via SSH
     */
    public function connect(Server $server): SSH2
    {
        $ssh = new SSH2($server->ip_address, $server->ssh_port, 10);
        
        // Get decrypted private key
        $privateKeyContent = decrypt($server->ssh_key_private);
        $privateKey = PublicKeyLoader::load($privateKeyContent);
        
        if (!$ssh->login($server->ssh_user, $privateKey)) {
            throw new \Exception('SSH authentication failed');
        }
        
        return $ssh;
    }
    
    /**
     * Test SSH connection
     */
    public function testConnection(Server $server): bool
    {
        try {
            $ssh = $this->connect($server);
            $output = $ssh->exec('echo "OK"');
            return trim($output) === 'OK';
        } catch (\Exception $e) {
            \Log::error("SSH connection test failed for server {$server->name}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a shell script on the server
     */
    public function executeScript(Server $server, string $script): array
    {
        $ssh = $this->connect($server);
        
        // Upload script to /tmp
        $scriptPath = '/tmp/provision_' . time() . '.sh';
        $ssh->exec("cat > {$scriptPath} << 'EOFSCRIPT'\n{$script}\nEOFSCRIPT");
        $ssh->exec("chmod +x {$scriptPath}");
        
        // Execute script
        $output = $ssh->exec("bash {$scriptPath} 2>&1");
        $exitCode = $ssh->getExitStatus();
        
        // Cleanup
        $ssh->exec("rm -f {$scriptPath}");
        
        return [
            'output' => $output,
            'exit_code' => $exitCode,
            'success' => $exitCode === 0
        ];
    }
    
    /**
     * Execute a single command on the server
     */
    public function executeCommand(Server $server, string $command): array
    {
        $ssh = $this->connect($server);
        
        $output = $ssh->exec($command . ' 2>&1');
        $exitCode = $ssh->getExitStatus();
        
        return [
            'output' => $output,
            'exit_code' => $exitCode,
            'success' => $exitCode === 0
        ];
    }
    
    /**
     * Get system information from server
     */
    public function getSystemInfo(Server $server): array
    {
        $ssh = $this->connect($server);
        
        return [
            'architecture' => trim($ssh->exec('uname -m')),
            'kernel' => trim($ssh->exec('uname -r')),
            'cpu_cores' => (int) trim($ssh->exec('nproc')),
            'ram_mb' => (int) trim($ssh->exec('free -m | grep Mem | awk \'{print $2}\'')),
            'disk_gb' => (int) trim($ssh->exec('df -BG / | tail -1 | awk \'{print $2}\' | sed \'s/G//\'')),
            'hostname' => trim($ssh->exec('hostname')),
            'os_release' => trim($ssh->exec('lsb_release -ds 2>/dev/null || cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2 | tr -d \'"\''))
        ];
    }
    
    /**
     * Check if server is accessible
     */
    public function ping(Server $server): bool
    {
        try {
            $ssh = $this->connect($server);
            return $ssh->isConnected();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Upload a file to the server
     */
    public function uploadFile(Server $server, string $localPath, string $remotePath): bool
    {
        try {
            $ssh = $this->connect($server);
            $content = file_get_contents($localPath);
            
            $ssh->exec("cat > {$remotePath} << 'EOFFILE'\n{$content}\nEOFFILE");
            
            return $ssh->getExitStatus() === 0;
        } catch (\Exception $e) {
            \Log::error("File upload failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Download a file from the server
     */
    public function downloadFile(Server $server, string $remotePath, string $localPath): bool
    {
        try {
            $ssh = $this->connect($server);
            $content = $ssh->exec("cat {$remotePath}");
            
            if ($ssh->getExitStatus() !== 0) {
                return false;
            }
            
            return file_put_contents($localPath, $content) !== false;
        } catch (\Exception $e) {
            \Log::error("File download failed: " . $e->getMessage());
            return false;
        }
    }
}
