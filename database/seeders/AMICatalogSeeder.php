<?php

namespace Database\Seeders;

use App\Models\AMICatalog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AMICatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ubuntu 22.04 LTS AMIs (updated as of 2024)
        // These are the official Ubuntu AMIs for different regions and architectures
        
        $amis = [
            // us-east-1 (N. Virginia)
            ['region' => 'us-east-1', 'ami_id' => 'ami-0c7217cdde317cfec', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'us-east-1', 'ami_id' => 'ami-0d8a9b0419ddb331a', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // us-east-2 (Ohio)
            ['region' => 'us-east-2', 'ami_id' => 'ami-0a695f0d95cefc163', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'us-east-2', 'ami_id' => 'ami-0c820c196a818d66a', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // us-west-1 (N. California)
            ['region' => 'us-west-1', 'ami_id' => 'ami-0819a8650d771b8be', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'us-west-1', 'ami_id' => 'ami-0da424eb883458071', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // us-west-2 (Oregon)
            ['region' => 'us-west-2', 'ami_id' => 'ami-0aff18ec83b712f05', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'us-west-2', 'ami_id' => 'ami-0d8e27447ec9f8633', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // eu-west-1 (Ireland)
            ['region' => 'eu-west-1', 'ami_id' => 'ami-0905a3c97561e0b69', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'eu-west-1', 'ami_id' => 'ami-0f3164307ee5d695a', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // eu-west-2 (London)
            ['region' => 'eu-west-2', 'ami_id' => 'ami-0eb260c4d5475b901', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'eu-west-2', 'ami_id' => 'ami-096cb92bb3580c759', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // eu-west-3 (Paris)
            ['region' => 'eu-west-3', 'ami_id' => 'ami-00ac45f3035ff009e', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'eu-west-3', 'ami_id' => 'ami-0f15e0a4c8d3ee5fe', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // eu-central-1 (Frankfurt)
            ['region' => 'eu-central-1', 'ami_id' => 'ami-0faab6bdbac9486fb', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'eu-central-1', 'ami_id' => 'ami-0e872aee57663ae2d', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // ap-south-1 (Mumbai)
            ['region' => 'ap-south-1', 'ami_id' => 'ami-0f58b397bc5c1f2e8', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'ap-south-1', 'ami_id' => 'ami-062df10d14676e201', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // ap-southeast-1 (Singapore)
            ['region' => 'ap-southeast-1', 'ami_id' => 'ami-0d07675d294f17973', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'ap-southeast-1', 'ami_id' => 'ami-0497a974f8d5dcef8', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // ap-southeast-2 (Sydney)
            ['region' => 'ap-southeast-2', 'ami_id' => 'ami-001f2488b35ca8aad', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'ap-southeast-2', 'ami_id' => 'ami-0310483fb2b488153', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // ap-northeast-1 (Tokyo)
            ['region' => 'ap-northeast-1', 'ami_id' => 'ami-09a81b370b76de6a2', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'ap-northeast-1', 'ami_id' => 'ami-0d979355d03fa2522', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // sa-east-1 (São Paulo)
            ['region' => 'sa-east-1', 'ami_id' => 'ami-0c820c196a818d66a', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'sa-east-1', 'ami_id' => 'ami-0d8a9b0419ddb331a', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
            
            // ca-central-1 (Canada)
            ['region' => 'ca-central-1', 'ami_id' => 'ami-0ea18256de20ecdfc', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'x86_64', 'root_device_type' => 'ebs', 'is_active' => true],
            ['region' => 'ca-central-1', 'ami_id' => 'ami-0fc20dd1da406780b', 'os_name' => 'Ubuntu', 'os_version' => '22.04', 'architecture' => 'arm64', 'root_device_type' => 'ebs', 'is_active' => true],
        ];

        foreach ($amis as $ami) {
            AMICatalog::updateOrCreate(
                [
                    'region' => $ami['region'],
                    'os_version' => $ami['os_version'],
                    'architecture' => $ami['architecture'],
                ],
                $ami
            );
        }

        $this->command->info('✅ AMI Catalog seeded successfully!');
        $this->command->info('Total AMIs: ' . count($amis));
        $this->command->info('Regions covered: ' . count(array_unique(array_column($amis, 'region'))));
        $this->command->info('Architectures: x86_64 and ARM64 (Graviton)');
    }
}

