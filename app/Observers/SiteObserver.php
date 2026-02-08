<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Site;

class SiteObserver
{
    /**
     * Handle the Site "created" event.
     */
    public function created(Site $site): void
    {
        AuditLog::logAction('created', $site, null, [
            'site_name' => $site->name,
            'domain' => $site->domain,
            'server_id' => $site->server_id,
            'php_version' => $site->php_version,
        ]);
    }

    /**
     * Handle the Site "updated" event.
     */
    public function updated(Site $site): void
    {
        AuditLog::logAction('updated', $site, $site->getChanges(), [
            'site_name' => $site->name,
            'domain' => $site->domain,
            'changed_fields' => array_keys($site->getChanges()),
        ]);
    }

    /**
     * Handle the Site "deleted" event.
     */
    public function deleted(Site $site): void
    {
        AuditLog::logAction('deleted', $site, null, [
            'site_name' => $site->name,
            'domain' => $site->domain,
            'deployments_count' => $site->deployments()->count(),
        ]);
    }

    /**
     * Handle the Site "restored" event.
     */
    public function restored(Site $site): void
    {
        AuditLog::logAction('restored', $site, null, [
            'site_name' => $site->name,
            'domain' => $site->domain,
        ]);
    }

    /**
     * Handle the Site "force deleted" event.
     */
    public function forceDeleted(Site $site): void
    {
        AuditLog::logAction('force_deleted', $site, null, [
            'site_name' => $site->name,
            'domain' => $site->domain,
        ]);
    }
}
