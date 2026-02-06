<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InstanceType;

class InstanceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instances = [
            // T3 - x86_64 - Burstable Performance
            ['name' => 't3.micro', 'arch' => 'x86_64', 'family' => 't3', 'vcpu' => 2, 'memory' => 1.0, 'price_hour' => 0.0104, 'network' => 'Up to 5 Gigabit'],
            ['name' => 't3.small', 'arch' => 'x86_64', 'family' => 't3', 'vcpu' => 2, 'memory' => 2.0, 'price_hour' => 0.0208, 'network' => 'Up to 5 Gigabit'],
            ['name' => 't3.medium', 'arch' => 'x86_64', 'family' => 't3', 'vcpu' => 2, 'memory' => 4.0, 'price_hour' => 0.0416, 'network' => 'Up to 5 Gigabit'],
            ['name' => 't3.large', 'arch' => 'x86_64', 'family' => 't3', 'vcpu' => 2, 'memory' => 8.0, 'price_hour' => 0.0832, 'network' => 'Up to 5 Gigabit'],
            ['name' => 't3.xlarge', 'arch' => 'x86_64', 'family' => 't3', 'vcpu' => 4, 'memory' => 16.0, 'price_hour' => 0.1664, 'network' => 'Up to 5 Gigabit'],
            
            // T4g - ARM64 (Graviton2) - Burstable Performance - 40% cheaper
            ['name' => 't4g.micro', 'arch' => 'arm64', 'family' => 't4g', 'vcpu' => 2, 'memory' => 1.0, 'price_hour' => 0.0084, 'network' => 'Up to 5 Gigabit'],
            ['name' => 't4g.small', 'arch' => 'arm64', 'family' => 't4g', 'vcpu' => 2, 'memory' => 2.0, 'price_hour' => 0.0168, 'network' => 'Up to 5 Gigabit'],
            ['name' => 't4g.medium', 'arch' => 'arm64', 'family' => 't4g', 'vcpu' => 2, 'memory' => 4.0, 'price_hour' => 0.0336, 'network' => 'Up to 5 Gigabit'],
            ['name' => 't4g.large', 'arch' => 'arm64', 'family' => 't4g', 'vcpu' => 2, 'memory' => 8.0, 'price_hour' => 0.0672, 'network' => 'Up to 5 Gigabit'],
            ['name' => 't4g.xlarge', 'arch' => 'arm64', 'family' => 't4g', 'vcpu' => 4, 'memory' => 16.0, 'price_hour' => 0.1344, 'network' => 'Up to 5 Gigabit'],
            
            // M5 - x86_64 - General Purpose
            ['name' => 'm5.large', 'arch' => 'x86_64', 'family' => 'm5', 'vcpu' => 2, 'memory' => 8.0, 'price_hour' => 0.096, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'm5.xlarge', 'arch' => 'x86_64', 'family' => 'm5', 'vcpu' => 4, 'memory' => 16.0, 'price_hour' => 0.192, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'm5.2xlarge', 'arch' => 'x86_64', 'family' => 'm5', 'vcpu' => 8, 'memory' => 32.0, 'price_hour' => 0.384, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'm5.4xlarge', 'arch' => 'x86_64', 'family' => 'm5', 'vcpu' => 16, 'memory' => 64.0, 'price_hour' => 0.768, 'network' => '10 Gigabit'],
            
            // M6g - ARM64 (Graviton2) - General Purpose
            ['name' => 'm6g.medium', 'arch' => 'arm64', 'family' => 'm6g', 'vcpu' => 1, 'memory' => 4.0, 'price_hour' => 0.0385, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'm6g.large', 'arch' => 'arm64', 'family' => 'm6g', 'vcpu' => 2, 'memory' => 8.0, 'price_hour' => 0.077, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'm6g.xlarge', 'arch' => 'arm64', 'family' => 'm6g', 'vcpu' => 4, 'memory' => 16.0, 'price_hour' => 0.154, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'm6g.2xlarge', 'arch' => 'arm64', 'family' => 'm6g', 'vcpu' => 8, 'memory' => 32.0, 'price_hour' => 0.308, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'm6g.4xlarge', 'arch' => 'arm64', 'family' => 'm6g', 'vcpu' => 16, 'memory' => 64.0, 'price_hour' => 0.616, 'network' => '10 Gigabit'],
            
            // M7g - ARM64 (Graviton3) - General Purpose - Latest gen
            ['name' => 'm7g.medium', 'arch' => 'arm64', 'family' => 'm7g', 'vcpu' => 1, 'memory' => 4.0, 'price_hour' => 0.0408, 'network' => 'Up to 12.5 Gigabit'],
            ['name' => 'm7g.large', 'arch' => 'arm64', 'family' => 'm7g', 'vcpu' => 2, 'memory' => 8.0, 'price_hour' => 0.0816, 'network' => 'Up to 12.5 Gigabit'],
            ['name' => 'm7g.xlarge', 'arch' => 'arm64', 'family' => 'm7g', 'vcpu' => 4, 'memory' => 16.0, 'price_hour' => 0.1632, 'network' => 'Up to 12.5 Gigabit'],
            ['name' => 'm7g.2xlarge', 'arch' => 'arm64', 'family' => 'm7g', 'vcpu' => 8, 'memory' => 32.0, 'price_hour' => 0.3264, 'network' => '15 Gigabit'],
            ['name' => 'm7g.4xlarge', 'arch' => 'arm64', 'family' => 'm7g', 'vcpu' => 16, 'memory' => 64.0, 'price_hour' => 0.6528, 'network' => '15 Gigabit'],
            
            // C5 - x86_64 - Compute Optimized
            ['name' => 'c5.large', 'arch' => 'x86_64', 'family' => 'c5', 'vcpu' => 2, 'memory' => 4.0, 'price_hour' => 0.085, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'c5.xlarge', 'arch' => 'x86_64', 'family' => 'c5', 'vcpu' => 4, 'memory' => 8.0, 'price_hour' => 0.17, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'c5.2xlarge', 'arch' => 'x86_64', 'family' => 'c5', 'vcpu' => 8, 'memory' => 16.0, 'price_hour' => 0.34, 'network' => 'Up to 10 Gigabit'],
            
            // C6g - ARM64 (Graviton2) - Compute Optimized
            ['name' => 'c6g.medium', 'arch' => 'arm64', 'family' => 'c6g', 'vcpu' => 1, 'memory' => 2.0, 'price_hour' => 0.034, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'c6g.large', 'arch' => 'arm64', 'family' => 'c6g', 'vcpu' => 2, 'memory' => 4.0, 'price_hour' => 0.068, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'c6g.xlarge', 'arch' => 'arm64', 'family' => 'c6g', 'vcpu' => 4, 'memory' => 8.0, 'price_hour' => 0.136, 'network' => 'Up to 10 Gigabit'],
            
            // R5 - x86_64 - Memory Optimized
            ['name' => 'r5.large', 'arch' => 'x86_64', 'family' => 'r5', 'vcpu' => 2, 'memory' => 16.0, 'price_hour' => 0.126, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'r5.xlarge', 'arch' => 'x86_64', 'family' => 'r5', 'vcpu' => 4, 'memory' => 32.0, 'price_hour' => 0.252, 'network' => 'Up to 10 Gigabit'],
            
            // R6g - ARM64 (Graviton2) - Memory Optimized
            ['name' => 'r6g.medium', 'arch' => 'arm64', 'family' => 'r6g', 'vcpu' => 1, 'memory' => 8.0, 'price_hour' => 0.0504, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'r6g.large', 'arch' => 'arm64', 'family' => 'r6g', 'vcpu' => 2, 'memory' => 16.0, 'price_hour' => 0.1008, 'network' => 'Up to 10 Gigabit'],
            ['name' => 'r6g.xlarge', 'arch' => 'arm64', 'family' => 'r6g', 'vcpu' => 4, 'memory' => 32.0, 'price_hour' => 0.2016, 'network' => 'Up to 10 Gigabit'],
        ];

        $allRegions = ['us-east-1', 'us-west-2', 'eu-west-1', 'sa-east-1', 'ap-southeast-1'];

        foreach ($instances as $instance) {
            InstanceType::updateOrCreate(
                ['name' => $instance['name']],
                [
                    'architecture' => $instance['arch'],
                    'family' => $instance['family'],
                    'vcpu' => $instance['vcpu'],
                    'memory_gib' => $instance['memory'],
                    'price_per_hour' => $instance['price_hour'],
                    'price_per_month' => round($instance['price_hour'] * 730, 2), // 730 hours/month
                    'network_performance' => $instance['network'],
                    'is_available' => true,
                    'regions' => $allRegions,
                    'description' => $this->getDescription($instance),
                ]
            );
        }

        $this->command->info('✅ Instance types seeded successfully!');
        $this->command->info('Total: ' . count($instances) . ' instance types');
        $arm64Count = collect($instances)->where('arch', 'arm64')->count();
        $this->command->info('ARM64 (Graviton): ' . $arm64Count . ' types');
        $this->command->info('x86_64: ' . (count($instances) - $arm64Count) . ' types');
    }

    private function getDescription(array $instance): string
    {
        $arch = $instance['arch'] === 'arm64' ? 'ARM64 (Graviton)' : 'x86_64';
        
        $type = match($instance['family']) {
            't3', 't4g' => 'Burstable Performance',
            'm5', 'm6g', 'm7g' => 'General Purpose',
            'c5', 'c6g', 'c7g' => 'Compute Optimized',
            'r5', 'r6g', 'r7g' => 'Memory Optimized',
            default => 'General Purpose',
        };

        return "{$arch} - {$type} - {$instance['vcpu']} vCPU, {$instance['memory']} GiB RAM";
    }
}
