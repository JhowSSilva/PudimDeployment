<?php

namespace App\Services\Cloud;

use App\Contracts\CloudProvider;
use App\Models\Server;
use App\Models\AWSCredential;
use App\Models\AMICatalog;
use App\Models\InstanceType;
use Aws\Ec2\Ec2Client;
use Aws\CloudWatch\CloudWatchClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class AWSService implements CloudProvider
{
    private ?Ec2Client $ec2 = null;
    private ?CloudWatchClient $cloudWatch = null;
    private AWSCredential $credential;

    public function __construct(AWSCredential $credential)
    {
        $this->credential = $credential;
    }

    /**
     * Get or create EC2 client
     */
    private function getEC2Client(string $region = null): Ec2Client
    {
        $region = $region ?? $this->credential->default_region;
        
        if (!$this->ec2) {
            $this->ec2 = new Ec2Client([
                'version' => 'latest',
                'region' => $region,
                'credentials' => [
                    'key' => $this->credential->access_key_id,
                    'secret' => $this->credential->secret_access_key,
                ],
            ]);
        }
        
        return $this->ec2;
    }

    /**
     * Get or create CloudWatch client
     */
    private function getCloudWatchClient(string $region = null): CloudWatchClient
    {
        $region = $region ?? $this->credential->default_region;
        
        if (!$this->cloudWatch) {
            $this->cloudWatch = new CloudWatchClient([
                'version' => 'latest',
                'region' => $region,
                'credentials' => [
                    'key' => $this->credential->access_key_id,
                    'secret' => $this->credential->secret_access_key,
                ],
            ]);
        }
        
        return $this->cloudWatch;
    }

    /**
     * Create a new EC2 instance
     */
    public function createServer(array $config): Server
    {
        try {
            $ec2 = $this->getEC2Client($config['region']);
            
            // Detect architecture
            $architecture = ArchitectureDetector::detect($config['instance_type']);
            
            // Get appropriate AMI
            $ami = AMICatalog::getLatestUbuntu(
                $config['region'],
                $architecture
            );
            
            if (!$ami) {
                throw new \Exception("No AMI found for region {$config['region']} and architecture {$architecture}");
            }

            // Create or get key pair
            $keyPairName = 'server-' . uniqid();
            $keyPair = $this->createKeyPair($keyPairName, $config['region']);

            // Create security group
            $securityGroupId = $this->createSecurityGroup(
                $config['name'] ?? 'managed-server-' . uniqid(),
                $config['region']
            );

            // Launch instance
            $result = $ec2->runInstances([
                'ImageId' => $ami->ami_id,
                'InstanceType' => $config['instance_type'],
                'KeyName' => $keyPairName,
                'MinCount' => 1,
                'MaxCount' => 1,
                'SecurityGroupIds' => [$securityGroupId],
                'BlockDeviceMappings' => [
                    [
                        'DeviceName' => '/dev/sda1',
                        'Ebs' => [
                            'VolumeSize' => $config['disk_size'] ?? 20,
                            'VolumeType' => 'gp3',
                            'DeleteOnTermination' => true,
                        ],
                    ],
                ],
                'TagSpecifications' => [
                    [
                        'ResourceType' => 'instance',
                        'Tags' => [
                            ['Key' => 'Name', 'Value' => $config['name'] ?? 'Managed Server'],
                            ['Key' => 'ManagedBy', 'Value' => config('app.name')],
                        ],
                    ],
                ],
            ]);

            $instance = $result['Instances'][0];
            $instanceId = $instance['InstanceId'];

            // Wait for instance to get public IP
            sleep(10);
            $instanceInfo = $this->getInstanceInfo($instanceId, $config['region']);

            // Calculate monthly cost
            $monthlyCost = $this->estimateCost($config);

            // Create server record
            $server = Server::create([
                'user_id' => auth()->id(),
                'aws_credential_id' => $this->credential->id,
                'name' => $config['name'] ?? 'AWS Server',
                'instance_id' => $instanceId,
                'instance_type' => $config['instance_type'],
                'architecture' => $architecture,
                'region' => $config['region'],
                'availability_zone' => $instance['Placement']['AvailabilityZone'],
                'ami_id' => $ami->ami_id,
                'key_pair_name' => $keyPairName,
                'private_key' => $keyPair['private_key'],
                'security_group_id' => $securityGroupId,
                'disk_size' => $config['disk_size'] ?? 20,
                'public_ip' => $instanceInfo['PublicIpAddress'] ?? null,
                'private_ip' => $instance['PrivateIpAddress'] ?? null,
                'ip_address' => $instanceInfo['PublicIpAddress'] ?? null,
                'monthly_cost' => $monthlyCost,
                'stack_config' => $config['stack'] ?? [],
                'provision_status' => 'pending',
                'status' => 'provisioning',
                'ssh_port' => 22,
                'ssh_user' => 'ubuntu',
                'auth_type' => 'key',
                'os_type' => 'ubuntu',
                'os_version' => $ami->os_version,
            ]);

            Log::info('EC2 instance created', [
                'instance_id' => $instanceId,
                'server_id' => $server->id,
            ]);

            return $server;

        } catch (AwsException $e) {
            Log::error('AWS Error creating instance: ' . $e->getMessage());
            throw new \Exception('Failed to create AWS instance: ' . $e->getAwsErrorMessage());
        }
    }

    /**
     * Create SSH key pair
     */
    private function createKeyPair(string $keyName, string $region): array
    {
        $ec2 = $this->getEC2Client($region);
        
        $result = $ec2->createKeyPair([
            'KeyName' => $keyName,
        ]);

        return [
            'name' => $keyName,
            'private_key' => $result['KeyMaterial'],
        ];
    }

    /**
     * Create security group with basic rules
     */
    private function createSecurityGroup(string $name, string $region): string
    {
        $ec2 = $this->getEC2Client($region);
        
        // Create security group
        $result = $ec2->createSecurityGroup([
            'GroupName' => $name . '-sg',
            'Description' => 'Security group for ' . $name,
        ]);

        $groupId = $result['GroupId'];

        // Add inbound rules
        $ec2->authorizeSecurityGroupIngress([
            'GroupId' => $groupId,
            'IpPermissions' => [
                [
                    'IpProtocol' => 'tcp',
                    'FromPort' => 22,
                    'ToPort' => 22,
                    'IpRanges' => [['CidrIp' => '0.0.0.0/0']],
                ],
                [
                    'IpProtocol' => 'tcp',
                    'FromPort' => 80,
                    'ToPort' => 80,
                    'IpRanges' => [['CidrIp' => '0.0.0.0/0']],
                ],
                [
                    'IpProtocol' => 'tcp',
                    'FromPort' => 443,
                    'ToPort' => 443,
                    'IpRanges' => [['CidrIp' => '0.0.0.0/0']],
                ],
            ],
        ]);

        return $groupId;
    }

    /**
     * Get instance information
     */
    private function getInstanceInfo(string $instanceId, string $region): array
    {
        $ec2 = $this->getEC2Client($region);
        
        $result = $ec2->describeInstances([
            'InstanceIds' => [$instanceId],
        ]);

        return $result['Reservations'][0]['Instances'][0] ?? [];
    }

    /**
     * Start a stopped server
     */
    public function startServer(string $instanceId): bool
    {
        try {
            $ec2 = $this->getEC2Client();
            $ec2->startInstances(['InstanceIds' => [$instanceId]]);
            
            Log::info('EC2 instance started', ['instance_id' => $instanceId]);
            return true;
        } catch (AwsException $e) {
            Log::error('AWS Error starting instance: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Stop a running server
     */
    public function stopServer(string $instanceId): bool
    {
        try {
            $ec2 = $this->getEC2Client();
            $ec2->stopInstances(['InstanceIds' => [$instanceId]]);
            
            Log::info('EC2 instance stopped', ['instance_id' => $instanceId]);
            return true;
        } catch (AwsException $e) {
            Log::error('AWS Error stopping instance: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reboot a server
     */
    public function rebootServer(string $instanceId): bool
    {
        try {
            $ec2 = $this->getEC2Client();
            $ec2->rebootInstances(['InstanceIds' => [$instanceId]]);
            
            Log::info('EC2 instance rebooted', ['instance_id' => $instanceId]);
            return true;
        } catch (AwsException $e) {
            Log::error('AWS Error rebooting instance: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Terminate (delete) a server
     */
    public function terminateServer(string $instanceId): bool
    {
        try {
            $ec2 = $this->getEC2Client();
            $ec2->terminateInstances(['InstanceIds' => [$instanceId]]);
            
            Log::info('EC2 instance terminated', ['instance_id' => $instanceId]);
            return true;
        } catch (AwsException $e) {
            Log::error('AWS Error terminating instance: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get instance status
     */
    public function getInstanceStatus(string $instanceId): string
    {
        try {
            $ec2 = $this->getEC2Client();
            $result = $ec2->describeInstanceStatus([
                'InstanceIds' => [$instanceId],
                'IncludeAllInstances' => true,
            ]);

            if (empty($result['InstanceStatuses'])) {
                return 'unknown';
            }

            return $result['InstanceStatuses'][0]['InstanceState']['Name'] ?? 'unknown';
        } catch (AwsException $e) {
            Log::error('AWS Error getting instance status: ' . $e->getMessage());
            return 'error';
        }
    }

    /**
     * Get available instance types
     */
    public function getInstanceTypes(string $region): array
    {
        // Return from database cache
        return InstanceType::available()
            ->whereJsonContains('regions', $region)
            ->orderBy('price_per_month')
            ->get()
            ->toArray();
    }

    /**
     * Get available AMIs
     */
    public function getAvailableAMIs(string $region, string $os, string $arch): array
    {
        return AMICatalog::forRegion($region)
            ->forArchitecture($arch)
            ->active()
            ->get()
            ->toArray();
    }

    /**
     * Get CloudWatch metrics
     */
    public function getMetrics(string $instanceId, string $metric, int $period = 300): array
    {
        try {
            $cloudWatch = $this->getCloudWatchClient();
            
            $metricName = match($metric) {
                'cpu' => 'CPUUtilization',
                'network_in' => 'NetworkIn',
                'network_out' => 'NetworkOut',
                default => 'CPUUtilization',
            };

            $result = $cloudWatch->getMetricStatistics([
                'Namespace' => 'AWS/EC2',
                'MetricName' => $metricName,
                'Dimensions' => [
                    ['Name' => 'InstanceId', 'Value' => $instanceId],
                ],
                'StartTime' => strtotime('-1 hour'),
                'EndTime' => time(),
                'Period' => $period,
                'Statistics' => ['Average', 'Maximum'],
            ]);

            return $result['Datapoints'] ?? [];
        } catch (AwsException $e) {
            Log::error('AWS Error getting metrics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Estimate monthly cost
     */
    public function estimateCost(array $config): float
    {
        $instanceType = InstanceType::where('name', $config['instance_type'])->first();
        
        if (!$instanceType) {
            return 0.0;
        }

        // Instance cost
        $instanceCost = $instanceType->price_per_month;

        // EBS storage cost (gp3: $0.08/GB/month)
        $storageCost = ($config['disk_size'] ?? 20) * 0.08;

        // Data transfer (estimate $1/month for small instances)
        $transferCost = 1.00;

        return round($instanceCost + $storageCost + $transferCost, 2);
    }

    /**
     * Validate AWS credentials
     */
    public function validateCredentials(): bool
    {
        try {
            $ec2 = $this->getEC2Client();
            $ec2->describeRegions();
            
            return true;
        } catch (AwsException $e) {
            Log::error('AWS credentials validation failed: ' . $e->getMessage());
            return false;
        }
    }
}
