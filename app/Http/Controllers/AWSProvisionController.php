<?php

namespace App\Http\Controllers;

use App\Jobs\ProvisionAWSServerJob;
use App\Models\AWSCredential;
use App\Models\AMICatalog;
use App\Models\InstanceType;
use App\Models\Server;
use App\Services\Cloud\ArchitectureDetector;
use App\Services\Cloud\AWSService;
use Illuminate\Http\Request;

class AWSProvisionController extends Controller
{
    /**
     * Show wizard step 1: Select AWS credentials
     */
    public function step1()
    {
        $credentials = AWSCredential::where('is_active', true)->get();
        
        return view('aws-provision.step1', compact('credentials'));
    }

    /**
     * Show wizard step 2: Configure instance
     */
    public function step2(Request $request)
    {
        $request->validate([
            'aws_credential_id' => 'required|exists:aws_credentials,id',
        ]);

        $credential = AWSCredential::findOrFail($request->aws_credential_id);
        
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
        
        $instanceTypes = InstanceType::available()->orderBy('price_per_month')->get()
            ->groupBy('architecture');
        
        return view('aws-provision.step2', compact('credential', 'regions', 'instanceTypes'));
    }

    /**
     * Show wizard step 3: Configure stack
     */
    public function step3(Request $request)
    {
        $request->validate([
            'aws_credential_id' => 'required|exists:aws_credentials,id',
            'region' => 'required|string',
            'instance_type' => 'required|string',
            'disk_size' => 'required|integer|min:20|max:1000',
        ]);

        $credential = AWSCredential::findOrFail($request->aws_credential_id);
        $instanceType = InstanceType::where('name', $request->instance_type)->firstOrFail();
        
        return view('aws-provision.step3', compact('credential', 'instanceType'));
    }

    /**
     * Show wizard step 4: Review and provision
     */
    public function step4(Request $request)
    {
        $request->validate([
            'aws_credential_id' => 'required|exists:aws_credentials,id',
            'region' => 'required|string',
            'instance_type' => 'required|string',
            'disk_size' => 'required|integer|min:20|max:1000',
            'webserver' => 'required|in:nginx,apache',
            'php_version' => 'required|in:8.1,8.2,8.3,8.4',
            'database' => 'required|in:mysql,postgresql,none',
            'cache' => 'required|in:redis,memcached,none',
            'nodejs' => 'nullable|in:18,20,22',
            'extras' => 'nullable|array',
        ]);

        $credential = AWSCredential::findOrFail($request->aws_credential_id);
        $instanceType = InstanceType::where('name', $request->instance_type)->firstOrFail();
        
        $awsService = new AWSService($credential);
        
        $cost = $awsService->estimateCost(
            $request->instance_type,
            $request->disk_size
        );
        
        return view('aws-provision.step4', compact('credential', 'instanceType', 'cost'));
    }

    /**
     * Execute provisioning
     */
    public function provision(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'aws_credential_id' => 'required|exists:aws_credentials,id',
            'region' => 'required|string',
            'instance_type' => 'required|string',
            'disk_size' => 'required|integer|min:20|max:1000',
            'webserver' => 'required|in:nginx,apache',
            'php_version' => 'required|in:8.1,8.2,8.3,8.4',
            'database' => 'required|in:mysql,postgresql,none',
            'cache' => 'required|in:redis,memcached,none',
            'nodejs' => 'nullable|in:18,20,22',
            'extras' => 'nullable|array',
        ]);

        $credential = AWSCredential::findOrFail($validated['aws_credential_id']);
        $instanceType = InstanceType::where('name', $validated['instance_type'])->firstOrFail();
        
        $architecture = ArchitectureDetector::detect($validated['instance_type']);
        
        $awsService = new AWSService($credential);
        $cost = $awsService->estimateCost($validated['instance_type'], $validated['disk_size']);
        
        // Create server record
        $server = Server::create([
            'name' => $validated['name'],
            'ip_address' => 'pending',
            'ssh_port' => 22,
            'ssh_username' => 'ubuntu',
            'provider' => 'aws',
            'status' => 'pending',
            'aws_credential_id' => $credential->id,
            'instance_type' => $validated['instance_type'],
            'architecture' => $architecture,
            'region' => $validated['region'],
            'disk_size' => $validated['disk_size'],
            'monthly_cost' => $cost['total'],
            'provision_status' => 'pending',
            'stack_config' => [
                'webserver' => $validated['webserver'],
                'php_version' => $validated['php_version'],
                'database' => $validated['database'],
                'cache' => $validated['cache'],
                'nodejs' => $validated['nodejs'] ?? null,
                'extras' => $validated['extras'] ?? [],
            ],
        ]);

        // Dispatch provisioning job
        ProvisionAWSServerJob::dispatch($server);

        return redirect()->route('servers.index')
            ->with('success', 'Servidor AWS em provisionamento! Acompanhe o progresso no dashboard.');
    }
}
