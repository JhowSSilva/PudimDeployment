<?php

namespace App\Policies;

use App\Models\Server;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Server $server): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $server->team_id === $currentTeam->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $currentTeam->userCan($user, 'create-resources');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Server $server): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $server->team_id === $currentTeam->id 
            && $currentTeam->userCan($user, 'create-resources');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Server $server): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $server->team_id === $currentTeam->id 
            && $currentTeam->userCan($user, 'delete-resources');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Server $server): bool
    {
        return $this->delete($user, $server);
    }

    /**
     * Determine whether the user can manage the server (monitoring, firewall, etc.).
     */
    public function manage(User $user, Server $server): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $server->team_id === $currentTeam->id 
            && $currentTeam->userCan($user, 'create-resources');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Server $server): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $server->team_id === $currentTeam->id 
            && $currentTeam->isOwner($user);
    }
}
