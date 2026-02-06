<?php

namespace App\Http\Controllers;

use App\Models\GcpCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GcpCredentialController extends Controller
{
    public function index()
    {
        $credentials = Auth::user()->currentTeam->gcpCredentials()->latest()->get();
        
        return view('gcp-credentials.index', compact('credentials'));
    }

    public function create()
    {
        $regions = [
            'us-central1' => 'US Central (Iowa)',
            'us-east1' => 'US East (South Carolina)',
            'us-east4' => 'US East (Virginia)',
            'us-west1' => 'US West (Oregon)',
            'us-west2' => 'US West (Los Angeles)',
            'europe-west1' => 'Europe West (Belgium)',
            'europe-west2' => 'Europe West (London)',
            'europe-west3' => 'Europe West (Frankfurt)',
            'europe-west4' => 'Europe West (Netherlands)',
            'asia-east1' => 'Asia East (Taiwan)',
            'asia-east2' => 'Asia East (Hong Kong)',
            'asia-southeast1' => 'Asia Southeast (Singapore)',
            'asia-south1' => 'Asia South (Mumbai)',
            'southamerica-east1' => 'South America East (São Paulo)',
        ];
        
        return view('gcp-credentials.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|string|max:255',
            'service_account_json' => 'required|string',
            'region' => 'required|string|max:255',
        ]);

        // Validate JSON format first
        if (!json_decode($validated['service_account_json'])) {
            return back()->withErrors(['service_account_json' => 'O JSON da Service Account é inválido.'])->withInput();
        }

        // Validate credentials with GCP API (skip in testing environment)
        if (!config('app.skip_cloud_validation', false)) {
            $validationService = new \App\Services\Cloud\CloudValidationService();
            $validation = $validationService->validateGcpCredentials($validated);
            
            if (!$validation['valid']) {
                return back()->withErrors(['service_account_json' => $validation['message']])->withInput();
            }
        }

        $validated['team_id'] = auth()->user()->currentTeam->id;
        $credential = $validated;
        $credential['team_id'] = auth()->user()->currentTeam->id;
        $credential = GcpCredential::create($validated);

        if (auth()->user()->currentTeam->gcpCredentials()->count() === 1) {
            $credential->update(['is_default' => true]);
        }

        return redirect()->route('gcp-credentials.index')
            ->with('success', 'Credenciais GCP adicionadas com sucesso!');
    }

    public function edit(GcpCredential $gcpCredential)
    {
        $regions = [
            'us-central1' => 'US Central (Iowa)',
            'us-east1' => 'US East (South Carolina)',
            'us-east4' => 'US East (Virginia)',
            'us-west1' => 'US West (Oregon)',
            'us-west2' => 'US West (Los Angeles)',
            'europe-west1' => 'Europe West (Belgium)',
            'europe-west2' => 'Europe West (London)',
            'europe-west3' => 'Europe West (Frankfurt)',
            'europe-west4' => 'Europe West (Netherlands)',
            'asia-east1' => 'Asia East (Taiwan)',
            'asia-east2' => 'Asia East (Hong Kong)',
            'asia-southeast1' => 'Asia Southeast (Singapore)',
            'asia-south1' => 'Asia South (Mumbai)',
            'southamerica-east1' => 'South America East (São Paulo)',
        ];
        
        return view('gcp-credentials.edit', compact('gcpCredential', 'regions'));
    }

    public function update(Request $request, GcpCredential $gcpCredential)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|string|max:255',
            'service_account_json' => 'nullable|string',
            'region' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);

        if (!empty($validated['service_account_json'])) {
            if (!json_decode($validated['service_account_json'])) {
                return back()->withErrors(['service_account_json' => 'O JSON da Service Account é inválido.'])->withInput();
            }
        } else {
            unset($validated['service_account_json']);
        }

        $gcpCredential->update($validated);

        if ($request->has('is_default') && $request->is_default) {
            Auth::user()->currentTeam->gcpCredentials()
                ->where('id', '!=', $gcpCredential->id)
                ->update(['is_default' => false]);
        }

        return redirect()->route('gcp-credentials.index')
            ->with('success', 'Credenciais GCP atualizadas com sucesso!');
    }

    public function destroy(GcpCredential $gcpCredential)
    {
        $gcpCredential->delete();

        return redirect()->route('gcp-credentials.index')
            ->with('success', 'Credenciais GCP removidas com sucesso!');
    }
}

