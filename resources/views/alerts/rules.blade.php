<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-100">Alert Rules</h1>
                <p class="mt-1 text-sm text-neutral-400">Configure automated monitoring and alerting rules</p>
            </div>
            <x-button href="{{ route('alerts.rules.create') }}" variant="primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Rule
            </x-button>
        </div>
    </div>

    @if($rules->isEmpty())
        <!-- Empty State -->
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-neutral-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <h3 class="text-lg font-medium text-neutral-300 mb-2">No Alert Rules Configured</h3>
                <p class="text-neutral-400 mb-6">Create your first alert rule to start monitoring your infrastructure</p>
                <x-button href="{{ route('alerts.rules.create') }}" variant="primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Your First Rule
                </x-button>
            </div>
        </x-card>
    @else
        <!-- Rules Grid -->
        <div class="grid grid-cols-1 gap-6">
            @foreach($rules as $rule)
                <x-card>
                    <div class="flex items-start justify-between">
                        <!-- Rule Info -->
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <h3 class="text-lg font-bold text-neutral-100">{{ $rule->name }}</h3>
                                
                                <!-- Active Status -->
                                @if($rule->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-900/20 text-success-400 ring-1 ring-success-500/30">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-700 text-neutral-400 ring-1 ring-neutral-600/30">
                                        Inactive
                                    </span>
                                @endif

                                <!-- Severity Badge -->
                                @php
                                    $severityColors = [
                                        'critical' => 'bg-error-900/20 text-error-400 ring-error-500/30',
                                        'warning' => 'bg-warning-900/20 text-warning-400 ring-warning-500/30',
                                        'info' => 'bg-primary-900/20 text-primary-400 ring-primary-500/30',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 {{ $severityColors[$rule->severity] }}">
                                    {{ ucfirst($rule->severity) }}
                                </span>
                            </div>

                            @if($rule->description)
                                <p class="text-sm text-neutral-400 mb-4">{{ $rule->description }}</p>
                            @endif

                            <!-- Rule Details -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-neutral-500 mb-1">Metric</p>
                                    <p class="text-sm font-medium text-neutral-200">{{ ucwords(str_replace('_', ' ', $rule->metric_type)) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-500 mb-1">Condition</p>
                                    <p class="text-sm font-medium text-neutral-200">
                                        {{ ucwords(str_replace('_', ' ', $rule->condition)) }} {{ number_format($rule->threshold, 2) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-500 mb-1">Duration</p>
                                    <p class="text-sm font-medium text-neutral-200">{{ $rule->duration ?? 0 }} min</p>
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-500 mb-1">Cooldown</p>
                                    <p class="text-sm font-medium text-neutral-200">{{ $rule->cooldown ?? 0 }}s</p>
                                </div>
                            </div>

                            <!-- Scope & Notifications -->
                            <div class="flex flex-wrap items-center gap-4 text-xs text-neutral-400">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                    </svg>
                                    <span>
                                        @if($rule->server)
                                            {{ $rule->server->name }}
                                        @else
                                            All Servers
                                        @endif
                                    </span>
                                </div>

                                @if($rule->channels && count($rule->channels) > 0)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                        <span>{{ implode(', ', array_map('ucfirst', $rule->channels)) }}</span>
                                    </div>
                                @endif

                                @if($rule->trigger_count > 0)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Triggered {{ $rule->trigger_count }} time{{ $rule->trigger_count === 1 ? '' : 's' }}</span>
                                        @if($rule->last_triggered_at)
                                            <span class="ml-1">({{ $rule->last_triggered_at->diffForHumans() }})</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 ml-6">
                            <!-- Toggle Active -->
                            <form action="{{ route('alerts.rules.toggle', $rule) }}" method="POST" class="inline">
                                @csrf
                                <x-button 
                                    type="submit" 
                                    variant="ghost" 
                                    size="sm"
                                    title="{{ $rule->is_active ? 'Disable Rule' : 'Enable Rule' }}">
                                    @if($rule->is_active)
                                        <svg class="w-5 h-5 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                </x-button>
                            </form>

                            <!-- Delete -->
                            <form 
                                action="{{ route('alerts.rules.destroy', $rule) }}" 
                                method="POST" 
                                class="inline"
                                onsubmit="return confirm('Are you sure you want to delete this alert rule?')">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="ghost" size="sm" title="Delete Rule">
                                    <svg class="w-5 h-5 text-error-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </x-button>
                            </form>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</x-layout>
