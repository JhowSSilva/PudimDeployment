<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-100">Monitoring & Metrics</h1>
                <p class="mt-1 text-sm text-neutral-400">Monitor your servers performance and health in real-time</p>
            </div>
            <div class="flex items-center space-x-3">
                <x-button variant="ghost" size="sm" id="refresh-metrics">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span class="ml-2">Refresh</span>
                </x-button>
            </div>
        </div>
    </div>

    @if($servers->isEmpty())
        <!-- Empty State -->
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-neutral-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-neutral-300 mb-2">No Servers to Monitor</h3>
                <p class="text-neutral-400 mb-6">Create a server to start tracking metrics</p>
                <x-button href="{{ route('servers.create') }}" variant="primary">
                    Create Server
                </x-button>
            </div>
        </x-card>
    @else
        <!-- Servers Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
            @foreach($servers as $server)
                @php
                    $metrics = $serverMetrics[$server->id] ?? [];
                    $cpuCurrent = $metrics['cpu']['current'] ?? 0;
                    $memoryCurrent = $metrics['memory']['current'] ?? 0;
                    $diskCurrent = $metrics['disk']['current'] ?? 0;
                    
                    // Determine health status
                    $criticalCount = 0;
                    $warningCount = 0;
                    
                    if ($cpuCurrent >= 90 || $memoryCurrent >= 90 || $diskCurrent >= 85) {
                        $criticalCount++;
                    } elseif ($cpuCurrent >= 75 || $memoryCurrent >= 75 || $diskCurrent >= 70) {
                        $warningCount++;
                    }
                    
                    $healthStatus = $criticalCount > 0 ? 'critical' : ($warningCount > 0 ? 'warning' : 'healthy');
                    $healthBadgeColors = [
                        'healthy' => 'bg-success-900/20 text-success-400 ring-success-500/30',
                        'warning' => 'bg-warning-900/20 text-warning-400 ring-warning-500/30',
                        'critical' => 'bg-error-900/20 text-error-400 ring-error-500/30',
                    ];
                    $healthBadgeText = [
                        'healthy' => 'Healthy',
                        'warning' => 'Warning',
                        'critical' => 'Critical',
                    ];
                @endphp
                
                <x-card padding="false" class="group hover:scale-[1.02] transition-transform">
                    <div class="p-6">
                        <!-- Server Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-neutral-100">{{ $server->name }}</h3>
                                <p class="text-sm text-neutral-400 mt-1">{{ $server->ip_address }}</p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ring-1 {{ $healthBadgeColors[$healthStatus] }}">
                                {{ $healthBadgeText[$healthStatus] }}
                            </span>
                        </div>

                        <!-- Metrics Grid -->
                        <div class="space-y-4 mb-4">
                            <!-- CPU -->
                            <div>
                                <div class="flex items-center justify-between text-xs text-neutral-400 mb-1">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                        </svg>
                                        CPU
                                    </span>
                                    <span class="font-medium">{{ number_format($cpuCurrent, 1) }}%</span>
                                </div>
                                <div class="w-full bg-neutral-700 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $cpuCurrent >= 90 ? 'bg-error-500' : ($cpuCurrent >= 75 ? 'bg-warning-500' : 'bg-success-500') }} h-full rounded-full transition-all duration-500" 
                                         style="width: {{ min($cpuCurrent, 100) }}%"></div>
                                </div>
                            </div>

                            <!-- Memory -->
                            <div>
                                <div class="flex items-center justify-between text-xs text-neutral-400 mb-1">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                        </svg>
                                        Memory
                                    </span>
                                    <span class="font-medium">{{ number_format($memoryCurrent, 1) }}%</span>
                                </div>
                                <div class="w-full bg-neutral-700 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $memoryCurrent >= 90 ? 'bg-error-500' : ($memoryCurrent >= 75 ? 'bg-warning-500' : 'bg-primary-500') }} h-full rounded-full transition-all duration-500" 
                                         style="width: {{ min($memoryCurrent, 100) }}%"></div>
                                </div>
                            </div>

                            <!-- Disk -->
                            <div>
                                <div class="flex items-center justify-between text-xs text-neutral-400 mb-1">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                        </svg>
                                        Disk
                                    </span>
                                    <span class="font-medium">{{ number_format($diskCurrent, 1) }}%</span>
                                </div>
                                <div class="w-full bg-neutral-700 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $diskCurrent >= 85 ? 'bg-error-500' : ($diskCurrent >= 70 ? 'bg-warning-500' : 'bg-info-500') }} h-full rounded-full transition-all duration-500" 
                                         style="width: {{ min($diskCurrent, 100) }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <x-button 
                            href="{{ route('monitoring.show', $server) }}" 
                            variant="secondary" 
                            class="w-full justify-center mt-4">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            View Details
                        </x-button>
                    </div>
                </x-card>
            @endforeach
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-card>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-primary-900/30 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-neutral-100">Alerts & Rules</h3>
                        <p class="text-sm text-neutral-400">Configure monitoring alerts</p>
                    </div>
                    <x-button href="{{ route('alerts.rules') }}" variant="ghost" size="sm">
                        Configure
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </x-button>
                </div>
            </x-card>

            <x-card>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-success-900/30 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-neutral-100">Uptime Monitoring</h3>
                        <p class="text-sm text-neutral-400">Track website availability</p>
                    </div>
                    <x-button href="#" variant="ghost" size="sm">
                        View Status
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </x-button>
                </div>
            </x-card>
        </div>
    @endif

    @push('scripts')
    <script>
        // Auto-refresh metrics every 30 seconds
        let refreshInterval;
        
        function startAutoRefresh() {
            refreshInterval = setInterval(() => {
                location.reload();
            }, 30000);
        }
        
        document.getElementById('refresh-metrics')?.addEventListener('click', () => {
            location.reload();
        });
        
        // Start auto-refresh
        startAutoRefresh();
        
        // Clear interval on page unload
        window.addEventListener('beforeunload', () => {
            clearInterval(refreshInterval);
        });
    </script>
    @endpush
</x-layout>
