<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Server;

class ServerObserver
{
    /**
     * Handle the Server "created" event.
     */
    public function created(Server $server): void
    {
        AuditLog::logAction('created', $server, null, [
            'server_name' => $server->name,
            'ip_address' => $server->ip_address,
            'provider' => $server->provider,
        ]);
    }

    /**
     * Handle the Server "updated" event.
     */
    public function updated(Server $server): void
    {
        AuditLog::logAction('updated', $server, $server->getChanges(), [
            'server_name' => $server->name,
            'changed_fields' => array_keys($server->getChanges()),
        ]);
    }

    /**
     * Handle the Server "deleted" event.
     */
    public function deleted(Server $server): void
    {
        AuditLog::logAction('deleted', $server, null, [
            'server_name' => $server->name,
            'ip_address' => $server->ip_address,
            'sites_count' => $server->sites()->count(),
        ]);
    }

    /**
     * Handle the Server "restored" event.
     */
    public function restored(Server $server): void
    {
        AuditLog::logAction('restored', $server, null, [
            'server_name' => $server->name,
        ]);
    }

    /**
     * Handle the Server "force deleted" event.
     */
    public function forceDeleted(Server $server): void
    {
        AuditLog::logAction('force_deleted', $server, null, [
            'server_name' => $server->name,
        ]);
    }
}
