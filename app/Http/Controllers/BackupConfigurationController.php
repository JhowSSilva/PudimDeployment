<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBackupConfigurationRequest;
use App\Http\Requests\UpdateBackupConfigurationRequest;
use App\Jobs\ExecuteBackupJob;
use App\Models\BackupConfiguration;
use App\Models\BackupDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupConfigurationController extends Controller
{
    /**
     * Display a listing of backups
     */
    public function index(Request $request)
    {
        $query = BackupConfiguration::with([
            'database.server',
            'database',
            'recentJobs' => fn($q) => $q->latest()->limit(1)
        ])
            ->where('team_id', Auth::user()->currentTeam->id);

        // Filters
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($provider = $request->get('storage_provider')) {
            $query->where('storage_provider', $provider);
        }

        $backups = $query->latest()->paginate(20);

        return view('backups.index', compact('backups'));
    }

    /**
     * Show the form for creating a new backup
     */
    public function create()
    {
        $databases = BackupDatabase::where('team_id', Auth::user()->currentTeam->id)
            ->with('server')
            ->get();

        $providers = config('backup-providers.providers');
        $compressionTypes = config('backup-providers.compression_types');
        $frequencies = config('backup-providers.frequencies');

        return view('backups.create', compact(
            'databases',
            'providers',
            'compressionTypes',
            'frequencies'
        ));
    }

    /**
     * Store a newly created backup
     */
    public function store(StoreBackupConfigurationRequest $request)
    {
        $data = $request->validated();
        $data['team_id'] = Auth::user()->currentTeam->id;
        
        // Calculate next backup time
        $config = new BackupConfiguration($data);
        $data['next_backup_at'] = $config->calculateNextBackup();

        $backup = BackupConfiguration::create($data);

        // Create notification settings if provided
        if ($request->has('notifications')) {
            $backup->notificationSettings()->create($request->input('notifications'));
        }

        return redirect()
            ->route('backups.show', $backup)
            ->with('success', 'Backup configuration created successfully!');
    }

    /**
     * Display the specified backup
     */
    public function show(BackupConfiguration $backup)
    {
        $this->authorize('view', $backup);

        $backup->load([
            'database.server',
            'files' => fn($q) => $q->latest()->limit(10),
            'recentJobs',
            'notificationSettings'
        ]);

        $stats = [
            'total_backups' => $backup->total_backups,
            'failed_backups' => $backup->failed_backups,
            'success_rate' => $backup->success_rate,
            'total_size' => $backup->total_size,
            'last_backup' => $backup->last_backup_at,
            'next_backup' => $backup->next_backup_at,
        ];

        return view('backups.show', compact('backup', 'stats'));
    }

    /**
     * Show the form for editing the backup
     */
    public function edit(BackupConfiguration $backup)
    {
        $this->authorize('update', $backup);

        $databases = BackupDatabase::where('team_id', Auth::user()->currentTeam->id)
            ->with('server')
            ->get();

        $providers = config('backup-providers.providers');
        $compressionTypes = config('backup-providers.compression_types');
        $frequencies = config('backup-providers.frequencies');

        $backup->load('notificationSettings');

        return view('backups.edit', compact(
            'backup',
            'databases',
            'providers',
            'compressionTypes',
            'frequencies'
        ));
    }

    /**
     * Update the specified backup
     */
    public function update(UpdateBackupConfigurationRequest $request, BackupConfiguration $backup)
    {
        $this->authorize('update', $backup);

        $data = $request->validated();
        
        // Recalculate next backup if frequency changed
        if ($request->has('frequency') || $request->has('start_time')) {
            $backup->fill($data);
            $data['next_backup_at'] = $backup->calculateNextBackup();
        }

        $backup->update($data);

        // Update notification settings
        if ($request->has('notifications')) {
            $backup->notificationSettings()->updateOrCreate(
                ['backup_configuration_id' => $backup->id],
                $request->input('notifications')
            );
        }

        return redirect()
            ->route('backups.show', $backup)
            ->with('success', 'Backup configuration updated successfully!');
    }

    /**
     * Remove the specified backup
     */
    public function destroy(BackupConfiguration $backup)
    {
        $this->authorize('delete', $backup);

        $backup->delete();

        return redirect()
            ->route('backups.index')
            ->with('success', 'Backup configuration deleted successfully!');
    }

    /**
     * Run backup manually
     */
    public function run(BackupConfiguration $backup)
    {
        $this->authorize('update', $backup);

        if ($backup->status === 'running') {
            return back()->with('warning', 'Backup is already running!');
        }

        ExecuteBackupJob::dispatch($backup);

        return back()->with('success', 'Backup job dispatched! It will run in the background.');
    }

    /**
     * Pause backup
     */
    public function pause(BackupConfiguration $backup)
    {
        $this->authorize('update', $backup);

        $backup->pause();

        return back()->with('success', 'Backup paused successfully!');
    }

    /**
     * Resume backup
     */
    public function resume(BackupConfiguration $backup)
    {
        $this->authorize('update', $backup);

        $backup->resume();

        return back()->with('success', 'Backup resumed successfully!');
    }
}
