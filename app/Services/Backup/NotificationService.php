<?php

namespace App\Services\Backup;

use App\Events\BackupCompleted;
use App\Events\BackupFailed;
use App\Models\BackupNotificationSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send notifications for backup completion
     */
    public function sendBackupCompleted(BackupCompleted $event): void
    {
        $settings = $event->configuration->notificationSettings;

        if (!$settings) {
            return;
        }

        // Email notification
        if ($settings->email_on_success && $settings->hasEmailNotifications()) {
            $this->sendEmail($settings, $event->configuration, 'success', [
                'file_size' => $event->file->formatted_size,
                'duration' => $event->job->formatted_duration,
            ]);
        }

        // Webhook notification
        if ($settings->hasWebhook()) {
            $this->sendWebhook($settings->webhook_url, 'backup.completed', [
                'backup_name' => $event->configuration->name,
                'database' => $event->configuration->database->name,
                'file_size' => $event->file->file_size,
                'duration' => $event->job->duration,
                'timestamp' => now()->toIso8601String(),
            ], $settings->webhook_headers);
        }

        // Slack notification
        if ($settings->hasSlack()) {
            $this->sendSlack($settings->slack_webhook, 'success', [
                'backup_name' => $event->configuration->name,
                'database' => $event->configuration->database->name,
                'file_size' => $event->file->formatted_size,
            ]);
        }

        // Discord notification
        if ($settings->hasDiscord()) {
            $this->sendDiscord($settings->discord_webhook, 'success', [
                'backup_name' => $event->configuration->name,
                'database' => $event->configuration->database->name,
                'file_size' => $event->file->formatted_size,
            ]);
        }
    }

    /**
     * Send notifications for backup failure
     */
    public function sendBackupFailed(BackupFailed $event): void
    {
        $settings = $event->configuration->notificationSettings;

        if (!$settings) {
            return;
        }

        // Email notification
        if ($settings->email_on_failure && $settings->hasEmailNotifications()) {
            $this->sendEmail($settings, $event->configuration, 'failure', [
                'error' => $event->exception->getMessage(),
            ]);
        }

        // Webhook notification
        if ($settings->hasWebhook()) {
            $this->sendWebhook($settings->webhook_url, 'backup.failed', [
                'backup_name' => $event->configuration->name,
                'database' => $event->configuration->database->name,
                'error' => $event->exception->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], $settings->webhook_headers);
        }

        // Slack notification
        if ($settings->hasSlack()) {
            $this->sendSlack($settings->slack_webhook, 'failure', [
                'backup_name' => $event->configuration->name,
                'error' => $event->exception->getMessage(),
            ]);
        }

        // Discord notification
        if ($settings->hasDiscord()) {
            $this->sendDiscord($settings->discord_webhook, 'failure', [
                'backup_name' => $event->configuration->name,
                'error' => $event->exception->getMessage(),
            ]);
        }
    }

    /**
     * Send email notification
     */
    private function sendEmail(BackupNotificationSetting $settings, $configuration, string $status, array $data): void
    {
        try {
            $subject = sprintf(
                '[%s] Backup %s: %s',
                config('app.name'),
                $status === 'success' ? 'Completed' : 'Failed',
                $configuration->name
            );

            foreach ($settings->email_recipients as $email) {
                Mail::raw(
                    $this->formatEmailBody($configuration, $status, $data),
                    function ($message) use ($email, $subject) {
                        $message->to($email)->subject($subject);
                    }
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send webhook notification
     */
    private function sendWebhook(string $url, string $event, array $data, ?array $headers = null): void
    {
        try {
            $request = Http::timeout(10);

            if ($headers) {
                $request->withHeaders($headers);
            }

            $request->post($url, [
                'event' => $event,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send webhook notification', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send Slack notification
     */
    private function sendSlack(string $webhook, string $status, array $data): void
    {
        try {
            $color = $status === 'success' ? 'good' : 'danger';
            $icon = $status === 'success' ? ':white_check_mark:' : ':x:';

            Http::post($webhook, [
                'attachments' => [
                    [
                        'color' => $color,
                        'title' => $icon . ' Backup ' . ucfirst($status),
                        'fields' => [
                            [
                                'title' => 'Backup Name',
                                'value' => $data['backup_name'],
                                'short' => true,
                            ],
                            [
                                'title' => 'Database',
                                'value' => $data['database'] ?? '-',
                                'short' => true,
                            ],
                            [
                                'title' => $status === 'success' ? 'File Size' : 'Error',
                                'value' => $data['file_size'] ?? $data['error'] ?? '-',
                                'short' => false,
                            ],
                        ],
                        'footer' => config('app.name'),
                        'ts' => time(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send Slack notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send Discord notification
     */
    private function sendDiscord(string $webhook, string $status, array $data): void
    {
        try {
            $color = $status === 'success' ? 3066993 : 15158332; // Green or Red

            Http::post($webhook, [
                'embeds' => [
                    [
                        'title' => 'Backup ' . ucfirst($status),
                        'color' => $color,
                        'fields' => [
                            [
                                'name' => 'Backup Name',
                                'value' => $data['backup_name'],
                                'inline' => true,
                            ],
                            [
                                'name' => 'Database',
                                'value' => $data['database'] ?? '-',
                                'inline' => true,
                            ],
                            [
                                'name' => $status === 'success' ? 'File Size' : 'Error',
                                'value' => $data['file_size'] ?? $data['error'] ?? '-',
                            ],
                        ],
                        'timestamp' => now()->toIso8601String(),
                        'footer' => [
                            'text' => config('app.name'),
                        ],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send Discord notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format email body
     */
    private function formatEmailBody($configuration, string $status, array $data): string
    {
        $body = "Backup " . ucfirst($status) . "\n\n";
        $body .= "Backup Name: " . $configuration->name . "\n";
        $body .= "Database: " . $configuration->database->name . "\n";
        $body .= "Database Type: " . $configuration->database->type . "\n";

        if ($status === 'success') {
            $body .= "File Size: " . ($data['file_size'] ?? '-') . "\n";
            $body .= "Duration: " . ($data['duration'] ?? '-') . "\n";
        } else {
            $body .= "\nError: " . ($data['error'] ?? 'Unknown error') . "\n";
        }

        $body .= "\nTimestamp: " . now()->toDateTimeString() . "\n";

        return $body;
    }
}
