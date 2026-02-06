<?php

namespace App\Http\Controllers;

use App\Models\GitHubRepository;
use App\Services\GitHubPagesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GitHubPagesController extends Controller
{
    public function show(GitHubRepository $repository)
    {
        $this->authorize('view', $repository);
        
        $pages = $repository->pages;
        $service = new GitHubPagesService(Auth::user());
        $builds = $pages ? $service->getBuilds($repository) : [];

        return view('github.pages.show', compact('repository', 'pages', 'builds'));
    }

    public function enable(Request $request, GitHubRepository $repository)
    {
        $this->authorize('update', $repository);
        
        $request->validate([
            'branch' => 'required|string',
            'path' => 'required|in:/,/docs',
        ]);

        $service = new GitHubPagesService(Auth::user());
        
        if ($service->enablePages($repository, $request->branch, $request->path)) {
            return back()->with('success', 'GitHub Pages enabled successfully!');
        }
        
        return back()->with('error', 'Failed to enable GitHub Pages');
    }

    public function disable(GitHubRepository $repository)
    {
        $this->authorize('update', $repository);
        
        $service = new GitHubPagesService(Auth::user());
        
        if ($service->disablePages($repository)) {
            return back()->with('success', 'GitHub Pages disabled');
        }
        
        return back()->with('error', 'Failed to disable GitHub Pages');
    }

    public function update(Request $request, GitHubRepository $repository)
    {
        $this->authorize('update', $repository);
        
        $request->validate([
            'branch' => 'sometimes|string',
            'path' => 'sometimes|in:/,/docs',
            'custom_domain' => 'nullable|string',
            'https_enforced' => 'sometimes|boolean',
        ]);

        $service = new GitHubPagesService(Auth::user());
        
        if ($service->updatePages(
            $repository,
            $request->branch,
            $request->path,
            $request->custom_domain,
            $request->boolean('https_enforced')
        )) {
            return back()->with('success', 'GitHub Pages updated successfully!');
        }
        
        return back()->with('error', 'Failed to update GitHub Pages');
    }

    public function requestBuild(GitHubRepository $repository)
    {
        $this->authorize('update', $repository);
        
        $service = new GitHubPagesService(Auth::user());
        
        if ($service->requestBuild($repository)) {
            return back()->with('success', 'Build requested successfully!');
        }
        
        return back()->with('error', 'Failed to request build');
    }
}
