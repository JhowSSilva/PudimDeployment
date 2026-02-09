<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Log;

class FirewallService
{
    private SSHConnectionService $ssh;

    public function __construct(private Server $server)
    {
        $this->ssh = new SSHConnectionService($server);
    }

    /**
     * Configure UFW (Uncomplicated Firewall) on the server
     */
    public function configureUFW(): array
    {
        try {
            // Install UFW if not present
            $this->ssh->execute('apt-get update && apt-get install -y ufw');

            // Default policies: deny incoming, allow outgoing
            $this->ssh->execute('ufw --force default deny incoming');
            $this->ssh->execute('ufw --force default allow outgoing');

            // Essential rules
            $this->addRule(22, 'tcp', null, 'SSH'); // SSH
            $this->addRule(80, 'tcp', null, 'HTTP'); // HTTP
            $this->addRule(443, 'tcp', null, 'HTTPS'); // HTTPS

            // Enable UFW
            $result = $this->ssh->execute('ufw --force enable');

            Log::info('UFW configured successfully', ['server' => $this->server->name]);

            return [
                'success' => true,
                'message' => 'Firewall configured successfully',
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            Log::error('Failed to configure UFW', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add a firewall rule
     */
    public function addRule(int $port, string $protocol = 'tcp', ?string $source = null, ?string $comment = null): array
    {
        try {
            // Validate protocol
            if (!in_array($protocol, ['tcp', 'udp'], true)) {
                throw new \InvalidArgumentException('Protocol must be tcp or udp');
            }

            // Validate port range
            if ($port < 1 || $port > 65535) {
                throw new \InvalidArgumentException('Port must be between 1 and 65535');
            }

            $safePort = (int) $port;
            $command = "ufw allow {$safePort}/{$protocol}";
            
            if ($source) {
                // Validate source is a valid IP/CIDR
                if (!filter_var($source, FILTER_VALIDATE_IP) && !preg_match('/^\d{1,3}(\.\d{1,3}){3}\/\d{1,2}$/', $source)) {
                    throw new \InvalidArgumentException('Source must be a valid IP address or CIDR range');
                }
                $command = "ufw allow from " . escapeshellarg($source) . " to any port {$safePort} proto {$protocol}";
            }

            if ($comment) {
                // Sanitize comment — allow only alphanumeric, spaces, hyphens
                $safeComment = preg_replace('/[^a-zA-Z0-9 _-]/', '', $comment);
                $command .= " comment " . escapeshellarg($safeComment);
            }

            $result = $this->ssh->execute($command);

            Log::info('Firewall rule added', [
                'server' => $this->server->name,
                'port' => $port,
                'protocol' => $protocol,
                'source' => $source
            ]);

            return [
                'success' => true,
                'message' => "Rule added for port {$port}",
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            Log::error('Failed to add firewall rule', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Remove a firewall rule
     */
    public function removeRule(int $port, string $protocol = 'tcp'): array
    {
        try {
            $safePort = (int) $port;
            $command = "ufw delete allow {$safePort}/{$protocol}";
            $result = $this->ssh->execute($command);

            Log::info('Firewall rule removed', [
                'server' => $this->server->name,
                'port' => $port,
                'protocol' => $protocol
            ]);

            return [
                'success' => true,
                'message' => "Rule removed for port {$port}",
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            Log::error('Failed to remove firewall rule', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Block an IP address
     */
    public function blockIP(string $ip, ?string $comment = null): array
    {
        try {
            $command = "ufw deny from {$ip}";
            
            if ($comment) {
                $command .= " comment '{$comment}'";
            }

            $result = $this->ssh->execute($command);

            Log::warning('IP address blocked', [
                'server' => $this->server->name,
                'ip' => $ip,
                'comment' => $comment
            ]);

            return [
                'success' => true,
                'message' => "IP {$ip} blocked successfully",
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            Log::error('Failed to block IP', [
                'server' => $this->server->name,
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Unblock an IP address
     */
    public function unblockIP(string $ip): array
    {
        try {
            $command = "ufw delete deny from {$ip}";
            $result = $this->ssh->execute($command);

            Log::info('IP address unblocked', [
                'server' => $this->server->name,
                'ip' => $ip
            ]);

            return [
                'success' => true,
                'message' => "IP {$ip} unblocked successfully",
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            Log::error('Failed to unblock IP', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get list of active firewall rules
     */
    public function getActivePorts(): array
    {
        try {
            $result = $this->ssh->execute('ufw status numbered');
            
            return [
                'success' => true,
                'rules' => $this->parseFirewallRules($result['output'])
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get firewall status', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'rules' => []
            ];
        }
    }

    /**
     * Parse firewall rules from UFW output
     */
    private function parseFirewallRules(string $output): array
    {
        $rules = [];
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            // Parse UFW numbered status output
            if (preg_match('/\[(\d+)\]\s+(.+?)\s+(ALLOW|DENY|REJECT)\s+(.+)/', $line, $matches)) {
                $rules[] = [
                    'number' => $matches[1],
                    'target' => trim($matches[2]),
                    'action' => $matches[3],
                    'source' => trim($matches[4])
                ];
            }
        }

        return $rules;
    }

    /**
     * Enable fail2ban for additional security
     */
    public function enableFail2Ban(): array
    {
        try {
            // Install fail2ban
            $this->ssh->execute('apt-get update && apt-get install -y fail2ban');

            // Configure fail2ban for common services
            $jailConfig = <<<'CONFIG'
[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 5
bantime = 3600

[nginx-http-auth]
enabled = true
port = http,https
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 3
bantime = 3600

[nginx-noscript]
enabled = true
port = http,https
filter = nginx-noscript
logpath = /var/log/nginx/access.log
maxretry = 6
bantime = 600
CONFIG;

            // Write jail.local config
            $this->ssh->execute("cat > /etc/fail2ban/jail.local << 'EOF'\n{$jailConfig}\nEOF");

            // Start fail2ban
            $this->ssh->execute('systemctl enable fail2ban');
            $this->ssh->execute('systemctl start fail2ban');

            Log::info('Fail2ban enabled successfully', ['server' => $this->server->name]);

            return [
                'success' => true,
                'message' => 'Fail2ban enabled and configured successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to enable fail2ban', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get fail2ban banned IPs
     */
    public function getBannedIPs(): array
    {
        try {
            $result = $this->ssh->execute('fail2ban-client status sshd');
            
            // Parse banned IPs from output
            $bannedIPs = [];
            if (preg_match('/Banned IP list:\s+(.+)/', $result['output'], $matches)) {
                $bannedIPs = array_filter(explode(' ', trim($matches[1])));
            }

            return [
                'success' => true,
                'banned_ips' => $bannedIPs
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'banned_ips' => []
            ];
        }
    }

    /**
     * Disable the firewall (use with caution!)
     */
    public function disableFirewall(): array
    {
        try {
            $result = $this->ssh->execute('ufw disable');

            Log::warning('Firewall disabled', ['server' => $this->server->name]);

            return [
                'success' => true,
                'message' => 'Firewall disabled',
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            Log::error('Failed to disable firewall', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get firewall status
     */
    public function getStatus(): array
    {
        try {
            $result = $this->ssh->execute('ufw status verbose');

            return [
                'success' => true,
                'status' => $result['output']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status' => 'unknown'
            ];
        }
    }
}
