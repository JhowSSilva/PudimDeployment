<?php

namespace App\Policies;

use App\Models\ScalingPolicy;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ScalingPolicyPolicy
{
    /**
     * Check if user belongs to the scaling policy's team.
     */
    private function belongsToTeam(User $user, ScalingPolicy $scalingPolicy): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam && $scalingPolicy->team_id === $currentTeam->id;
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
    public function view(User $user, ScalingPolicy $scalingPolicy): bool
    {
        return $this->belongsToTeam($user, $scalingPolicy);
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
    public function update(User $user, ScalingPolicy $scalingPolicy): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam 
            && $scalingPolicy->team_id === $currentTeam->id
            && $currentTeam->userCan($user, 'create-resources');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScalingPolicy $scalingPolicy): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam 
            && $scalingPolicy->team_id === $currentTeam->id
            && $currentTeam->userCan($user, 'delete-resources');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ScalingPolicy $scalingPolicy): bool
    {
        return $this->delete($user, $scalingPolicy);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ScalingPolicy $scalingPolicy): bool
    {
        $currentTeam = $user->getCurrentTeam();
        return $currentTeam 
            && $scalingPolicy->team_id === $currentTeam->id
            && $currentTeam->isOwner($user);
    }
}
