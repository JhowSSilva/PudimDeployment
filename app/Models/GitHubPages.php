<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GitHubPages extends Model
{
    use HasFactory;

    protected $fillable = [
        'repository_id',
        'enabled',
        'status',
        'branch',
        'path',
        'url',
        'custom_domain',
        'https_enforced',
        'build_error',
        'last_build_at',
        'metadata',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'https_enforced' => 'boolean',
        'last_build_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(GitHubRepository::class, 'repository_id');
    }

    public function isBuilding(): bool
    {
        return $this->status === 'building';
    }

    public function isBuilt(): bool
    {
        return $this->status === 'built';
    }

    public function hasError(): bool
    {
        return $this->status === 'errored';
    }

    public function getPublicUrl(): ?string
    {
        return $this->custom_domain 
            ? 'https://' . $this->custom_domain
            : $this->url;
    }
}
