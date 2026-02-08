<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Display the activity feed.
     */
    public function index(Request $request)
    {
        $team = Auth::user()->currentTeam;

        $query = ActivityLog::where('team_id', $team->id)
            ->with(['user', 'team'])
            ->latest();

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by subject type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $activities = $query->paginate(50);

        // Get available actions for filter
        $actions = ActivityLog::where('team_id', $team->id)
            ->distinct()
            ->pluck('action')
            ->sort();

        // Get available subject types for filter
        $subjectTypes = ActivityLog::where('team_id', $team->id)
            ->distinct()
            ->pluck('subject_type')
            ->sort();

        // Get team members for filter
        $users = $team->users()->orderBy('name')->get();

        return view('activity.index', compact('activities', 'actions', 'subjectTypes', 'users'));
    }

    /**
     * Get activity feed for a specific resource.
     */
    public function resource(Request $request, string $type, int $id)
    {
        $team = Auth::user()->currentTeam;

        $activities = ActivityLog::where('team_id', $team->id)
            ->where('subject_type', $type)
            ->where('subject_id', $id)
            ->with(['user'])
            ->latest()
            ->paginate(20);

        return view('activity.resource', compact('activities', 'type', 'id'));
    }
}
