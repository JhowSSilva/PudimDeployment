<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Database;
use App\Models\DatabaseUser;
use App\Services\DatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DatabaseController extends Controller
{
    /**
     * Global databases index - shows databases from all servers
     */
    public function globalIndex()
    {
        $currentTeam = Auth::user()->currentTeam();
        
        $servers = $currentTeam->servers()
            ->with(['databases.users'])
            ->get();
            
        return view('databases.global-index', compact('servers'));
    }
    
    /**
     * Display databases for a server
     */
    public function index(Server $server)
    {
        $databases = $server->databases()
            ->with('users')
            ->latest()
            ->get();

        return view('databases.index', compact('server', 'databases'));
    }

    /**
     * Show form to create database
     */
    public function create(Server $server)
    {
        return view('databases.create', compact('server'));
    }

    /**
     * Store new database
     */
    public function store(Server $server, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:64|regex:/^[a-zA-Z0-9_]+$/',
            'type' => 'required|in:mysql,postgresql',
        ]);

        $databaseService = new DatabaseService($server);
        $result = $databaseService->createDatabase(
            $request->input('name'),
            $request->input('type')
        );

        if ($result['success']) {
            return redirect()->route('servers.databases.index', $server)
                ->with('success', $result['message']);
        }

        return back()->withErrors(['database' => $result['message']])->withInput();
    }

    /**
     * Show database details
     */
    public function show(Server $server, Database $database)
    {
        $database->load('users');
        return view('databases.show', compact('server', 'database'));
    }

    /**
     * Delete database
     */
    public function destroy(Server $server, Database $database)
    {
        $databaseService = new DatabaseService($server);
        $result = $databaseService->deleteDatabase($database);

        if ($result['success']) {
            return redirect()->route('servers.databases.index', $server)
                ->with('success', $result['message']);
        }

        return back()->withErrors(['database' => $result['message']]);
    }

    /**
     * Create database user
     */
    public function createUser(Server $server, Database $database, Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:32|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'required|string|min:8|max:255',
            'privileges' => 'required|array',
            'privileges.*' => 'in:SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,ALL',
        ]);

        $databaseService = new DatabaseService($server);
        $result = $databaseService->createUser(
            $database,
            $request->input('username'),
            $request->input('password'),
            $request->input('privileges')
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['user' => $result['message']]);
    }

    /**
     * Delete database user
     */
    public function deleteUser(Server $server, Database $database, DatabaseUser $user)
    {
        $databaseService = new DatabaseService($server);
        $result = $databaseService->deleteUser($user);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['user' => $result['message']]);
    }

    /**
     * Create database backup
     */
    public function backup(Server $server, Database $database, Request $request)
    {
        $request->validate([
            'backup_name' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9_-]+$/',
        ]);

        $databaseService = new DatabaseService($server);
        $result = $databaseService->createBackup(
            $database,
            $request->input('backup_name')
        );

        if ($result['success']) {
            // Update last backup timestamp
            $database->update(['last_backup_at' => now()]);
            
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['backup' => $result['message']]);
    }

    /**
     * Sync databases from server (detect existing databases)
     */
    public function sync(Server $server, Request $request)
    {
        $request->validate([
            'type' => 'required|in:mysql,postgresql',
        ]);

        $databaseService = new DatabaseService($server);
        $result = $databaseService->listDatabases($request->input('type'));

        if (!$result['success']) {
            return back()->withErrors(['sync' => $result['message']]);
        }

        $existingDbs = $server->databases()
            ->where('type', $request->input('type'))
            ->pluck('name')
            ->toArray();

        $syncedCount = 0;
        foreach ($result['databases'] as $dbName) {
            if (!in_array($dbName, $existingDbs)) {
                Database::create([
                    'server_id' => $server->id,
                    'name' => $dbName,
                    'type' => $request->input('type'),
                    'status' => 'active',
                ]);
                $syncedCount++;
            }
        }

        $message = $syncedCount > 0 
            ? "Synchronized {$syncedCount} new databases"
            : 'No new databases found';

        return back()->with('success', $message);
    }
}