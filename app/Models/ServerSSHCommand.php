<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerSSHCommand extends Model
{
    protected $fillable = [
        'server_id',
        'user_id',
        'command',
        'output',
        'exit_code',
        'executed_at',
    ];

    protected $casts = [
        'exit_code' => 'integer',
        'executed_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wasSuccessful(): bool
    {
        return $this->exit_code === 0;
    }

    public function getDurationAttribute(): ?int
    {
        // If we add timing in future versions
        return null;
    }
}