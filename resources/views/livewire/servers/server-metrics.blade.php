<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-neutral-900">Server Metrics</h3>
        <button wire:click="refresh" class="text-blue-600 hover:text-blue-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </button>
    </div>

    @if($loading)
        <div class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
    @elseif(isset($metrics['error']))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ $metrics['error'] }}
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- CPU Usage -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-blue-900">CPU Usage</span>
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-blue-900">
                    {{ $metrics['cpu_usage'] ?? 'N/A' }}%
                </div>
            </div>

            <!-- Memory Usage -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-green-900">Memory</span>
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-green-900">
                    {{ $metrics['memory_usage'] ?? 'N/A' }}%
                </div>
            </div>

            <!-- Disk Usage -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-yellow-900">Disk</span>
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-yellow-900">
                    {{ $metrics['disk_usage'] ?? 'N/A' }}%
                </div>
            </div>

            <!-- Load Average -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-purple-900">Load Avg</span>
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-purple-900">
                    {{ $metrics['load_average'] ?? 'N/A' }}
                </div>
            </div>
        </div>

        @if(isset($metrics['active_connections']))
        <div class="mt-4 pt-4 border-t border-neutral-200">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-neutral-600">Active Connections:</span>
                    <span class="font-semibold ml-2">{{ $metrics['active_connections'] }}</span>
                </div>
                <div>
                    <span class="text-neutral-600">Uptime:</span>
                    <span class="font-semibold ml-2">{{ $metrics['uptime'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-neutral-600">Network In:</span>
                    <span class="font-semibold ml-2">{{ $metrics['network_in'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-neutral-600">Network Out:</span>
                    <span class="font-semibold ml-2">{{ $metrics['network_out'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
