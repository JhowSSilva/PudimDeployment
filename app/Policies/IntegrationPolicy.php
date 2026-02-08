<?php

namespace App\Policies;

use App\Models\Integration;
use App\Models\User;

class IntegrationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Integration $integration): bool
    {
        return $user->current_team_id === $integration->team_id;
    }

    public function create(User $user): bool
    {
        return $user->current_team_id !== null;
    }

    public function update(User $user, Integration $integration): bool
    {
        return $user->current_team_id === $integration->team_id;
    }

    public function delete(User $user, Integration $integration): bool
    {
        return $user->current_team_id === $integration->team_id;
    }

    public function restore(User $user, Integration $integration): bool
    {
        return $user->current_team_id === $integration->team_id;
    }

    public function forceDelete(User $user, Integration $integration): bool
    {
        return $user->current_team_id === $integration->team_id;
    }
}
