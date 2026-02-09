<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;

class Server extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'ip_address',
        'ssh_port',
        'ssh_user',
        'auth_type',
        'ssh_key',
        'ssh_password',
        'os_type',
        'os_version',
        'status',
        'last_ping_at',
        'metadata',
        // AWS fields
        'aws_credential_id',
        'instance_id',
        'instance_type',
        'architecture',
        'region',
        'availability_zone',
        'ami_id',
        'key_pair_name',
        'private_key',
        'security_group_id',
        'disk_size',
        'public_ip',
        'private_ip',
        'monthly_cost',
        'stack_config',
        'provision_status',
        'provision_log',
        'provisioned_at',
        // SSH provisioning fields
        'ssh_key_private',
        'ssh_key_public',
        'deploy_user',
        'webserver',
        'php_versions',
        'database_type',
        'database_version',
        'cache_service',
        'nodejs_version',
        'installed_software',
        'provision_script',
        'provision_started_at',
        'provision_completed_at',
        'type',
        'os',
        'hostname',
        'kernel_version',
        'cpu_cores',
        'ram_mb',
        'disk_gb',
        'system_info',
        'default_key_id',
        // Multi-language support fields
        'programming_language',
        'language_version',
        'webserver_version',
        'database_version_new',
        'installed_tools',
        'process_manager',
        'size_slug',
        'ipv6_address',
    ];

    protected $casts = [
        'ssh_port' => 'integer',
        'cpu_cores' => 'integer',
        'ram_mb' => 'integer',
        'disk_gb' => 'integer',
        'last_ping_at' => 'datetime',
        'metadata' => 'array',
        'disk_size' => 'integer',
        'monthly_cost' => 'decimal:2',
        'stack_config' => 'array',
        'provisioned_at' => 'datetime',
        // SSH provisioning casts
        'php_versions' => 'array',
        'installed_software' => 'array',
        'provision_log' => 'array',
        'system_info' => 'array',
        'provision_started_at' => 'datetime',
        'provision_completed_at' => 'datetime',
        // Multi-language support casts
        'installed_tools' => 'array',
    ];

    protected $hidden = [
        'ssh_key',
        'ssh_password',
        'private_key',
        'ssh_key_private',
    ];

    // Automatically encrypt/decrypt SSH credentials
    public function setSshKeyAttribute($value): void
    {
        $this->attributes['ssh_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getSshKeyAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setSshPasswordAttribute($value): void
    {
        $this->attributes['ssh_password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getSshPasswordAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setPrivateKeyAttribute($value): void
    {
        $this->attributes['private_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getPrivateKeyAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function awsCredential(): BelongsTo
    {
        return $this->belongsTo(AWSCredential::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(ServerMetric::class);
    }

    public function databases(): HasMany
    {
        return $this->hasMany(Database::class);
    }

    public function queueWorkers(): HasMany
    {
        return $this->hasMany(QueueWorker::class);
    }

    public function defaultSSHKey(): BelongsTo
    {
        return $this->belongsTo(SSHKey::class, 'default_key_id');
    }

    // New relationships for multi-language support
    public function sshCommands(): HasMany
    {
        return $this->hasMany(ServerSSHCommand::class);
    }

    public function firewallRules(): HasMany
    {
        return $this->hasMany(ServerFirewallRule::class);
    }

    public function provisionLogs(): HasMany
    {
        return $this->hasMany(ServerProvisionLog::class);
    }

    // Helper methods
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function isProvisioned(): bool
    {
        return $this->provision_status === 'active';
    }

    public function isProvisioning(): bool
    {
        return $this->provision_status === 'provisioning';
    }

    public function isAWS(): bool
    {
        return !empty($this->instance_id);
    }

    public function isGraviton(): bool
    {
        return $this->architecture === 'arm64';
    }

    public function latestMetric(): HasOne
    {
        return $this->hasOne(ServerMetric::class)->latestOfMany();
    }

    public function getFormattedMonthlyCostAttribute(): string
    {
        return $this->monthly_cost ? '$' . number_format($this->monthly_cost, 2) . '/mês' : 'N/A';
    }

    // Multi-language helper methods
    public function isPHP(): bool
    {
        return $this->programming_language === 'php';
    }

    public function isNodeJS(): bool
    {
        return $this->programming_language === 'nodejs';
    }

    public function isPython(): bool
    {
        return $this->programming_language === 'python';
    }

    public function getLanguageDisplayName(): string
    {
        return match ($this->programming_language) {
            'php' => 'PHP',
            'nodejs' => 'Node.js',
            'python' => 'Python',
            'ruby' => 'Ruby',
            'go' => 'Go',
            'java' => 'Java',
            'dotnet' => '.NET',
            'rust' => 'Rust',
            'elixir' => 'Elixir',
            'static' => 'Static Files',
            default => ucfirst($this->programming_language ?? 'Unknown'),
        };
    }

    public function hasProcessManager(): bool
    {
        return !empty($this->process_manager);
    }

    public function getActiveFirewallRules()
    {
        return $this->firewallRules()->where('is_active', true)->get();
    }

    public function getInstalledToolsList(): array
    {
        return $this->installed_tools ?? [];
    }

    public function hasToolInstalled(string $tool): bool
    {
        return in_array($tool, $this->getInstalledToolsList());
    }
}
