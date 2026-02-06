<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Mail\TeamInvitationMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        return view('profile.edit', [
            'user' => $user,
            'ownedTeams' => $user->ownedTeams()->withCount('users')->get(),
            'teams' => $user->teams()->withCount('users')->get(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return Redirect::route('profile.edit')->with('success', 'Senha atualizada com sucesso!');
    }

    /**
     * Create a new team.
     */
    public function createTeam(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $team = Team::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => Str::slug($validated['name']),
            'personal_team' => false,
        ]);

        // Add owner as admin
        $team->users()->attach($request->user()->id, ['role' => 'admin']);

        return Redirect::route('profile.edit')->with('success', 'Time criado com sucesso!');
    }

    /**
     * Invite user to team.
     */
    public function inviteToTeam(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:admin,manager,member,viewer'],
        ]);

        // Check if user exists and is already in team
        $user = User::where('email', $validated['email'])->first();
        if ($user && $team->hasUser($user)) {
            return back()->with('error', 'Este usuário já faz parte do time.');
        }

        // Check for pending invitations
        $existingInvite = TeamInvitation::where('team_id', $team->id)
            ->where('email', $validated['email'])
            ->where('status', 'pending')
            ->first();

        if ($existingInvite) {
            return back()->with('error', 'Já existe um convite pendente para este e-mail.');
        }

        // Create invitation
        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'invited_by' => $request->user()->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        // Send email
        try {
            Mail::to($validated['email'])->send(new TeamInvitationMail($invitation));
            return back()->with('success', 'Convite enviado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('success', 'Convite criado! Link: ' . $invitation->invite_url);
        }
    }

    /**
     * Update team member role.
     */
    public function updateTeamMemberRole(Request $request, Team $team, User $user): RedirectResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'role' => ['required', 'in:admin,manager,member,viewer'],
        ]);

        $team->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', 'Função atualizada com sucesso!');
    }

    /**
     * Remove team member.
     */
    public function removeTeamMember(Request $request, Team $team, User $user): RedirectResponse
    {
        $this->authorize('update', $team);

        if ($team->user_id === $user->id) {
            return back()->with('error', 'Não é possível remover o proprietário do time.');
        }

        $team->users()->detach($user->id);

        return back()->with('success', 'Membro removido com sucesso!');
    }

    /**
     * Delete team.
     */
    public function deleteTeam(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('delete', $team);

        if ($team->personal_team) {
            return back()->with('error', 'Não é possível deletar seu time pessoal.');
        }

        $team->delete();

        return Redirect::route('profile.edit')->with('success', 'Time deletado com sucesso!');
    }

    /**
     * Show team details.
     */
    public function showTeam(Team $team): View
    {
        $this->authorize('view', $team);

        $team->load(['owner', 'users']);

        return view('teams.show', compact('team'));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
