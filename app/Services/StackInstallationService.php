<?php

namespace App\Services;

use App\Contracts\StackInstallerInterface;
use App\Models\Server;
use App\Models\ServerInstallationTemplate;
use App\Services\Installers\PHPStackInstaller;
use App\Services\Installers\NodeStackInstaller;
use App\Services\Installers\PythonStackInstaller;
use Illuminate\Support\Facades\Log;

class StackInstallationService
{
    protected array $installers = [];
    
    public function __construct()
    {
        $this->registerDefaultInstallers();
    }
    
    /**
     * Register all default installers
     */
    protected function registerDefaultInstallers(): void
    {
        $this->registerInstaller('php', app(PHPStackInstaller::class));
        $this->registerInstaller('nodejs', app(NodeStackInstaller::class));
        $this->registerInstaller('python', app(PythonStackInstaller::class));
    }
    
    /**
     * Register a new installer
     */
    public function registerInstaller(string $language, StackInstallerInterface $installer): void
    {
        $this->installers[strtolower($language)] = $installer;
    }
    
    /**
     * Get installer for specific language
     */
    public function getInstaller(string $language): ?StackInstallerInterface
    {
        return $this->installers[strtolower($language)] ?? null;
    }
    
    /**
     * Check if installer exists for language
     */
    public function hasInstaller(string $language): bool
    {
        return isset($this->installers[strtolower($language)]);
    }
    
    /**
     * Install stack on server
     */
    public function install(Server $server, string $language, array $config = []): bool
    {
        $installer = $this->getInstaller($language);
        
        if (!$installer) {
            throw new \InvalidArgumentException("No installer found for language: {$language}");
        }
        
        Log::info("Installing {$language} stack on server {$server->id}", [
            'server_name' => $server->name,
            'language' => $language,
            'config' => $config,
        ]);
        
        try {
            return $installer->install($server, $config);
        } catch (\Exception $e) {
            Log::error("Failed to install {$language} on server {$server->id}: {$e->getMessage()}");
            throw $e;
        }
    }
    
    /**
     * Validate installation on server
     */
    public function validate(Server $server): bool
    {
        if (!$server->programming_language) {
            return false;
        }
        
        $installer = $this->getInstaller($server->programming_language);
        
        if (!$installer) {
            Log::warning("No installer found for validation: {$server->programming_language}");
            return false;
        }
        
        try {
            return $installer->validate($server);
        } catch (\Exception $e) {
            Log::error("Validation failed for server {$server->id}: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Get all available languages with installers
     */
    public function getAvailableLanguages(): array
    {
        $languages = [];
        
        foreach ($this->installers as $language => $installer) {
            $languages[$language] = [
                'name' => $installer->getName(),
                'versions' => $installer->getSupportedVersions(),
                'default_config' => $installer->getDefaultConfig(),
                'required_packages' => $installer->getRequiredPackages(),
            ];
        }
        
        return $languages;
    }
    
    /**
     * Get supported versions for specific language
     */
    public function getSupportedVersions(string $language): array
    {
        $installer = $this->getInstaller($language);
        return $installer ? $installer->getSupportedVersions() : [];
    }
    
    /**
     * Get default configuration for language
     */
    public function getDefaultConfig(string $language): array
    {
        $installer = $this->getInstaller($language);
        return $installer ? $installer->getDefaultConfig() : [];
    }
    
    /**
     * Generate installation script for language and config
     */
    public function generateInstallScript(Server $server, string $language, array $config = []): string
    {
        $installer = $this->getInstaller($language);
        
        if (!$installer) {
            throw new \InvalidArgumentException("No installer found for language: {$language}");
        }
        
        return $installer->generateInstallScript($server, $config);
    }
    
    /**
     * Get installation template for language and version
     */
    public function getInstallationTemplate(string $language, string $version): ?ServerInstallationTemplate
    {
        return ServerInstallationTemplate::active()
            ->where('language', $language)
            ->where('version', $version)
            ->first();
    }
    
    /**
     * Create server with stack configuration
     */
    public function createServerWithStack(array $serverData, string $language, array $stackConfig = []): Server
    {
        // Validate language
        if (!$this->hasInstaller($language)) {
            throw new \InvalidArgumentException("Unsupported language: {$language}");
        }
        
        // Get default configuration
        $defaultConfig = $this->getDefaultConfig($language);
        $config = array_merge($defaultConfig, $stackConfig);
        
        // Create server with programming language and stack config
        $serverData['programming_language'] = $language;
        $serverData['language_version'] = $config['version'] ?? $this->getLatestVersion($language);
        $serverData['stack_config'] = $config;
        
        return Server::create($serverData);
    }
    
    /**
     * Get latest supported version for language
     */
    public function getLatestVersion(string $language): ?string
    {
        $versions = $this->getSupportedVersions($language);
        
        if (empty($versions)) {
            return null;
        }
        
        // Sort versions in descending order and return the first one
        usort($versions, fn($a, $b) => version_compare($b, $a));
        
        return $versions[0];
    }
    
    /**
     * Estimate installation time for language and config
     */
    public function estimateInstallationTime(string $language, array $config = []): int
    {
        $baseTime = 3; // Base time in minutes
        
        // Language-specific time estimates
        $languageTimes = [
            'php' => 5,
            'nodejs' => 4,
            'python' => 6,
            'ruby' => 5,
            'go' => 3,
            'java' => 8,
            'dotnet' => 7,
            'rust' => 10,
            'elixir' => 6,
        ];
        
        $baseTime = $languageTimes[$language] ?? $baseTime;
        
        // Add time based on additional tools
        $additionalTime = 0;
        
        if (isset($config['install_composer']) && $config['install_composer']) {
            $additionalTime += 1;
        }
        
        if (isset($config['install_yarn']) && $config['install_yarn']) {
            $additionalTime += 1;
        }
        
        if (isset($config['install_pm2']) && $config['install_pm2']) {
            $additionalTime += 1;
        }
        
        if (isset($config['install_poetry']) && $config['install_poetry']) {
            $additionalTime += 2;
        }
        
        if (isset($config['global_packages'])) {
            $additionalTime += count($config['global_packages']) * 0.5;
        }
        
        return (int) ceil($baseTime + $additionalTime);
    }
    
    /**
     * Get installation progress for server
     */
    public function getInstallationProgress(Server $server): array
    {
        $logs = $server->provisionLogs()->orderBy('created_at')->get();
        
        $progress = [
            'total_steps' => 0,
            'completed_steps' => 0,
            'current_step' => null,
            'status' => 'pending',
            'steps' => [],
        ];
        
        foreach ($logs as $log) {
            $progress['steps'][] = [
                'name' => $log->step,
                'status' => $log->status,
                'started_at' => $log->started_at,
                'completed_at' => $log->completed_at,
                'duration' => $log->formatted_duration,
                'error' => $log->error_message,
            ];
            
            $progress['total_steps']++;
            
            if ($log->status === 'completed') {
                $progress['completed_steps']++;
            } elseif ($log->status === 'running') {
                $progress['current_step'] = $log->step;
                $progress['status'] = 'running';
            } elseif ($log->status === 'failed') {
                $progress['status'] = 'failed';
                $progress['current_step'] = $log->step;
                break;
            }
        }
        
        if ($progress['completed_steps'] === $progress['total_steps'] && $progress['total_steps'] > 0) {
            $progress['status'] = 'completed';
        }
        
        $progress['percentage'] = $progress['total_steps'] > 0 
            ? round(($progress['completed_steps'] / $progress['total_steps']) * 100, 2)
            : 0;
        
        return $progress;
    }
    
    /**
     * Get installer statistics
     */
    public function getInstallerStats(): array
    {
        $stats = [];
        
        foreach ($this->installers as $language => $installer) {
            $servers = Server::where('programming_language', $language)->count();
            $activeServers = Server::where('programming_language', $language)
                ->where('status', 'active')
                ->count();
            
            $stats[$language] = [
                'name' => $installer->getName(),
                'total_servers' => $servers,
                'active_servers' => $activeServers,
                'success_rate' => $servers > 0 ? round(($activeServers / $servers) * 100, 2) : 0,
                'versions' => $installer->getSupportedVersions(),
            ];
        }
        
        return $stats;
    }
}