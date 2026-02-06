<?php

namespace App\Events;

use App\Models\BackupConfiguration;
use App\Models\BackupJob;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BackupStarted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public BackupConfiguration $configuration,
        public BackupJob $job
    ) {}
}
