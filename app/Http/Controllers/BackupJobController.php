<?php

namespace App\Http\Controllers;

use App\Models\BackupConfiguration;
use Illuminate\Http\Request;

class BackupJobController extends Controller
{
    /**
     * Display jobs for a backup configuration
     */
    public function index(BackupConfiguration $backup, Request $request)
    {
        $this->authorize('view', $backup);

        $query = $backup->jobs()->with('file');

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $jobs = $query->latest()->paginate(20);

        return view('backups.jobs', compact('backup', 'jobs'));
    }
}
