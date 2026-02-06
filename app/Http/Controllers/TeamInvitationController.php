<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\User;
use App\Mail\TeamInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeamInvitationController extends Controller
{
    use AuthorizesRequests;
    /**
     * Show the invitation page.
     */
    public function show(string $token): View
    {
        $invitation = TeamInvitation::where('token', $token)
            ->with(['team', 'inviter'])
            ->firstOrFail();

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
        }

        return view('invitations.show', compact('invitation'));
    }

    /**
     * Accept an invitation.
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if (!$invitation->isPending()) {
            if ($invitation->status === 'accepted') {
                return redirect()->route('profile.edit')
                    ->with('error', 'Este convite já foi aceito.');
            }
            return redirect()->route('profile.edit')
                ->with('error', 'Este convite expirou ou não é mais válido.');
        }

        // Check if user is logged in
        if (!Auth::check()) {
            // Store token in session and redirect to register with email
            session(['invitation_token' => $token]);
            return redirect()->route('register', ['email' => $invitation->email])
                ->with('info', 'Crie uma conta para aceitar o convite.');
        }

        $user = Auth::user();

        // Check if email matches
        if ($user->email !== $invitation->email) {
            return redirect()->route('profile.edit')
                ->with('error', 'Este convite foi enviado para outro endereço de e-mail.');
        }

        // Check if user is already in team
        if ($invitation->team->hasUser($user)) {
            $invitation->update(['status' => 'accepted']);
            return redirect()->route('teams.show', $invitation->team)
                ->with('info', 'Você já é membro deste time.');
        }

        // Accept invitation
        if ($invitation->accept($user)) {
            return redirect()->route('teams.show', $invitation->team)
                ->with('success', 'Convite aceito! Você agora é membro do time.');
        }

        return redirect()->route('profile.edit')
            ->with('error', 'Não foi possível aceitar o convite.');
    }

    /**
     * Reject an invitation.
     */
    public function reject(string $token): RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if (!$invitation->isPending()) {
            return redirect()->route('profile.edit')
                ->with('error', 'Este convite não é mais válido.');
        }

        $invitation->reject();

        return redirect()->route('profile.edit')
            ->with('success', 'Convite rejeitado.');
    }

    /**
     * Resend an invitation email.
     */
    public function resend(TeamInvitation $invitation): RedirectResponse
    {
        $this->authorize('update', $invitation->team);

        if ($invitation->status !== 'pending') {
            return back()->with('error', 'Este convite não pode ser reenviado.');
        }

        // Extend expiration
        $invitation->update([
            'expires_at' => now()->addDays(7),
        ]);

        // Send email
        try {
            Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));
            return back()->with('success', 'Convite reenviado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('success', 'Convite atualizado! Link: ' . $invitation->invite_url);
        }
    }

    /**
     * Cancel an invitation.
     */
    public function cancel(TeamInvitation $invitation): RedirectResponse
    {
        $this->authorize('update', $invitation->team);

        if ($invitation->status === 'accepted') {
            return back()->with('error', 'Este convite já foi aceito.');
        }

        $invitation->delete();

        return back()->with('success', 'Convite cancelado.');
    }
}
