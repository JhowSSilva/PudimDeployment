<?php

namespace App\Http\Controllers;

use App\Models\AzureCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AzureCredentialController extends Controller
{
    public function index()
    {
        $credentials = Auth::user()->currentTeam()->azureCredentials()->latest()->get();
        
        return view('azure-credentials.index', compact('credentials'));
    }

    public function create()
    {
        $regions = [
            'eastus' => 'East US',
            'eastus2' => 'East US 2',
            'westus' => 'West US',
            'westus2' => 'West US 2',
            'centralus' => 'Central US',
            'northcentralus' => 'North Central US',
            'southcentralus' => 'South Central US',
            'northeurope' => 'North Europe',
            'westeurope' => 'West Europe',
            'southeastasia' => 'Southeast Asia',
            'eastasia' => 'East Asia',
            'brazilsouth' => 'Brazil South',
            'japaneast' => 'Japan East',
            'australiaeast' => 'Australia East',
        ];
        
        return view('azure-credentials.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subscription_id' => 'required|string|max:255',
            'tenant_id' => 'required|string|max:255',
            'client_id' => 'required|string|max:255',
            'client_secret' => 'required|string',
            'region' => 'required|string|max:255',
        ]);

        // Validate credentials with Azure API (skip in testing environment)
        if (!config('app.skip_cloud_validation', false)) {
            $validationService = new \App\Services\Cloud\CloudValidationService();
            $validation = $validationService->validateAzureCredentials($validated);
            
            if (!$validation['valid']) {
                return back()->withErrors(['client_secret' => $validation['message']])->withInput();
            }
        }

        $validated['team_id'] = auth()->user()->currentTeam()->id;
        $credential = AzureCredential::create($validated);

        // Set as default if it's the first one
        if (auth()->user()->currentTeam()->azureCredentials()->count() === 1) {
            $credential->update(['is_default' => true]);
        }

        return redirect()->route('azure-credentials.index')
            ->with('success', 'Credenciais Azure adicionadas com sucesso!');
    }

    public function edit(AzureCredential $azureCredential)
    {
        $regions = [
            'eastus' => 'East US',
            'eastus2' => 'East US 2',
            'westus' => 'West US',
            'westus2' => 'West US 2',
            'centralus' => 'Central US',
            'northcentralus' => 'North Central US',
            'southcentralus' => 'South Central US',
            'northeurope' => 'North Europe',
            'westeurope' => 'West Europe',
            'southeastasia' => 'Southeast Asia',
            'eastasia' => 'East Asia',
            'brazilsouth' => 'Brazil South',
            'japaneast' => 'Japan East',
            'australiaeast' => 'Australia East',
        ];
        
        return view('azure-credentials.edit', compact('azureCredential', 'regions'));
    }

    public function update(Request $request, AzureCredential $azureCredential)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subscription_id' => 'required|string|max:255',
            'tenant_id' => 'required|string|max:255',
            'client_id' => 'required|string|max:255',
            'client_secret' => 'nullable|string',
            'region' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);

        if (empty($validated['client_secret'])) {
            unset($validated['client_secret']);
        }

        $azureCredential->update($validated);

        if ($request->has('is_default') && $request->is_default) {
            Auth::user()->currentTeam()->azureCredentials()
                ->where('id', '!=', $azureCredential->id)
                ->update(['is_default' => false]);
        }

        return redirect()->route('azure-credentials.index')
            ->with('success', 'Credenciais Azure atualizadas com sucesso!');
    }

    public function destroy(AzureCredential $azureCredential)
    {
        $azureCredential->delete();

        return redirect()->route('azure-credentials.index')
            ->with('success', 'Credenciais Azure removidas com sucesso!');
    }
}

