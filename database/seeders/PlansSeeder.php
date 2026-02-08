<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfeito para começar e testar a plataforma',
                'price' => 0,
                'yearly_price' => 0,
                'max_servers' => 1,
                'max_sites_per_server' => 2,
                'max_deployments_per_month' => 50,
                'max_backups' => 3,
                'max_team_members' => 1,
                'max_storage_gb' => 1,
                'has_ssl_auto_renewal' => false,
                'has_priority_support' => false,
                'has_advanced_analytics' => false,
                'has_custom_domains' => true,
                'has_api_access' => false,
                'has_audit_logs' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Para profissionais e pequenas equipes',
                'price' => 29.00,
                'yearly_price' => 290.00, // ~17% desconto (2 meses grátis)
                'max_servers' => 5,
                'max_sites_per_server' => 10,
                'max_deployments_per_month' => 500,
                'max_backups' => 20,
                'max_team_members' => 5,
                'max_storage_gb' => 10,
                'has_ssl_auto_renewal' => true,
                'has_priority_support' => true,
                'has_advanced_analytics' => true,
                'has_custom_domains' => true,
                'has_api_access' => true,
                'has_audit_logs' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Para grandes equipes e empresas',
                'price' => 99.00,
                'yearly_price' => 990.00, // ~17% desconto
                'max_servers' => 50,
                'max_sites_per_server' => 50,
                'max_deployments_per_month' => 5000,
                'max_backups' => 100,
                'max_team_members' => 25,
                'max_storage_gb' => 100,
                'has_ssl_auto_renewal' => true,
                'has_priority_support' => true,
                'has_advanced_analytics' => true,
                'has_custom_domains' => true,
                'has_api_access' => true,
                'has_audit_logs' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
