<?php

namespace App\Services;

use App\Models\Server;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingService
{
    /**
     * Calculate server costs based on provider pricing
     */
    public function calculateServerCosts(Server $server, Carbon $startDate, Carbon $endDate): array
    {
        try {
            $hours = $startDate->diffInHours($endDate);
            $days = ceil($hours / 24);

            // Get pricing based on provider and instance type
            $hourlyRate = $this->getHourlyRate($server);
            $monthlyRate = $hourlyRate * 730; // Average hours in a month

            $totalCost = $hourlyRate * $hours;

            // Calculate bandwidth costs
            $bandwidthCost = $this->calculateBandwidthCost($server, $startDate, $endDate);

            // Calculate storage costs
            $storageCost = $this->calculateStorageCost($server, $days);

            $totalWithExtras = $totalCost + $bandwidthCost + $storageCost;

            return [
                'success' => true,
                'server_id' => $server->id,
                'server_name' => $server->name,
                'period' => [
                    'start' => $startDate->toDateTimeString(),
                    'end' => $endDate->toDateTimeString(),
                    'hours' => $hours,
                    'days' => $days
                ],
                'costs' => [
                    'hourly_rate' => $hourlyRate,
                    'monthly_rate' => $monthlyRate,
                    'compute_cost' => round($totalCost, 2),
                    'bandwidth_cost' => round($bandwidthCost, 2),
                    'storage_cost' => round($storageCost, 2),
                    'total' => round($totalWithExtras, 2)
                ],
                'currency' => 'USD'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to calculate server costs', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get hourly rate for server
     */
    private function getHourlyRate(Server $server): float
    {
        // Base rates per provider (these should be stored in database or config)
        $baseRates = [
            'aws' => [
                't3.micro' => 0.0104,
                't3.small' => 0.0208,
                't3.medium' => 0.0416,
                't3.large' => 0.0832,
                't3.xlarge' => 0.1664,
            ],
            'digitalocean' => [
                's-1vcpu-1gb' => 0.00744,
                's-1vcpu-2gb' => 0.01488,
                's-2vcpu-2gb' => 0.02232,
                's-2vcpu-4gb' => 0.02976,
                's-4vcpu-8gb' => 0.0595,
            ],
            'azure' => [
                'Standard_B1s' => 0.0104,
                'Standard_B1ms' => 0.0207,
                'Standard_B2s' => 0.0416,
                'Standard_B2ms' => 0.0832,
            ],
            'gcp' => [
                'e2-micro' => 0.0084,
                'e2-small' => 0.0168,
                'e2-medium' => 0.0336,
                'e2-standard-2' => 0.067,
            ],
        ];

        $provider = strtolower($server->provider ?? 'custom');
        $instanceType = $server->instance_type ?? 'unknown';

        // Return rate from database or default rates
        if (isset($baseRates[$provider][$instanceType])) {
            return $baseRates[$provider][$instanceType];
        }

        // Fallback to custom rate if set
        return $server->custom_hourly_rate ?? 0.05;
    }

    /**
     * Calculate bandwidth costs
     */
    private function calculateBandwidthCost(Server $server, Carbon $startDate, Carbon $endDate): float
    {
        // Get bandwidth usage from metrics
        $bandwidthGB = DB::table('server_metrics')
            ->where('server_id', $server->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('bandwidth_out') / 1024 / 1024 / 1024; // Convert to GB

        // Cost per GB (varies by provider, typically $0.01-0.12 per GB)
        $costPerGB = match(strtolower($server->provider ?? 'custom')) {
            'aws' => 0.09,
            'digitalocean' => 0.01,
            'azure' => 0.087,
            'gcp' => 0.12,
            default => 0.05
        };

        // Usually first 1TB is free
        $freeGB = 1024;
        $billableGB = max(0, $bandwidthGB - $freeGB);

        return $billableGB * $costPerGB;
    }

    /**
     * Calculate storage costs
     */
    private function calculateStorageCost(Server $server, int $days): float
    {
        // Get storage size in GB
        $storageGB = $server->disk_size ?? 50;

        // Cost per GB-month
        $costPerGBMonth = match(strtolower($server->provider ?? 'custom')) {
            'aws' => 0.10,
            'digitalocean' => 0.15,
            'azure' => 0.05,
            'gcp' => 0.04,
            default => 0.10
        };

        // Calculate cost for the period
        $monthFraction = $days / 30;

        return $storageGB * $costPerGBMonth * $monthFraction;
    }

    /**
     * Generate invoice for a team
     */
    public function generateInvoice(Team $team, Carbon $month): array
    {
        try {
            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

            Log::info('Generating invoice', [
                'team_id' => $team->id,
                'period' => $month->format('Y-m')
            ]);

            // Get all servers for this team
            $servers = $team->servers;
            $serverCosts = [];
            $totalCost = 0;

            foreach ($servers as $server) {
                $cost = $this->calculateServerCosts($server, $startDate, $endDate);
                if ($cost['success']) {
                    $serverCosts[] = $cost;
                    $totalCost += $cost['costs']['total'];
                }
            }

            // Create invoice record
            $invoice = DB::table('invoices')->insertGetId([
                'team_id' => $team->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'period_start' => $startDate,
                'period_end' => $endDate,
                'subtotal' => round($totalCost, 2),
                'tax' => round($totalCost * 0.0, 2), // Configure tax rate as needed
                'total' => round($totalCost, 2),
                'currency' => 'USD',
                'status' => 'pending',
                'line_items' => json_encode($serverCosts),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('Invoice generated', [
                'team_id' => $team->id,
                'invoice_id' => $invoice,
                'total' => $totalCost
            ]);

            return [
                'success' => true,
                'invoice_id' => $invoice,
                'invoice_number' => $this->generateInvoiceNumber(),
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString()
                ],
                'total' => round($totalCost, 2),
                'currency' => 'USD',
                'line_items' => $serverCosts
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate invoice', [
                'team_id' => $team->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Get last invoice number for this month
        $lastInvoice = DB::table('invoices')
            ->where('invoice_number', 'like', "INV-{$year}{$month}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('INV-%s%s%04d', $year, $month, $newNumber);
    }

    /**
     * Track usage for billing
     */
    public function trackUsage(Server $server): array
    {
        try {
            // Get current metrics
            $ssh = new SSHConnectionService($server);

            // CPU usage
            $cpuResult = $ssh->execute("top -bn1 | grep 'Cpu(s)' | awk '{print \$2}' | cut -d'%' -f1");
            $cpuUsage = (float) trim($cpuResult['output']);

            // Memory usage
            $memResult = $ssh->execute("free -m | grep Mem | awk '{print \$3}'");
            $memoryUsed = (int) trim($memResult['output']);

            // Disk usage
            $diskResult = $ssh->execute("df -BG / | tail -1 | awk '{print \$3}' | sed 's/G//'");
            $diskUsed = (int) trim($diskResult['output']);

            // Network usage (bytes sent/received)
            $netResult = $ssh->execute("cat /proc/net/dev | grep -E '(eth0|ens|enp)' | head -1 | awk '{print \$2,\$10}'");
            $netData = explode(' ', trim($netResult['output']));
            $bytesReceived = (int) ($netData[0] ?? 0);
            $bytesSent = (int) ($netData[1] ?? 0);

            // Store usage metrics
            DB::table('usage_metrics')->insert([
                'server_id' => $server->id,
                'cpu_usage' => $cpuUsage,
                'memory_used_mb' => $memoryUsed,
                'disk_used_gb' => $diskUsed,
                'bandwidth_in' => $bytesReceived,
                'bandwidth_out' => $bytesSent,
                'recorded_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return [
                'success' => true,
                'metrics' => [
                    'cpu_usage' => $cpuUsage,
                    'memory_used_mb' => $memoryUsed,
                    'disk_used_gb' => $diskUsed,
                    'bandwidth_in' => $bytesReceived,
                    'bandwidth_out' => $bytesSent
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to track usage', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get usage summary for a period
     */
    public function getUsageSummary(Server $server, Carbon $startDate, Carbon $endDate): array
    {
        try {
            $metrics = DB::table('usage_metrics')
                ->where('server_id', $server->id)
                ->whereBetween('recorded_at', [$startDate, $endDate])
                ->get();

            if ($metrics->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No usage data available for this period'
                ];
            }

            $avgCpu = $metrics->avg('cpu_usage');
            $avgMemory = $metrics->avg('memory_used_mb');
            $avgDisk = $metrics->avg('disk_used_gb');
            $totalBandwidthIn = $metrics->sum('bandwidth_in');
            $totalBandwidthOut = $metrics->sum('bandwidth_out');

            return [
                'success' => true,
                'period' => [
                    'start' => $startDate->toDateTimeString(),
                    'end' => $endDate->toDateTimeString()
                ],
                'summary' => [
                    'avg_cpu_usage' => round($avgCpu, 2),
                    'avg_memory_mb' => round($avgMemory, 2),
                    'avg_disk_gb' => round($avgDisk, 2),
                    'total_bandwidth_in_gb' => round($totalBandwidthIn / 1024 / 1024 / 1024, 2),
                    'total_bandwidth_out_gb' => round($totalBandwidthOut / 1024 / 1024 / 1024, 2),
                    'sample_count' => $metrics->count()
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get usage summary', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Manage subscriptions for a team
     */
    public function manageSubscription(Team $team, string $action, ?array $data = null): array
    {
        try {
            Log::info('Managing subscription', [
                'team_id' => $team->id,
                'action' => $action
            ]);

            switch ($action) {
                case 'create':
                    return $this->createSubscription($team, $data);
                
                case 'cancel':
                    return $this->cancelSubscription($team);
                
                case 'upgrade':
                    return $this->upgradeSubscription($team, $data);
                
                case 'downgrade':
                    return $this->downgradeSubscription($team, $data);
                
                default:
                    throw new \InvalidArgumentException("Unknown action: {$action}");
            }

        } catch (\Exception $e) {
            Log::error('Failed to manage subscription', [
                'team_id' => $team->id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create new subscription
     */
    private function createSubscription(Team $team, ?array $data): array
    {
        $plan = $data['plan'] ?? 'basic';
        $billingCycle = $data['billing_cycle'] ?? 'monthly';

        DB::table('subscriptions')->insert([
            'team_id' => $team->id,
            'plan' => $plan,
            'billing_cycle' => $billingCycle,
            'status' => 'active',
            'started_at' => now(),
            'next_billing_date' => $billingCycle === 'monthly' ? now()->addMonth() : now()->addYear(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return [
            'success' => true,
            'message' => 'Subscription created successfully',
            'plan' => $plan,
            'billing_cycle' => $billingCycle
        ];
    }

    /**
     * Cancel subscription
     */
    private function cancelSubscription(Team $team): array
    {
        DB::table('subscriptions')
            ->where('team_id', $team->id)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'updated_at' => now()
            ]);

        return [
            'success' => true,
            'message' => 'Subscription cancelled successfully'
        ];
    }

    /**
     * Upgrade subscription
     */
    private function upgradeSubscription(Team $team, ?array $data): array
    {
        $newPlan = $data['plan'] ?? 'premium';

        DB::table('subscriptions')
            ->where('team_id', $team->id)
            ->update([
                'plan' => $newPlan,
                'updated_at' => now()
            ]);

        return [
            'success' => true,
            'message' => 'Subscription upgraded successfully',
            'new_plan' => $newPlan
        ];
    }

    /**
     * Downgrade subscription
     */
    private function downgradeSubscription(Team $team, ?array $data): array
    {
        $newPlan = $data['plan'] ?? 'basic';

        DB::table('subscriptions')
            ->where('team_id', $team->id)
            ->update([
                'plan' => $newPlan,
                'downgrade_at_end_of_period' => true,
                'updated_at' => now()
            ]);

        return [
            'success' => true,
            'message' => 'Subscription will be downgraded at the end of the billing period',
            'new_plan' => $newPlan
        ];
    }

    /**
     * Get cost forecast for next month
     */
    public function forecastCosts(Team $team): array
    {
        try {
            // Get current month usage
            $currentMonth = now();
            $servers = $team->servers;
            
            $totalForecast = 0;
            $serverForecasts = [];

            foreach ($servers as $server) {
                // Calculate current month cost
                $currentCost = $this->calculateServerCosts(
                    $server,
                    $currentMonth->copy()->startOfMonth(),
                    $currentMonth
                );

                // Extrapolate to full month
                $daysElapsed = $currentMonth->day;
                $totalDaysInMonth = $currentMonth->daysInMonth;
                $dailyAverage = $currentCost['costs']['total'] / $daysElapsed;
                $forecastedCost = $dailyAverage * $totalDaysInMonth;

                $serverForecasts[] = [
                    'server_id' => $server->id,
                    'server_name' => $server->name,
                    'current_cost' => $currentCost['costs']['total'],
                    'forecasted_cost' => round($forecastedCost, 2)
                ];

                $totalForecast += $forecastedCost;
            }

            return [
                'success' => true,
                'forecast_month' => $currentMonth->format('Y-m'),
                'total_forecast' => round($totalForecast, 2),
                'servers' => $serverForecasts,
                'currency' => 'USD'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to forecast costs', [
                'team_id' => $team->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
