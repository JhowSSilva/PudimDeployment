<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeamRole;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TeamRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display team roles.
     */
    public function index()
    {
        $team = Auth::user()->currentTeam;

        $roles = TeamRole::where('team_id', $team->id)
            ->withCount('users')
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        return view('team.roles.index', compact('roles'));
    }

    /**
     * Show create role form.
     */
    public function create()
    {
        $permissions = RolePermission::orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        return view('team.roles.create', compact('permissions'));
    }

    /**
     * Store a new role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:role_permissions,slug',
            'color' => 'nullable|string|max:7',
        ]);

        $team = Auth::user()->currentTeam;

        $role = TeamRole::create([
            'team_id' => $team->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'],
            'color' => $validated['color'] ?? '#3b82f6',
            'is_system' => false,
        ]);

        return redirect()->route('team.roles.index')
            ->with('success', 'Role created successfully');
    }

    /**
     * Show edit role form.
     */
    public function edit(TeamRole $role)
    {
        if ($role->is_system) {
            abort(403, 'System roles cannot be edited');
        }

        if ($role->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        $permissions = RolePermission::orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        return view('team.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update a role.
     */
    public function update(Request $request, TeamRole $role)
    {
        if ($role->is_system) {
            abort(403, 'System roles cannot be modified');
        }

        if ($role->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:role_permissions,slug',
            'color' => 'nullable|string|max:7',
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'],
            'color' => $validated['color'] ?? $role->color,
        ]);

        return redirect()->route('team.roles.index')
            ->with('success', 'Role updated successfully');
    }

    /**
     * Delete a role.
     */
    public function destroy(TeamRole $role)
    {
        if ($role->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        try {
            $role->delete();
            return redirect()->route('team.roles.index')
                ->with('success', 'Role deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('team.roles.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Assign role to user.
     */
    public function assign(Request $request, TeamRole $role)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if ($role->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        $role->users()->syncWithoutDetaching([$validated['user_id']]);

        return redirect()->back()->with('success', 'Role assigned successfully');
    }

    /**
     * Remove role from user.
     */
    public function remove(Request $request, TeamRole $role)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if ($role->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        $role->users()->detach($validated['user_id']);

        return redirect()->back()->with('success', 'Role removed successfully');
    }
}
