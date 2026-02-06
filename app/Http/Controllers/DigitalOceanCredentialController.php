<?php

namespace App\Http\Controllers;

use App\Models\DigitalOceanCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DigitalOceanCredentialController extends Controller
{
    public function index()
    {
        $credentials = Auth::user()->currentTeam()->digitalOceanCredentials()->latest()->get();
        
        return view('digitalocean-credentials.index', compact('credentials'));
    }

    public function create()
    {
        $regions = [
            'nyc1' => 'New York 1',
            'nyc3' => 'New York 3',
            'sfo3' => 'San Francisco 3',
            'ams3' => 'Amsterdam 3',
            'sgp1' => 'Singapore 1',
            'lon1' => 'London 1',
            'fra1' => 'Frankfurt 1',
            'tor1' => 'Toronto 1',
            'blr1' => 'Bangalore 1',
            'syd1' => 'Sydney 1',
        ];
        
        return view('digitalocean-credentials.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_token' => 'required|string|max:255',
            'region' => 'required|string|max:255',
        ]);

        // Validate credentials with DigitalOcean API (skip in testing environment)
        if (!config('app.skip_cloud_validation', false)) {
            $validationService = new \App\Services\Cloud\CloudValidationService();
            $validation = $validationService->validateDigitalOceanCredentials($validated);
            
            if (!$validation['valid']) {
                return back()->withErrors(['api_token' => $validation['message']])->withInput();
            }
        }

        $validated['team_id'] = auth()->user()->currentTeam()->id;
        $credential = DigitalOceanCredential::create($validated);

        if (auth()->user()->currentTeam()->digitalOceanCredentials()->count() === 1) {
            $credential->update(['is_default' => true]);
        }

        return redirect()->route('digitalocean-credentials.index')
            ->with('success', 'Credenciais DigitalOcean adicionadas com sucesso!');
    }

    public function edit(DigitalOceanCredential $digitaloceanCredential)
    {
        $regions = [
            'nyc1' => 'New York 1',
            'nyc3' => 'New York 3',
            'sfo3' => 'San Francisco 3',
            'ams3' => 'Amsterdam 3',
            'sgp1' => 'Singapore 1',
            'lon1' => 'London 1',
            'fra1' => 'Frankfurt 1',
            'tor1' => 'Toronto 1',
            'blr1' => 'Bangalore 1',
            'syd1' => 'Sydney 1',
        ];
        
        return view('digitalocean-credentials.edit', compact('digitaloceanCredential', 'regions'));
    }

    public function update(Request $request, DigitalOceanCredential $digitaloceanCredential)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_token' => 'nullable|string|max:255',
            'region' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);

        if (empty($validated['api_token'])) {
            unset($validated['api_token']);
        }

        $digitaloceanCredential->update($validated);

        if ($request->has('is_default') && $request->is_default) {
            Auth::user()->currentTeam()->digitalOceanCredentials()
                ->where('id', '!=', $digitaloceanCredential->id)
                ->update(['is_default' => false]);
        }

        return redirect()->route('digitalocean-credentials.index')
            ->with('success', 'Credenciais DigitalOcean atualizadas com sucesso!');
    }

    public function destroy(DigitalOceanCredential $digitaloceanCredential)
    {
        $digitaloceanCredential->delete();

        return redirect()->route('digitalocean-credentials.index')
            ->with('success', 'Credenciais DigitalOcean removidas com sucesso!');
    }
}

