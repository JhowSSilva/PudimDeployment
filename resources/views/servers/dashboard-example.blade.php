{{-- 
    Exemplo de Dashboard do Servidor usando os novos componentes Livewire
    
    Adicione este conteúdo em uma view de servidor existente
    ou crie uma nova view em resources/views/servers/dashboard.blade.php
--}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $server->name }} - Dashboard
            </h2>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    {{ $server->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($server->status) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Server Metrics Card --}}
            <div class="mb-6">
                <livewire:servers.server-metrics :server="$server" />
            </div>

            {{-- Row with Performance Chart and Security Alerts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                
                {{-- Performance Chart --}}
                <livewire:servers.performance-chart :server="$server" />
                
                {{-- Security Alerts --}}
                <livewire:servers.security-alerts :server="$server" />
                
            </div>

            {{-- Cost Forecast (if user has access) --}}
            @if(auth()->user()->currentTeam)
                <div class="mb-6">
                    <livewire:billing.cost-forecast :team="auth()->user()->currentTeam" />
                </div>
            @endif

            {{-- Additional Server Information --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Server Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <span class="text-sm text-gray-600">IP Address:</span>
                        <p class="font-semibold">{{ $server->ip_address }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Provider:</span>
                        <p class="font-semibold">{{ $server->cloud_provider }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Region:</span>
                        <p class="font-semibold">{{ $server->region }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">PHP Version:</span>
                        <p class="font-semibold">{{ $server->php_version ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Database:</span>
                        <p class="font-semibold">{{ $server->database_type ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Created:</span>
                        <p class="font-semibold">{{ $server->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Quick Actions</h4>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('servers.firewall', $server) }}" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            🔒 Firewall Settings
                        </a>
                        <a href="{{ route('servers.performance', $server) }}" 
                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            📊 Performance
                        </a>
                        <a href="{{ route('servers.databases', $server) }}" 
                           class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            💾 Databases
                        </a>
                        <a href="{{ route('servers.deployments', $server) }}" 
                           class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            🚀 Deployments
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Scripts for real-time updates (opcional) --}}
    @push('scripts')
    <script>
        // Auto-refresh metrics every 30 seconds
        setInterval(() => {
            Livewire.dispatch('refreshMetrics');
        }, 30000);
    </script>
    @endpush
</x-app-layout>
