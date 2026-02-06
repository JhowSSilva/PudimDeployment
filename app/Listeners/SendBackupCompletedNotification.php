<?php

namespace App\Listeners;

use App\Events\BackupCompleted;
use App\Services\Backup\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBackupCompletedNotification implements ShouldQueue
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(BackupCompleted $event): void
    {
        $this->notificationService->sendBackupCompleted($event);
    }
}
