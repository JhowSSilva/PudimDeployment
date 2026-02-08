<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Load Balancers') }}
            </h2>
            <a href="{{ route('scaling.load-balancers.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Load Balancer
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if($loadBalancers->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No load balancers yet</h3>
                        <p class="mt-2 text-sm text-gray-500">Get started by creating your first load balancer to distribute traffic across your server pools.</p>
                        <div class="mt-6">
                            <a href="{{ route('scaling.load-balancers.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create Load Balancer
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($loadBalancers as $lb)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $lb->name }}</h3>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $lb->status === 'active' ? 'bg-green-100 text-green-800' : ($lb->status === 'error' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($lb->status) }}
                                </span>
                            </div>

                            <!-- Description -->
                            @if($lb->description)
                            <p class="text-sm text-gray-600 mb-4">{{ Str::limit($lb->description, 100) }}</p>
                            @endif

                            <!-- Info Grid -->
                            <div class="space-y-3 mb-4">
                                <!-- Server Pool -->
                                @if($lb->serverPool)
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <span class="text-gray-600">Pool: <span class="font-medium text-gray-900">{{ $lb->serverPool->name }}</span></span>
                                </div>
                                @endif

                                <!-- Protocol & Algorithm -->
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span class="text-gray-600">{{ strtoupper($lb->protocol) }} • {{ ucfirst(str_replace('_', ' ', $lb->algorithm)) }}</span>
                                </div>

                                <!-- Endpoint -->
                                @if($lb->ip_address && $lb->port)
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                    </svg>
                                    <span class="text-gray-600 font-mono text-xs">{{ $lb->ip_address }}:{{ $lb->port }}</span>
                                </div>
                                @endif

                                <!-- SSL Badge -->
                                @if($lb->ssl_enabled)
                                <div class="inline-flex">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        🔒 SSL Enabled
                                    </span>
                                </div>
                                @endif
                            </div>

                            <!-- Stats -->
                            <div class="border-t border-gray-200 pt-4 mb-4">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-xs text-gray-500">Total Requests</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ number_format($lb->total_requests) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Success Rate</p>
                                        <p class="text-lg font-semibold {{ $lb->success_rate >= 95 ? 'text-green-600' : ($lb->success_rate >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ number_format($lb->success_rate, 1) }}%
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                                @if($lb->health_check_enabled)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    ✓ Health Checks
                                </span>
                                @else
                                <span></span>
                                @endif
                                
                                <a href="{{ route('scaling.load-balancers.show', $lb) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                    View →
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $loadBalancers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
