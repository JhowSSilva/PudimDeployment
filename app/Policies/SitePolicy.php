<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SitePolicy
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
    public function view(User $user, Site $site): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $site->server->team_id === $currentTeam->id;
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
    public function update(User $user, Site $site): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $site->server->team_id === $currentTeam->id 
            && $currentTeam->userCan($user, 'create-resources');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Site $site): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $site->server->team_id === $currentTeam->id 
            && $currentTeam->userCan($user, 'delete-resources');
    }

    /**
     * Determine whether the user can deploy the site.
     */
    public function deploy(User $user, Site $site): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $site->server->team_id === $currentTeam->id 
            && $currentTeam->userCan($user, 'create-resources');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Site $site): bool
    {
        return $this->delete($user, $site);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Site $site): bool
    {
        $currentTeam = $user->getCurrentTeam();
        
        if (!$currentTeam) {
            return false;
        }
        
        return $site->server->team_id === $currentTeam->id 
            && $currentTeam->isOwner($user);
    }
}
