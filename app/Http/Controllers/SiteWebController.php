<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Server;
use Illuminate\Http\Request;

class SiteWebController extends Controller
{
    public function index()
    {
        $sites = Site::with('server')->latest()->paginate(15);
        return view('sites.index', compact('sites'));
    }

    public function create()
    {
        $servers = Server::where('status', 'online')->get();
        return view('sites.create', compact('servers'));
    }

    public function createForServer(Server $server)
    {
        return view('sites.create', compact('server'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'git_repository' => 'nullable|string|max:500',
            'git_branch' => 'nullable|string|max:100',
            'git_token' => 'nullable|string',
            'document_root' => 'nullable|string|max:255',
            'php_version' => 'required|string|max:10',
            'cloudflare_account_id' => 'nullable|exists:cloudflare_accounts,id',
            'auto_dns' => 'nullable|boolean',
            'cloudflare_proxy' => 'nullable|boolean',
            'ssl_type' => 'required|in:none,letsencrypt,cloudflare',
        ]);

        $validated['status'] = 'active';
        $validated['auto_dns'] = $request->boolean('auto_dns');
        $validated['cloudflare_proxy'] = $request->boolean('cloudflare_proxy');

        $site = Site::create($validated);

        // Dispatch DNS configuration job if enabled and account is set
        if ($validated['auto_dns'] && $validated['cloudflare_account_id']) {
            \App\Jobs\ConfigureDNSJob::dispatch($site)
                ->delay(now()->addSeconds(5));
        }

        // If no auto DNS, but SSL is enabled and account is set, generate SSL directly
        if (!$validated['auto_dns'] && $validated['ssl_type'] !== 'none' && $validated['cloudflare_account_id']) {
            \App\Jobs\GenerateSSLJob::dispatch($site)
                ->delay(now()->addSeconds(30));
        }

        return redirect()->route('sites.index')
            ->with('success', 'Site criado com sucesso! DNS e SSL serão configurados automaticamente.');
    }

    public function show(Site $site)
    {
        $site->load('server', 'deployments');
        return view('sites.show', compact('site'));
    }

    public function edit(Site $site)
    {
        $servers = Server::where('status', 'online')->get();
        return view('sites.edit', compact('site', 'servers'));
    }

    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'git_repository' => 'nullable|string|max:500',
            'git_branch' => 'nullable|string|max:100',
            'git_token' => 'nullable|string',
            'document_root' => 'nullable|string|max:255',
            'php_version' => 'required|string|max:10',
        ]);

        // Remove git_token vazio
        if (empty($validated['git_token'])) {
            unset($validated['git_token']);
        }

        $site->update($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site atualizado com sucesso!');
    }

    public function destroy(Site $site)
    {
        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Site deletado com sucesso!');
    }
}
