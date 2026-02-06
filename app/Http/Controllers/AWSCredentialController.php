<?php

namespace App\Http\Controllers;

use App\Models\AWSCredential;
use App\Services\Cloud\AWSService;
use Illuminate\Http\Request;

class AWSCredentialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $credentials = AWSCredential::withCount('servers')->latest()->get();
        
        return view('aws-credentials.index', compact('credentials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $regions = [
            'us-east-1' => 'US East (N. Virginia)',
            'us-east-2' => 'US East (Ohio)',
            'us-west-1' => 'US West (N. California)',
            'us-west-2' => 'US West (Oregon)',
            'eu-west-1' => 'Europe (Ireland)',
            'eu-west-2' => 'Europe (London)',
            'eu-west-3' => 'Europe (Paris)',
            'eu-central-1' => 'Europe (Frankfurt)',
            'ap-south-1' => 'Asia Pacific (Mumbai)',
            'ap-southeast-1' => 'Asia Pacific (Singapore)',
            'ap-southeast-2' => 'Asia Pacific (Sydney)',
            'ap-northeast-1' => 'Asia Pacific (Tokyo)',
            'sa-east-1' => 'South America (São Paulo)',
            'ca-central-1' => 'Canada (Central)',
        ];
        
        return view('aws-credentials.create', compact('regions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'access_key_id' => 'required|string|max:255',
            'secret_access_key' => 'required|string|max:255',
            'default_region' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // Validate credentials with AWS
        try {
            $awsService = new AWSService(null);
            $isValid = $awsService->validateCredentials(
                $validated['access_key_id'],
                $validated['secret_access_key'],
                $validated['default_region']
            );

            if (!$isValid) {
                return back()->withErrors(['access_key_id' => 'Credenciais AWS inválidas. Verifique e tente novamente.'])->withInput();
            }
        } catch (\Exception $e) {
            return back()->withErrors(['access_key_id' => 'Erro ao validar credenciais: ' . $e->getMessage()])->withInput();
        }

        $credential = AWSCredential::create([
            'name' => $validated['name'],
            'access_key_id' => $validated['access_key_id'],
            'secret_access_key' => $validated['secret_access_key'],
            'default_region' => $validated['default_region'],
            'description' => $validated['description'],
            'is_active' => true,
            'last_validated_at' => now(),
        ]);

        return redirect()->route('aws-credentials.index')
            ->with('success', 'Credenciais AWS adicionadas com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(AWSCredential $awsCredential)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AWSCredential $awsCredential)
    {
        $regions = [
            'us-east-1' => 'US East (N. Virginia)',
            'us-east-2' => 'US East (Ohio)',
            'us-west-1' => 'US West (N. California)',
            'us-west-2' => 'US West (Oregon)',
            'eu-west-1' => 'Europe (Ireland)',
            'eu-west-2' => 'Europe (London)',
            'eu-west-3' => 'Europe (Paris)',
            'eu-central-1' => 'Europe (Frankfurt)',
            'ap-south-1' => 'Asia Pacific (Mumbai)',
            'ap-southeast-1' => 'Asia Pacific (Singapore)',
            'ap-southeast-2' => 'Asia Pacific (Sydney)',
            'ap-northeast-1' => 'Asia Pacific (Tokyo)',
            'sa-east-1' => 'South America (São Paulo)',
            'ca-central-1' => 'Canada (Central)',
        ];
        
        return view('aws-credentials.edit', compact('awsCredential', 'regions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AWSCredential $awsCredential)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'access_key_id' => 'nullable|string|max:255',
            'secret_access_key' => 'nullable|string|max:255',
            'default_region' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Only validate if credentials are being updated
        if ($request->filled('access_key_id') && $request->filled('secret_access_key')) {
            try {
                $awsService = new AWSService(null);
                $isValid = $awsService->validateCredentials(
                    $validated['access_key_id'],
                    $validated['secret_access_key'],
                    $validated['default_region']
                );

                if (!$isValid) {
                    return back()->withErrors(['access_key_id' => 'Credenciais AWS inválidas.'])->withInput();
                }
                
                $awsCredential->access_key_id = $validated['access_key_id'];
                $awsCredential->secret_access_key = $validated['secret_access_key'];
                $awsCredential->last_validated_at = now();
            } catch (\Exception $e) {
                return back()->withErrors(['access_key_id' => 'Erro ao validar credenciais: ' . $e->getMessage()])->withInput();
            }
        }

        $awsCredential->name = $validated['name'];
        $awsCredential->default_region = $validated['default_region'];
        $awsCredential->description = $validated['description'];
        $awsCredential->is_active = $request->has('is_active');
        $awsCredential->save();

        return redirect()->route('aws-credentials.index')
            ->with('success', 'Credenciais AWS atualizadas com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AWSCredential $awsCredential)
    {
        if ($awsCredential->servers()->count() > 0) {
            return back()->withErrors(['error' => 'Não é possível excluir credenciais com servidores associados.']);
        }

        $awsCredential->delete();

        return redirect()->route('aws-credentials.index')
            ->with('success', 'Credenciais AWS removidas com sucesso!');
    }
}
