<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Team;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Create personal team
        $personalTeam = Team::create([
            'user_id' => $user->id,
            'name' => $user->name . "'s Team",
            'description' => 'Personal team',
            'slug' => \Illuminate\Support\Str::slug($user->name . '-team-' . $user->id),
            'personal_team' => true,
        ]);

        // Add user as admin of personal team
        $personalTeam->users()->attach($user->id, ['role' => 'admin']);

        // Set as current team
        $user->update(['current_team_id' => $personalTeam->id]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
