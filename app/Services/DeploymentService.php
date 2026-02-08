<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Deployment;
use App\Models\User;
use App\Traits\StructuredLogging;
use Illuminate\Support\Facades\Log;

class DeploymentService
{
    use StructuredLogging;
    
    private Site $site;
    private SSHConnectionService $ssh;
    private Deployment $deployment;

    public function __construct(Site $site)
    {
        $this->site = $site;
        $this->ssh = new SSHConnectionService($site->server);
    }

    /**
     * Execute full deployment process
     * 
     * @param User $user User who triggered the deployment
     * @param string $trigger manual|webhook|scheduled
     * @return Deployment
     */
    public function deploy(User $user, string $trigger = 'manual'): Deployment
    {
        // Create deployment record
        $this->deployment = Deployment::create([
            'site_id' => $this->site->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'trigger' => $trigger,
        ]);

        try {
            $this->deployment->markAsStarted();
            $this->deployment->appendLog("Deployment started by {$user->name}");

            // Connect to server
            $this->deployment->appendLog("Connecting to server {$this->site->server->name}...");
            $this->ssh->connect();
            $this->deployment->appendLog("Connected successfully");

            // Navigate to site directory
            $sitePath = $this->site->full_path;
            $this->deployment->appendLog("Navigating to {$sitePath}");

            // Pull latest code from Git
            if ($this->site->git_repository) {
                $this->pullFromGit($sitePath);
            }

            // Execute custom deploy script or default Laravel deployment
            $this->executeDeployScript($sitePath);

            // Get latest commit info
            $this->getCommitInfo($sitePath);

            $this->deployment->appendLog("Deployment completed successfully!");
            $this->deployment->markAsSuccessful();

            // Update site status
            $this->site->update(['status' => 'active']);

        } catch (\Exception $e) {
            $this->deployment->appendLog("ERROR: " . $e->getMessage());
            $this->deployment->markAsFailed();
            
            $this->site->update(['status' => 'error']);

            $this->logError("Deployment failed", [
                'deployment_id' => $this->deployment->id,
                'site_id' => $this->site->id,
                'site_domain' => $this->site->domain,
                'server_id' => $this->site->server_id,
                'trigger' => $trigger,
            ], $e);
        } finally {
            $this->ssh->disconnect();
        }

        return $this->deployment;
    }

    /**
     * Pull latest code from Git repository
     * 
     * @param string $sitePath
     */
    private function pullFromGit(string $sitePath): void
    {
        $this->deployment->appendLog("Pulling from Git repository: {$this->site->git_repository}");
        $this->deployment->appendLog("Branch: {$this->site->git_branch}");

        // Check if .git exists
        $result = $this->ssh->execute("cd {$sitePath} && [ -d .git ] && echo 'exists' || echo 'missing'");
        $gitExists = trim($result['output']) === 'exists';

        if (!$gitExists) {
            // Clone repository
            $this->deployment->appendLog("Repository not found, cloning...");
            
            $cloneUrl = $this->getAuthenticatedGitUrl();
            $result = $this->ssh->execute("git clone -b {$this->site->git_branch} {$cloneUrl} {$sitePath}");
            
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Git clone failed: " . $result['output']);
            }
            
            $this->deployment->appendLog("Repository cloned successfully");
        } else {
            // Pull latest changes
            $commands = [
                "cd {$sitePath}",
                "git fetch origin",
                "git reset --hard origin/{$this->site->git_branch}",
                "git pull origin {$this->site->git_branch}",
            ];

            $result = $this->ssh->execute(implode(' && ', $commands));
            
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Git pull failed: " . $result['output']);
            }
            
            $this->deployment->appendLog($result['output']);
        }
    }

    /**
     * Execute deployment script
     * 
     * @param string $sitePath
     */
    private function executeDeployScript(string $sitePath): void
    {
        $script = $this->site->deploy_script ?: $this->getDefaultLaravelDeployScript();
        
        $this->deployment->appendLog("Executing deployment script...");
        $this->deployment->appendLog("---");

        // Split script into individual commands
        $commands = array_filter(explode("\n", $script));

        foreach ($commands as $command) {
            $command = trim($command);
            
            // Skip comments and empty lines
            if (empty($command) || str_starts_with($command, '#')) {
                continue;
            }

            $this->deployment->appendLog("> {$command}");
            
            $result = $this->ssh->execute("cd {$sitePath} && {$command}");
            
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Command failed: {$command}\nOutput: " . $result['output']);
            }
            
            $this->deployment->appendLog($result['output']);
        }

        $this->deployment->appendLog("---");
        $this->deployment->appendLog("Deployment script executed successfully");
    }

    /**
     * Get commit information
     * 
     * @param string $sitePath
     */
    private function getCommitInfo(string $sitePath): void
    {
        try {
            // Get latest commit hash
            $hashResult = $this->ssh->execute("cd {$sitePath} && git rev-parse --short HEAD");
            $commitHash = trim($hashResult['output']);

            // Get commit message
            $messageResult = $this->ssh->execute("cd {$sitePath} && git log -1 --pretty=%B");
            $commitMessage = trim($messageResult['output']);

            $this->deployment->update([
                'commit_hash' => $commitHash,
                'commit_message' => $commitMessage,
            ]);

            $this->deployment->appendLog("Deployed commit: {$commitHash}");
            $this->deployment->appendLog("Message: {$commitMessage}");

        } catch (\Exception $e) {
            Log::warning("Failed to get commit info: " . $e->getMessage());
        }
    }

    /**
     * Rollback to previous deployment
     * 
     * @param User $user
     * @return Deployment
     */
    public function rollback(User $user): Deployment
    {
        // Get previous successful deployment
        $previousDeployment = $this->site->successfulDeployments()
            ->where('id', '<', $this->deployment->id ?? 0)
            ->latest()
            ->first();

        if (!$previousDeployment || !$previousDeployment->commit_hash) {
            throw new \Exception("No previous deployment found to rollback to");
        }

        $this->deployment = Deployment::create([
            'site_id' => $this->site->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'trigger' => 'manual',
        ]);

        try {
            $this->deployment->markAsStarted();
            $this->deployment->appendLog("Rolling back to commit: {$previousDeployment->commit_hash}");

            $this->ssh->connect();

            $sitePath = $this->site->full_path;

            // Reset to previous commit
            $result = $this->ssh->execute("cd {$sitePath} && git reset --hard {$previousDeployment->commit_hash}");
            
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Git reset failed: " . $result['output']);
            }

            $this->deployment->appendLog($result['output']);

            // Run deployment script
            $this->executeDeployScript($sitePath);

            $this->deployment->update([
                'commit_hash' => $previousDeployment->commit_hash,
                'commit_message' => "Rollback to: {$previousDeployment->commit_message}",
            ]);

            $this->deployment->appendLog("Rollback completed successfully");
            $this->deployment->markAsSuccessful();

        } catch (\Exception $e) {
            $this->deployment->appendLog("Rollback failed: " . $e->getMessage());
            $this->deployment->markAsFailed();
            throw $e;
        } finally {
            $this->ssh->disconnect();
        }

        return $this->deployment;
    }

    /**
     * Get authenticated Git URL with token
     * 
     * @return string
     */
    private function getAuthenticatedGitUrl(): string
    {
        $url = $this->site->git_repository;

        if ($this->site->git_token) {
            // GitHub/GitLab: https://token@github.com/user/repo.git
            if (str_contains($url, 'github.com')) {
                $url = str_replace('https://', "https://{$this->site->git_token}@", $url);
            } elseif (str_contains($url, 'gitlab.com')) {
                $url = str_replace('https://', "https://oauth2:{$this->site->git_token}@", $url);
            }
        }

        return $url;
    }

    /**
     * Get default Laravel deployment script
     * 
     * @return string
     */
    private function getDefaultLaravelDeployScript(): string
    {
        return <<<'SCRIPT'
# Install/Update Composer dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Run database migrations
php artisan migrate --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Restart PHP-FPM (if needed)
# sudo systemctl restart php8.3-fpm

# Clear application cache
php artisan cache:clear
php artisan queue:restart
SCRIPT;
    }

    /**
     * Test deployment (dry run)
     * 
     * @return array
     */
    public function testDeployment(): array
    {
        try {
            $this->ssh->connect();

            $tests = [
                'ssh_connection' => true,
                'git_repository' => false,
                'composer_installed' => false,
                'php_version' => null,
            ];

            // Test Git repository access
            if ($this->site->git_repository) {
                $result = $this->ssh->execute("git ls-remote {$this->getAuthenticatedGitUrl()} HEAD");
                $tests['git_repository'] = $result['exit_code'] === 0;
            }

            // Check Composer
            $result = $this->ssh->execute("which composer");
            $tests['composer_installed'] = $result['exit_code'] === 0;

            // Check PHP version
            $result = $this->ssh->execute("php -v | head -n 1");
            if ($result['exit_code'] === 0) {
                $tests['php_version'] = trim($result['output']);
            }

            return $tests;

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        } finally {
            $this->ssh->disconnect();
        }
    }
}
