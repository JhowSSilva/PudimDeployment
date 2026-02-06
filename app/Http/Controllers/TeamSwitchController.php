<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamSwitchController extends Controller
{
    /**
     * Switch the user's current team.
     */
    public function switch(Request $request, Team $team): RedirectResponse
    {
        // Check if user has access to this team
        if (!$request->user()->switchTeam($team)) {
            return back()->with('error', 'Você não tem acesso a este time.');
        }

        return back()->with('success', "Time alterado para: {$team->name}");
    }
}
