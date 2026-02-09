<?php

namespace App\Http\Controllers;

use App\Jobs\ProvisionAzureServerJob;
use App\Jobs\ProvisionGcpServerJob;
use App\Jobs\ProvisionAWSServerJob;
use App\Models\AWSCredential;
use App\Models\GcpCredential;
use App\Models\AzureCredential;
use App\Models\InstanceType;
use App\Models\Server;
use App\Services\Cloud\AWSService;
use App\Services\Cloud\ArchitectureDetector;
use Illuminate\Http\Request;

class CloudProvisionController extends Controller
{
    // List credentials available for the provider (team-scoped)
    public function credentials(Request $request, $provider)
    {
        $team = $request->user()->currentTeam ?? null;

        switch (strtolower($provider)) {
            case 'aws':
                $list = AWSCredential::where('is_active', true)->where('team_id', $team->id ?? null)->get()->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'default_region' => $c->default_region]);
                break;
            case 'gcp':
                $list = GcpCredential::where('team_id', $team->id ?? null)->get()->map(fn($c) => ['id' => $c->id, 'name' => $c->name]);
                break;
            case 'azure':
                $list = AzureCredential::where('team_id', $team->id ?? null)->get()->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'subscription_id' => $c->subscription_id ?? null]);
                break;
            default:
                return response()->json(['error' => 'unsupported provider'], 400);
        }

        return response()->json(['credentials' => $list]);
    }

    // Regions (basic lists or from credential)
    public function regions(Request $request, $provider)
    {
        $provider = strtolower($provider);
        $regions = match ($provider) {
            'aws' => [
                'us-east-1','us-east-2','us-west-1','us-west-2','eu-west-1','eu-central-1','ap-southeast-1','ap-southeast-2','ap-northeast-1',
            ],
            'gcp' => ['us-central1','us-east1','us-west1','europe-west1','asia-east1','asia-south1'],
            'azure' => ['eastus','westeurope','southeastasia','brazilsouth','canadacentral'],
            default => [],
        };

        return response()->json(['regions' => $regions]);
    }

    // Instance types (for AWS use InstanceType model; for others return curated list)
    public function instanceTypes(Request $request, $provider)
    {
        $provider = strtolower($provider);
        $arch = $request->query('arch');

        if ($provider === 'aws') {
            $query = InstanceType::available();
            if ($arch) {
                $query = $query->where('architecture', $arch);
            }
            $types = $query->orderBy('price_per_month')->get()->map(fn($t) => ['name' => $t->name, 'vcpu' => $t->vcpu, 'ram_mb' => $t->ram_mb, 'architecture' => $t->architecture]);
            return response()->json(['instance_types' => $types]);
        }

        // Curated lists for GCP/Azure (expand later to real API calls)
        $catalog = match ($provider) {
            'gcp' => [
                ['name' => 'e2-small', 'vcpu' => 1, 'ram_mb' => 2048, 'architecture' => 'x86_64'],
                ['name' => 'e2-medium', 'vcpu' => 2, 'ram_mb' => 4096, 'architecture' => 'x86_64'],
                ['name' => 't2a-standard-2', 'vcpu' => 2, 'ram_mb' => 4096, 'architecture' => 'arm64'],
            ],
            'azure' => [
                ['name' => 'Standard_B1s', 'vcpu' => 1, 'ram_mb' => 1024, 'architecture' => 'x86_64'],
                ['name' => 'Standard_D2s_v3', 'vcpu' => 2, 'ram_mb' => 8192, 'architecture' => 'x86_64'],
                ['name' => 'Standard_A1_v2', 'vcpu' => 1, 'ram_mb' => 2048, 'architecture' => 'arm64'],
            ],
            default => [],
        };

        if ($arch) {
            $catalog = array_values(array_filter($catalog, fn($t) => strpos(strtolower($t['architecture']), strtolower($arch)) !== false));
        }

        return response()->json(['instance_types' => $catalog]);
    }

    // Provision in provider account (creates server record and dispatches job)
    public function provision(Request $request, $provider)
    {
        $provider = strtolower($provider);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'credential_id' => 'required|integer',
            'region' => 'required|string',
            'instance_type' => 'required|string',
            'disk_size' => 'required|integer|min:10|max:2000',
            'arch' => 'nullable|string',
        ]);

        // detect architecture
        $architecture = $validated['arch'] ?? ArchitectureDetector::detect($validated['instance_type']);

        $team = $request->user()?->currentTeam ?? null;

        $server = Server::create([
            'user_id' => $request->user()?->id,
            'team_id' => $team?->id,
            'name' => $validated['name'],
            'ip_address' => 'pending',
            'ssh_port' => 22,
            'provider' => $provider,
            'status' => 'provisioning',
            'instance_type' => $validated['instance_type'],
            'architecture' => $architecture,
            'region' => $validated['region'],
            'disk_size' => $validated['disk_size'],
            'provision_status' => 'pending',
            'provision_log' => [],
        ]);

        // Dispatch provider-specific job
        if ($provider === 'aws') {
            $server->aws_credential_id = $validated['credential_id'];
            $server->save();
            ProvisionAWSServerJob::dispatch($server, [
                'instance_type' => $validated['instance_type'],
                'disk_size' => $validated['disk_size'],
                'region' => $validated['region'],
                'credential_id' => $validated['credential_id'],
            ]);
        } elseif ($provider === 'gcp') {
            // attach credential if exists
            $server->gcp_credential_id = $validated['credential_id'] ?? null;
            $server->save();
            ProvisionGcpServerJob::dispatch($server);
        } elseif ($provider === 'azure') {
            $server->azure_credential_id = $validated['credential_id'] ?? null;
            $server->save();
            ProvisionAzureServerJob::dispatch($server);
        } else {
            return response()->json(['error' => 'unsupported provider'], 400);
        }

        return response()->json(['server' => $server, 'message' => 'Provisioning started'], 201);
    }
}
