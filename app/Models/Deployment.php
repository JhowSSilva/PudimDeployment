<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deployment extends Model
{
    protected $fillable = [
        'site_id',
        'user_id',
        'commit_hash',
        'commit_message',
        'status',
        'trigger',
        'output_log',
        'started_at',
        'finished_at',
        'duration_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function appendLog(string $message): void
    {
        $this->output_log .= "[" . now()->format('Y-m-d H:i:s') . "] " . $message . "\n";
        $this->save();
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markAsSuccessful(): void
    {
        $this->update([
            'status' => 'success',
            'finished_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
        ]);
    }

    public function markAsFailed(string $error = null): void
    {
        if ($error) {
            $this->appendLog("ERROR: " . $error);
        }
        
        $this->update([
            'status' => 'failed',
            'finished_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
        ]);
    }
}
