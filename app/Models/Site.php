<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;

class Site extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'server_id',
        'name',
        'domain',
        'domain_aliases',
        'document_root',
        'root_directory',
        'php_version',
        'git_repository',
        'git_branch',
        'git_token',
        'deploy_script',
        'webhook_url',
        'webhook_secret',
        'auto_deploy_enabled',
        'last_webhook_at',
        'webhook_provider',
        'env_variables',
        'nginx_config_path',
        'is_active',
        'status',
        'cloudflare_account_id',
        'cloudflare_zone_id',
        'cloudflare_record_id',
        'cloudflare_proxy',
        'auto_dns',
        'ssl_type',
        'ssl_enabled',
        'force_https',
        'ssl_expires_at',
        'ssl_last_check',
        'ssl_certificate',
        'ssl_private_key',
        'ssl_ca_bundle',
        // New fields for expanded functionality
        'application_type',
        'custom_type',
        'dedicated_php_pool',
        'php_memory_limit',
        'php_upload_max_filesize',
        'php_post_max_size',
        'php_max_execution_time',
        'node_version',
        'package_manager',
        'node_port',
        'node_start_command',
        'process_manager',
        'auto_create_database',
        'linked_database_id',
        'web_server',
        'nginx_template',
        'auto_ssl',
        'git_provider',
        'auto_deploy',
        'has_staging',
        'daily_backup',
        'cdn_enabled',
        'cdn_provider',
        'firewall_rules',
        'last_deployed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cloudflare_proxy' => 'boolean',
        'auto_dns' => 'boolean',
        'ssl_enabled' => 'boolean',
        'force_https' => 'boolean',
        'auto_deploy_enabled' => 'boolean',
        'ssl_expires_at' => 'datetime',
        'ssl_last_check' => 'datetime',
        'last_webhook_at' => 'datetime',
        'domain_aliases' => 'array',
        // New field casts
        'dedicated_php_pool' => 'boolean',
        'php_max_execution_time' => 'integer',
        'node_port' => 'integer',
        'auto_create_database' => 'boolean',
        'auto_ssl' => 'boolean',
        'auto_deploy' => 'boolean',
        'has_staging' => 'boolean',
        'daily_backup' => 'boolean',
        'cdn_enabled' => 'boolean',
        'firewall_rules' => 'array',
        'last_deployed_at' => 'datetime',
    ];

    protected $hidden = [
        'git_token',
        'webhook_secret',
        'env_variables',
        'ssl_private_key',
        'ssl_certificate',
        'ssl_ca_bundle',
    ];

    // Automatically encrypt/decrypt sensitive data
    public function setGitTokenAttribute($value): void
    {
        $this->attributes['git_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getGitTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setEnvVariablesAttribute($value): void
    {
        $this->attributes['env_variables'] = $value ? Crypt::encryptString(json_encode($value)) : null;
    }

    public function getEnvVariablesAttribute($value): ?array
    {
        return $value ? json_decode(Crypt::decryptString($value), true) : null;
    }

    // Relationships
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function cloudflareAccount(): BelongsTo
    {
        return $this->belongsTo(CloudflareAccount::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function sslCertificates(): HasMany
    {
        return $this->hasMany(SSLCertificate::class);
    }

    public function linkedDatabase(): BelongsTo
    {
        return $this->belongsTo(Database::class, 'linked_database_id');
    }

    public function dockerContainers(): HasMany
    {
        return $this->hasMany(DockerContainer::class);
    }

    public function activeSslCertificate(): HasOne
    {
        return $this->hasOne(SSLCertificate::class)->ofMany(
            ['id' => 'max'],
            function ($query) {
                $query->where('status', 'active');
            }
        );
    }

    // Helper methods
    public function latestDeployment(): HasOne
    {
        return $this->hasOne(Deployment::class)->latestOfMany();
    }

    public function successfulDeployments(): HasMany
    {
        return $this->deployments()->where('status', 'success');
    }

    public function getFullPathAttribute(): string
    {
        return "/var/www/{$this->domain}";
    }
}
