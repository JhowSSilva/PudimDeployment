<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $loadBalancer->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('scaling.load-balancers.edit', $loadBalancer) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('scaling.load-balancers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Load Balancer Overview</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $loadBalancer->status === 'active' ? 'bg-green-100 text-green-800' : ($loadBalancer->status === 'error' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($loadBalancer->status) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Protocol</p>
                            <p class="mt-1 text-sm text-gray-900">{{ strtoupper($loadBalancer->protocol) }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Algorithm</p>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $loadBalancer->algorithm)) }}</p>
                        </div>

                        @if($loadBalancer->ip_address)
                        <div>
                            <p class="text-sm font-medium text-gray-500">Endpoint</p>
                            <p class="mt-1 text-sm text-gray-900 font-mono">{{ $loadBalancer->ip_address }}:{{ $loadBalancer->port }}</p>
                        </div>
                        @endif
                    </div>

                    @if($loadBalancer->description)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-500">Description</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $loadBalancer->description }}</p>
                    </div>
                    @endif

                    <!-- Features -->
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if($loadBalancer->ssl_enabled)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                🔒 SSL Enabled
                            </span>
                        @endif
                        @if($loadBalancer->health_check_enabled)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                ✓ Health Checks
                            </span>
                        @endif
                        @if($loadBalancer->sticky_sessions)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                📌 Sticky Sessions
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Statistics -->
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Traffic Statistics</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-blue-600">Total Requests</p>
                            <p class="mt-2 text-3xl font-bold text-blue-900">{{ number_format($loadBalancer->total_requests) }}</p>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-red-600">Failed Requests</p>
                            <p class="mt-2 text-3xl font-bold text-red-900">{{ number_format($loadBalancer->failed_requests) }}</p>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-green-600">Success Rate</p>
                            <p class="mt-2 text-3xl font-bold text-green-900">{{ number_format($loadBalancer->success_rate, 1) }}%</p>
                        </div>

                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-yellow-600">Error Rate</p>
                            <p class="mt-2 text-3xl font-bold text-yellow-900">{{ number_format($loadBalancer->error_rate, 1) }}%</p>
                        </div>
                    </div>

                    @if($loadBalancer->last_health_check_at)
                    <p class="mt-4 text-sm text-gray-500">
                        Last health check: {{ $loadBalancer->last_health_check_at->diffForHumans() }}
                    </p>
                    @endif
                </div>
            </div>

            <!-- Server Pool -->
            @if($loadBalancer->serverPool)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Server Pool</h3>
                        <a href="{{ route('scaling.pools.show', $loadBalancer->serverPool) }}" class="text-sm text-blue-600 hover:text-blue-800">
                            View Pool →
                        </a>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ $loadBalancer->serverPool->name }}</h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $loadBalancer->serverPool->current_servers }} servers • 
                                    {{ ucfirst($loadBalancer->serverPool->environment) }} • 
                                    {{ ucfirst($loadBalancer->serverPool->status) }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $loadBalancer->serverPool->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($loadBalancer->serverPool->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Servers in Pool -->
                    @if($loadBalancer->serverPool->servers->count() > 0)
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Servers ({{ $loadBalancer->serverPool->servers->count() }})</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Server</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Weight</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($loadBalancer->serverPool->servers as $server)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $server->name }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $server->ip_address }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $server->pivot->weight }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $server->pivot->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $server->pivot->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Health Checks -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Health Check Configuration</h3>
                    
                    @if($loadBalancer->health_check_enabled)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Health Check Path</p>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $loadBalancer->health_check_path ?? 'N/A' }}</p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-500">Interval</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $loadBalancer->health_check_interval }} seconds</p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-500">Timeout</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $loadBalancer->health_check_timeout }} seconds</p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-500">Healthy Threshold</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $loadBalancer->healthy_threshold }} checks</p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-500">Unhealthy Threshold</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $loadBalancer->unhealthy_threshold }} checks</p>
                            </div>
                        </div>

                        <!-- Recent Health Checks -->
                        @if($loadBalancer->healthChecks->count() > 0)
                        <div class="mt-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Recent Health Checks ({{ $loadBalancer->healthChecks->count() }})</h4>
                            <div class="space-y-2">
                                @foreach($loadBalancer->healthChecks->take(5) as $check)
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $check->server->name ?? 'Unknown Server' }}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Last checked: {{ $check->last_checked_at ? $check->last_checked_at->diffForHumans() : 'Never' }}
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $check->status === 'healthy' ? 'bg-green-100 text-green-800' : ($check->status === 'unhealthy' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                                {{ ucfirst($check->status) }}
                                            </span>
                                            @if($check->response_time)
                                            <span class="text-xs text-gray-500">{{ $check->response_time }}ms</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">Health checks are disabled for this load balancer.</p>
                    @endif
                </div>
            </div>

            <!-- SSL Configuration -->
            @if($loadBalancer->ssl_enabled)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">SSL/TLS Configuration</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">SSL Status</p>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    ✓ SSL Enabled
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Certificate</p>
                            <div class="mt-1 p-3 bg-gray-50 rounded border border-gray-200">
                                <p class="text-xs text-gray-500 font-mono">{{ Str::limit($loadBalancer->ssl_certificate, 100) }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Private Key</p>
                            <div class="mt-1 p-3 bg-gray-50 rounded border border-gray-200">
                                <p class="text-xs text-gray-500">••••••••••••••••••••</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Session Configuration -->
            @if($loadBalancer->sticky_sessions)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Session Persistence</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Sticky Sessions</p>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    ✓ Enabled
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Session TTL</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $loadBalancer->session_ttl }} seconds ({{ number_format($loadBalancer->session_ttl / 60, 1) }} minutes)</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
