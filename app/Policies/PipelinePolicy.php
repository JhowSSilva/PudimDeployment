<?php

namespace App\Policies;

use App\Models\Pipeline;
use App\Models\User;

class PipelinePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Team filtering happens in controller
    }

    public function view(User $user, Pipeline $pipeline): bool
    {
        return $user->current_team_id === $pipeline->team_id;
    }

    public function create(User $user): bool
    {
        return $user->current_team_id !== null;
    }

    public function update(User $user, Pipeline $pipeline): bool
    {
        return $user->current_team_id === $pipeline->team_id;
    }

    public function delete(User $user, Pipeline $pipeline): bool
    {
        return $user->current_team_id === $pipeline->team_id;
    }

    public function restore(User $user, Pipeline $pipeline): bool
    {
        return $user->current_team_id === $pipeline->team_id;
    }

    public function forceDelete(User $user, Pipeline $pipeline): bool
    {
        return $user->current_team_id === $pipeline->team_id;
    }
}
