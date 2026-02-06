<?php

namespace App\Listeners;

use App\Events\BackupFailed;
use App\Services\Backup\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBackupFailedNotification implements ShouldQueue
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(BackupFailed $event): void
    {
        $this->notificationService->sendBackupFailed($event);
    }
}
