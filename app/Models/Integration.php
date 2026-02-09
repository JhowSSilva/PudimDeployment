<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Integration extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'provider',
        'status',
        'config',
        'events',
        'webhook_url',
        'webhook_secret',
        'last_triggered_at',
        'trigger_count',
        'last_error',
    ];

    protected $casts = [
        'config' => 'array',
        'events' => 'array',
        'last_triggered_at' => 'datetime',
        'trigger_count' => 'integer',
    ];

    protected $hidden = [
        'webhook_secret',
        'config',
    ];

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // Business Logic
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isGitHub(): bool
    {
        return $this->provider === 'github';
    }

    public function isGitLab(): bool
    {
        return $this->provider === 'gitlab';
    }

    public function isBitbucket(): bool
    {
        return $this->provider === 'bitbucket';
    }

    public function isSlack(): bool
    {
        return $this->provider === 'slack';
    }

    public function isDiscord(): bool
    {
        return $this->provider === 'discord';
    }

    public function isTelegram(): bool
    {
        return $this->provider === 'telegram';
    }

    public function isWebhook(): bool
    {
        return $this->provider === 'webhook';
    }

    public function activate(): void
    {
        $this->update(['status' => 'active', 'last_error' => null]);
    }

    public function deactivate(): void
    {
        $this->update(['status' => 'inactive']);
    }

    public function markError(string $error): void
    {
        $this->update([
            'status' => 'error',
            'last_error' => $error,
        ]);

        Log::error("Integration Error: {$this->name}", ['error' => $error]);
    }

    public function shouldTriggerForEvent(string $eventType): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $events = $this->events ?? [];
        return in_array($eventType, $events) || in_array('all', $events);
    }

    public function trigger(string $eventType, array $data): bool
    {
        if (!$this->shouldTriggerForEvent($eventType)) {
            return false;
        }

        try {
            $result = $this->sendNotification($eventType, $data);
            
            $this->increment('trigger_count');
            $this->update(['last_triggered_at' => now()]);
            
            return $result;
        } catch (\Exception $e) {
            $this->markError($e->getMessage());
            return false;
        }
    }

    protected function sendNotification(string $eventType, array $data): bool
    {
        return match($this->provider) {
            'slack' => $this->sendSlackNotification($eventType, $data),
            'discord' => $this->sendDiscordNotification($eventType, $data),
            'telegram' => $this->sendTelegramNotification($eventType, $data),
            'webhook' => $this->sendWebhookNotification($eventType, $data),
            default => true, // Git providers don't send notifications
        };
    }

    protected function sendSlackNotification(string $eventType, array $data): bool
    {
        $webhookUrl = $this->config['webhook_url'] ?? null;
        if (!$webhookUrl) {
            return false;
        }

        $message = $this->formatMessage($eventType, $data);
        
        $response = Http::post($webhookUrl, [
            'text' => $message['title'],
            'attachments' => [[
                'color' => $message['color'],
                'text' => $message['body'],
                'footer' => 'Pudim Deployment',
                'ts' => time(),
            ]],
        ]);

        return $response->successful();
    }

    protected function sendDiscordNotification(string $eventType, array $data): bool
    {
        $webhookUrl = $this->config['webhook_url'] ?? null;
        if (!$webhookUrl) {
            return false;
        }

        $message = $this->formatMessage($eventType, $data);
        
        $response = Http::post($webhookUrl, [
            'embeds' => [[
                'title' => $message['title'],
                'description' => $message['body'],
                'color' => $this->getDiscordColor($message['color']),
                'timestamp' => now()->toIso8601String(),
            ]],
        ]);

        return $response->successful();
    }

    protected function sendTelegramNotification(string $eventType, array $data): bool
    {
        $botToken = $this->config['bot_token'] ?? null;
        $chatId = $this->config['chat_id'] ?? null;
        
        if (!$botToken || !$chatId) {
            return false;
        }

        $message = $this->formatMessage($eventType, $data);
        $text = "*{$message['title']}*\n\n{$message['body']}";
        
        $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);

        return $response->successful();
    }

    protected function sendWebhookNotification(string $eventType, array $data): bool
    {
        if (!$this->webhook_url) {
            return false;
        }

        $payload = [
            'event' => $eventType,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($this->webhook_secret) {
            $signature = hash_hmac('sha256', json_encode($payload), $this->webhook_secret);
            $headers = ['X-Webhook-Signature' => $signature];
        }

        $response = Http::withHeaders($headers ?? [])->post($this->webhook_url, $payload);

        return $response->successful();
    }

    protected function formatMessage(string $eventType, array $data): array
    {
        return match($eventType) {
            'deployment_started' => [
                'title' => '🚀 Deployment Started',
                'body' => "Site: {$data['site_name']}\nCommit: {$data['commit_hash']}",
                'color' => 'info',
            ],
            'deployment_success' => [
                'title' => '✅ Deployment Successful',
                'body' => "Site: {$data['site_name']}\nDuration: {$data['duration']}",
                'color' => 'good',
            ],
            'deployment_failed' => [
                'title' => '❌ Deployment Failed',
                'body' => "Site: {$data['site_name']}\nError: {$data['error']}",
                'color' => 'danger',
            ],
            'pipeline_started' => [
                'title' => '⚙️ Pipeline Started',
                'body' => "Pipeline: {$data['pipeline_name']}\nBranch: {$data['branch']}",
                'color' => 'info',
            ],
            'pipeline_success' => [
                'title' => '✅ Pipeline Completed',
                'body' => "Pipeline: {$data['pipeline_name']}\nDuration: {$data['duration']}",
                'color' => 'good',
            ],
            'pipeline_failed' => [
                'title' => '❌ Pipeline Failed',
                'body' => "Pipeline: {$data['pipeline_name']}\nStage: {$data['failed_stage']}",
                'color' => 'danger',
            ],
            default => [
                'title' => '📢 '. ucfirst(str_replace('_', ' ', $eventType)),
                'body' => json_encode($data, JSON_PRETTY_PRINT),
                'color' => 'info',
            ],
        };
    }

    protected function getDiscordColor(string $color): int
    {
        return match($color) {
            'good' => 3066993,   // green
            'danger' => 15158332, // red
            'warning' => 16776960, // yellow
            default => 3447003,   // blue
        };
    }

    public function getAccessToken(): ?string
    {
        return $this->config['access_token'] ?? $this->config['token'] ?? null;
    }

    public function getRepositoryUrl(): ?string
    {
        return $this->config['repository_url'] ?? null;
    }
}
