<?php

namespace App\Policies;

use App\Models\GitHubRepository;
use App\Models\User;

class GitHubRepositoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasGitHubConnected();
    }

    public function view(User $user, GitHubRepository $repository): bool
    {
        return $user->id === $repository->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasGitHubConnected();
    }

    public function update(User $user, GitHubRepository $repository): bool
    {
        return $user->id === $repository->user_id;
    }

    public function delete(User $user, GitHubRepository $repository): bool
    {
        return $user->id === $repository->user_id;
    }
}
