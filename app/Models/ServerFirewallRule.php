<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerFirewallRule extends Model
{
    protected $fillable = [
        'server_id',
        'name',
        'port',
        'protocol',
        'source',
        'action',
        'is_active',
        'description',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_active' => 'boolean',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPort($query, int $port)
    {
        return $query->where('port', $port);
    }

    public function isAllowRule(): bool
    {
        return $this->action === 'allow';
    }

    public function isDenyRule(): bool
    {
        return $this->action === 'deny';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: "{$this->action} {$this->port}/{$this->protocol}";
    }

    public function getFormattedRuleAttribute(): string
    {
        return sprintf(
            '%s %d/%s from %s (%s)',
            ucfirst($this->action),
            $this->port,
            strtoupper($this->protocol),
            $this->source,
            $this->is_active ? 'Active' : 'Inactive'
        );
    }
}