<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $policy->name }}
            </h2>
            <div class="flex space-x-2">
                <form method="POST" action="{{ route('scaling.policies.toggle', $policy) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 {{ $policy->is_active ? 'bg-gray-600' : 'bg-green-600' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:opacity-90">
                        {{ $policy->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
                <a href="{{ route('scaling.policies.edit', $policy) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('scaling.policies.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
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
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Policy Overview</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $policy->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Type</p>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ strtoupper($policy->type) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Metric</p>
                            <p class="mt-1 text-sm text-gray-900 font-mono">{{ $policy->metric ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Cooldown</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $policy->cooldown_minutes }} minutes</p>
                        </div>
                    </div>

                    @if($policy->description)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-500">Description</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $policy->description }}</p>
                    </div>
                    @endif

                    <!-- Policy Summary -->
                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm font-medium text-blue-900">Policy Summary</p>
                        <p class="mt-1 text-sm text-blue-800">{{ $policy->summary }}</p>
                    </div>
                </div>

                <!-- Thresholds & Limits -->
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Scaling Configuration</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @if($policy->threshold_up)
                        <div class="bg-red-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-red-600">Scale Up Threshold</p>
                            <p class="mt-2 text-3xl font-bold text-red-900">{{ number_format($policy->threshold_up, 1) }}%</p>
                            <p class="mt-1 text-xs text-red-700">Add {{ $policy->scale_up_by }} server(s)</p>
                        </div>
                        @endif

                        @if($policy->threshold_down)
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-blue-600">Scale Down Threshold</p>
                            <p class="mt-2 text-3xl font-bold text-blue-900">{{ number_format($policy->threshold_down, 1) }}%</p>
                            <p class="mt-1 text-xs text-blue-700">Remove {{ $policy->scale_down_by }} server(s)</p>
                        </div>
                        @endif

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-gray-600">Server Limits</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $policy->min_servers }} - {{ $policy->max_servers }}</p>
                            <p class="mt-1 text-xs text-gray-700">Min / Max servers</p>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Evaluation Periods</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $policy->evaluation_periods }} period(s) × {{ $policy->period_duration }} seconds</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Cooldown Period</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $policy->cooldown_minutes }} minutes</p>
                            @if($policy->isInCooldown())
                                <p class="mt-1 text-xs text-yellow-600">
                                    ⏳ In cooldown (next scaling: {{ $policy->next_scaling_time->diffForHumans() }})
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Server Pool -->
            @if($policy->serverPool)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Associated Server Pool</h3>
                        <a href="{{ route('scaling.pools.show', $policy->serverPool) }}" class="text-sm text-blue-600 hover:text-blue-800">
                            View Pool →
                        </a>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ $policy->serverPool->name }}</h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $policy->serverPool->current_servers }} servers ({{ $policy->serverPool->min_servers }} - {{ $policy->serverPool->max_servers }}) • 
                                    {{ ucfirst($policy->serverPool->environment) }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $policy->serverPool->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($policy->serverPool->status) }}
                            </span>
                        </div>

                        <!-- Pool Health Status -->
                        @php
                            $health = $policy->serverPool->health_status;
                            $healthColor = match($health['status']) {
                                'healthy' => 'green',
                                'degraded' => 'yellow',
                                'unhealthy' => 'red',
                                default => 'gray'
                            };
                        @endphp

                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                <span>Pool Health</span>
                                <span>{{ number_format($health['percentage'], 1) }}% Healthy</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-{{ $healthColor }}-500 h-2 rounded-full" style="width: {{ $health['percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Schedule (if applicable) -->
            @if($policy->type === 'schedule' && $policy->schedule)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Scaling Schedule</h3>
                    
                    <div class="space-y-2">
                        @foreach($policy->schedule as $item)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-900">{{ $item['time'] ?? 'N/A' }}</span>
                            </div>
                            <span class="text-sm text-gray-600">{{ $item['servers'] ?? 0 }} servers</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Activity History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Activity History</h3>
                    
                    <div class="space-y-4">
                        @if($policy->last_scaled_at)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Last Scaled</p>
                                <p class="text-sm text-gray-500">{{ $policy->last_scaled_at->format('M d, Y H:i:s') }}</p>
                                <p class="text-xs text-gray-400">{{ $policy->last_scaled_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endif

                        @if($policy->last_triggered_at)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Last Triggered</p>
                                <p class="text-sm text-gray-500">{{ $policy->last_triggered_at->format('M d, Y H:i:s') }}</p>
                                <p class="text-xs text-gray-400">{{ $policy->last_triggered_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endif

                        @if(!$policy->last_scaled_at && !$policy->last_triggered_at)
                        <p class="text-sm text-gray-500 text-center py-4">No activity recorded yet</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
