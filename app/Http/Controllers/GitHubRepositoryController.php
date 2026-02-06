<?php

namespace App\Http\Controllers;

use App\Models\GitHubRepository;
use App\Services\RepositoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GitHubRepositoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasGitHubConnected()) {
            return redirect()->route('github.settings')
                ->with('info', 'Por favor, conecte sua conta GitHub primeiro');
        }

        $repositories = GitHubRepository::where('user_id', $user->id)
            ->with(['workflowRuns' => fn($q) => $q->latest()->limit(1)])
            ->when($request->get('search'), function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->get('language'), function($query, $language) {
                $query->where('language', $language);
            })
            ->orderBy('last_synced_at', 'desc')
            ->paginate(20);

        return view('github.repositories.index', compact('repositories'));
    }

    public function sync(Request $request)
    {
        $user = Auth::user();
        $service = new RepositoryService($user);
        
        try {
            $service->syncRepositories(true);
            return back()->with('success', 'Repositories synced successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to sync repositories: ' . $e->getMessage());
        }
    }

    public function show(GitHubRepository $repository)
    {
        $this->authorize('view', $repository);
        
        $repository->load([
            'workflows',
            'workflowRuns' => fn($q) => $q->latest()->limit(10),
            'pages'
        ]);

        return view('github.repositories.show', compact('repository'));
    }

    public function setupWebhook(GitHubRepository $repository)
    {
        $this->authorize('update', $repository);
        
        $service = new RepositoryService(Auth::user());
        $webhookUrl = route('github.webhook');
        $secret = config('services.github.webhook_secret');
        
        try {
            $service->setupWebhook($repository, $webhookUrl, $secret);
            return back()->with('success', 'Webhook configured successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to setup webhook: ' . $e->getMessage());
        }
    }
}
