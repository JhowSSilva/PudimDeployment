<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-100">Alerts</h1>
                <p class="mt-1 text-sm text-neutral-400">Monitor and manage system alerts</p>
            </div>
            <x-button href="{{ route('alerts.rules') }}" variant="primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Manage Rules
            </x-button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-card>
            <div class="flex items-center">
                <div class="w-12 h-12 bg-primary-900/30 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-neutral-400">Total Alerts</p>
                    <p class="text-2xl font-bold text-neutral-100">{{ $summary['total'] ?? 0 }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="w-12 h-12 bg-error-900/30 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-error-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-neutral-400">Critical</p>
                    <p class="text-2xl font-bold text-error-400">{{ $summary['critical'] ?? 0 }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="w-12 h-12 bg-warning-900/30 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-neutral-400">Open</p>
                    <p class="text-2xl font-bold text-warning-400">{{ $summary['open'] ?? 0 }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="w-12 h-12 bg-success-900/30 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-neutral-400">Resolved</p>
                    <p class="text-2xl font-bold text-success-400">{{ $summary['resolved'] ?? 0 }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Filters -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('alerts.index') }}" class="flex items-end space-x-4">
            <div class="flex-1">
                <label for="status" class="block text-sm font-medium text-neutral-300 mb-2">Status</label>
                <select name="status" id="status" class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="acknowledged" {{ request('status') === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
            </div>

            <div class="flex-1">
                <label for="severity" class="block text-sm font-medium text-neutral-300 mb-2">Severity</label>
                <select name="severity" id="severity" class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Severities</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Info</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <x-button type="submit" variant="primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter
                </x-button>
                @if(request()->hasAny(['status', 'severity']))
                    <x-button href="{{ route('alerts.index') }}" variant="ghost">
                        Clear
                    </x-button>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Alerts List -->
    @if($alerts->isEmpty())
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-neutral-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-neutral-300 mb-2">No Alerts Found</h3>
                <p class="text-neutral-400">{{ request()->hasAny(['status', 'severity']) ? 'Try adjusting your filters' : 'All systems are healthy' }}</p>
            </div>
        </x-card>
    @else
        <x-card padding="false">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-neutral-800 border-b border-neutral-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Alert</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Resource</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Severity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-neutral-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800">
                        @foreach($alerts as $alert)
                            <tr class="hover:bg-neutral-800/50 transition">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-neutral-100">{{ $alert->title }}</div>
                                    <div class="text-sm text-neutral-400 truncate max-w-md">{{ $alert->message }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-neutral-300">
                                        @if($alert->server)
                                            <a href="{{ route('servers.show', $alert->server) }}" class="hover:text-primary-400">
                                                {{ $alert->server->name }}
                                            </a>
                                        @elseif($alert->site)
                                            <a href="{{ route('sites.show', $alert->site) }}" class="hover:text-primary-400">
                                                {{ $alert->site->domain }}
                                            </a>
                                        @else
                                            <span class="text-neutral-500">N/A</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $severityColors = [
                                            'critical' => 'bg-error-900/20 text-error-400 ring-error-500/30',
                                            'warning' => 'bg-warning-900/20 text-warning-400 ring-warning-500/30',
                                            'info' => 'bg-primary-900/20 text-primary-400 ring-primary-500/30',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 {{ $severityColors[$alert->severity] ?? $severityColors['info'] }}">
                                        {{ ucfirst($alert->severity) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'open' => 'bg-error-900/20 text-error-400 ring-error-500/30',
                                            'acknowledged' => 'bg-warning-900/20 text-warning-400 ring-warning-500/30',
                                            'resolved' => 'bg-success-900/20 text-success-400 ring-success-500/30',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 {{ $statusColors[$alert->status] ?? $statusColors['open'] }}">
                                        {{ ucfirst($alert->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-neutral-400">
                                    {{ $alert->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <x-button href="{{ route('alerts.show', $alert) }}" variant="ghost" size="sm">
                                        View
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Pagination -->
        @if($alerts->hasPages())
            <div class="mt-6">
                {{ $alerts->links() }}
            </div>
        @endif
    @endif
</x-layout>
