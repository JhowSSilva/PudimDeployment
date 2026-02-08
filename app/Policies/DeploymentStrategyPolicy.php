<?php

namespace App\Policies;

use App\Models\DeploymentStrategy;
use App\Models\User;

class DeploymentStrategyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DeploymentStrategy $deploymentStrategy): bool
    {
        return $user->current_team_id === $deploymentStrategy->team_id;
    }

    public function create(User $user): bool
    {
        return $user->current_team_id !== null;
    }

    public function update(User $user, DeploymentStrategy $deploymentStrategy): bool
    {
        return $user->current_team_id === $deploymentStrategy->team_id;
    }

    public function delete(User $user, DeploymentStrategy $deploymentStrategy): bool
    {
        return $user->current_team_id === $deploymentStrategy->team_id;
    }

    public function restore(User $user, DeploymentStrategy $deploymentStrategy): bool
    {
        return $user->current_team_id === $deploymentStrategy->team_id;
    }

    public function forceDelete(User $user, DeploymentStrategy $deploymentStrategy): bool
    {
        return $user->current_team_id === $deploymentStrategy->team_id;
    }
}
