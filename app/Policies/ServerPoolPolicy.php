<?php

namespace App\Policies;

use App\Models\ServerPool;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServerPoolPolicy
{
    /**
     * Check if user belongs to the server pool's team.
     */
    private function belongsToTeam(User $user, ServerPool $serverPool): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam && $serverPool->team_id === $currentTeam->id;
    }

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
    public function view(User $user, ServerPool $serverPool): bool
    {
        return $this->belongsToTeam($user, $serverPool);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam && $currentTeam->userCan($user, 'create-resources');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServerPool $serverPool): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam 
            && $serverPool->team_id === $currentTeam->id
            && $currentTeam->userCan($user, 'create-resources');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServerPool $serverPool): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam 
            && $serverPool->team_id === $currentTeam->id
            && $currentTeam->userCan($user, 'delete-resources');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServerPool $serverPool): bool
    {
        return $this->delete($user, $serverPool);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServerPool $serverPool): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam 
            && $serverPool->team_id === $currentTeam->id
            && $currentTeam->isOwner($user);
    }
}
