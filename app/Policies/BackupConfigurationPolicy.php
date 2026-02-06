<?php

namespace App\Policies;

use App\Models\BackupConfiguration;
use App\Models\User;

class BackupConfigurationPolicy
{
    /**
     * Determine if the user can view any backups
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the backup
     */
    public function view(User $user, BackupConfiguration $backup): bool
    {
        return $user->currentTeam->id === $backup->team_id;
    }

    /**
     * Determine if the user can create backups
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the backup
     */
    public function update(User $user, BackupConfiguration $backup): bool
    {
        return $user->currentTeam->id === $backup->team_id;
    }

    /**
     * Determine if the user can delete the backup
     */
    public function delete(User $user, BackupConfiguration $backup): bool
    {
        return $user->currentTeam->id === $backup->team_id;
    }
}
