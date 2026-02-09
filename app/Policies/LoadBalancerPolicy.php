<?php

namespace App\Policies;

use App\Models\LoadBalancer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LoadBalancerPolicy
{
    /**
     * Check if user belongs to the load balancer's team.
     */
    private function belongsToTeam(User $user, LoadBalancer $loadBalancer): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam && $loadBalancer->team_id === $currentTeam->id;
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
    public function view(User $user, LoadBalancer $loadBalancer): bool
    {
        return $this->belongsToTeam($user, $loadBalancer);
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
    public function update(User $user, LoadBalancer $loadBalancer): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam 
            && $loadBalancer->team_id === $currentTeam->id
            && $currentTeam->userCan($user, 'create-resources');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LoadBalancer $loadBalancer): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam 
            && $loadBalancer->team_id === $currentTeam->id
            && $currentTeam->userCan($user, 'delete-resources');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LoadBalancer $loadBalancer): bool
    {
        return $this->delete($user, $loadBalancer);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LoadBalancer $loadBalancer): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam 
            && $loadBalancer->team_id === $currentTeam->id
            && $currentTeam->isOwner($user);
    }
}
