<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $pool->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('scaling.pools.edit', $pool) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('scaling.pools.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
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
            
            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pool Overview</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $pool->status === 'active' ? 'bg-green-100 text-green-800' : ($pool->status === 'scaling' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($pool->status) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Environment</p>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($pool->environment) }}</p>
                        </div>

                        @if($pool->region)
                        <div>
                            <p class="text-sm font-medium text-gray-500">Region</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $pool->region }}</p>
                        </div>
                        @endif

                        <div>
                            <p class="text-sm font-medium text-gray-500">Auto-healing</p>
                            <p class="mt-1">
                                @if($pool->auto_healing)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Enabled</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Disabled</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($pool->description)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-500">Description</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $pool->description }}</p>
                    </div>
                    @endif
                </div>

                <!-- Scaling Metrics -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Scaling Metrics</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-blue-600">Current Servers</p>
                            <p class="mt-2 text-3xl font-bold text-blue-900">{{ $pool->current_servers }}</p>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-green-600">Desired Servers</p>
                            <p class="mt-2 text-3xl font-bold text-green-900">{{ $pool->desired_servers }}</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-gray-600">Min / Max</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $pool->min_servers }} / {{ $pool->max_servers }}</p>
                        </div>

                        <div class="bg-purple-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-purple-600">Scale Status</p>
                            <p class="mt-2 text-lg font-bold text-purple-900">{{ ucfirst(str_replace('_', ' ', $pool->scale_status)) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Health Status -->
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Health Status</h3>
                    
                    @php
                        $health = $pool->health_status;
                        $healthColor = match($health['status']) {
                            'healthy' => 'green',
                            'degraded' => 'yellow',
                            'unhealthy' => 'red',
                            default => 'gray'
                        };
                    @endphp

                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <div class="relative pt-1">
                                <div class="flex mb-2 items-center justify-between">
                                    <div>
                                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-{{ $healthColor }}-600 bg-{{ $healthColor }}-200">
                                            {{ ucfirst($health['status']) }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-semibold inline-block text-{{ $healthColor }}-600">
                                            {{ number_format($health['percentage'], 1) }}% Healthy
                                        </span>
                                    </div>
                                </div>
                                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-{{ $healthColor }}-200">
                                    <div style="width:{{ $health['percentage'] }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-{{ $healthColor }}-500"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold text-green-600">{{ $health['healthy'] }}</span> healthy,
                            <span class="font-semibold text-red-600">{{ $health['unhealthy'] }}</span> unhealthy
                            of {{ $health['total'] }} total
                        </div>
                    </div>

                    <p class="mt-2 text-sm text-gray-500">
                        Health checks run every {{ $pool->health_check_interval }} seconds
                    </p>
                </div>
            </div>

            <!-- Servers in Pool -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Servers in Pool ({{ $pool->servers->count() }})</h3>
                    </div>

                    @if($pool->servers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Server</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pool->servers as $server)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $server->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $server->ip_address }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $server->pivot->weight }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $server->pivot->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $server->pivot->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $server->pivot->joined_at ? \Carbon\Carbon::parse($server->pivot->joined_at)->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <form method="POST" action="{{ route('scaling.pools.servers.remove', $pool) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="server_id" value="{{ $server->id }}">
                                                <button type="submit" onclick="return confirm('Remove this server from the pool?')" class="text-red-600 hover:text-red-900">
                                                    Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">No servers in this pool</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Scaling Policies -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Scaling Policies ({{ $pool->scalingPolicies->count() }})</h3>
                        <a href="{{ route('scaling.policies.create', ['pool' => $pool->id]) }}" class="text-sm text-blue-600 hover:text-blue-800">
                            + Add Policy
                        </a>
                    </div>

                    @if($pool->scalingPolicies->count() > 0)
                        <div class="space-y-3">
                            @foreach($pool->scalingPolicies as $policy)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900">{{ $policy->name }}</h4>
                                        <p class="text-sm text-gray-500 mt-1">{{ $policy->summary }}</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $policy->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <a href="{{ route('scaling.policies.show', $policy) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">No scaling policies configured</p>
                    @endif
                </div>
            </div>

            <!-- Load Balancers -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Load Balancers ({{ $pool->loadBalancers->count() }})</h3>
                        <a href="{{ route('scaling.load-balancers.create', ['pool' => $pool->id]) }}" class="text-sm text-blue-600 hover:text-blue-800">
                            + Add Load Balancer
                        </a>
                    </div>

                    @if($pool->loadBalancers->count() > 0)
                        <div class="space-y-3">
                            @foreach($pool->loadBalancers as $lb)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900">{{ $lb->name }}</h4>
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ strtoupper($lb->protocol) }} • {{ ucfirst(str_replace('_', ' ', $lb->algorithm)) }} • 
                                            {{ number_format($lb->success_rate, 1) }}% success rate
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $lb->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($lb->status) }}
                                        </span>
                                        <a href="{{ route('scaling.load-balancers.show', $lb) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">No load balancers configured</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
