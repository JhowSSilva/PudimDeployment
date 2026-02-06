<?php

namespace App\Http\Controllers;

use App\Models\CloudflareAccount;
use App\Services\CloudflareService;
use Illuminate\Http\Request;

class CloudflareAccountController extends Controller
{
    public function __construct(private CloudflareService $cloudflareService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = CloudflareAccount::withCount('sites')->latest()->paginate(15);
        
        return view('cloudflare-accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cloudflare-accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_token' => 'required|string',
            'account_id' => 'nullable|string|max:255',
            'zone_id' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        // Verificar se o token é válido
        try {
            $this->cloudflareService->setApiToken($validated['api_token']);
            $this->cloudflareService->verifyToken();
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['api_token' => 'Token inválido: ' . $e->getMessage()]);
        }

        CloudflareAccount::create($validated);

        return redirect()->route('cloudflare-accounts.index')
            ->with('success', 'Conta Cloudflare adicionada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CloudflareAccount $cloudflareAccount)
    {
        return view('cloudflare-accounts.edit', compact('cloudflareAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CloudflareAccount $cloudflareAccount)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_token' => 'nullable|string',
            'account_id' => 'nullable|string|max:255',
            'zone_id' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        // Se enviou novo token, verificar se é válido
        if (!empty($validated['api_token'])) {
            try {
                $this->cloudflareService->setApiToken($validated['api_token']);
                $this->cloudflareService->verifyToken();
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['api_token' => 'Token inválido: ' . $e->getMessage()]);
            }
        } else {
            // Se não enviou token, remover do array para não sobrescrever
            unset($validated['api_token']);
        }

        $cloudflareAccount->update($validated);

        return redirect()->route('cloudflare-accounts.index')
            ->with('success', 'Conta Cloudflare atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CloudflareAccount $cloudflareAccount)
    {
        $sitesCount = $cloudflareAccount->sites()->count();
        
        if ($sitesCount > 0) {
            return back()->withErrors([
                'delete' => "Não é possível deletar esta conta pois ela está sendo usada por {$sitesCount} site(s)."
            ]);
        }

        $cloudflareAccount->delete();

        return redirect()->route('cloudflare-accounts.index')
            ->with('success', 'Conta Cloudflare removida com sucesso!');
    }
}
