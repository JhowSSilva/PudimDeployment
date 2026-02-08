<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-100">Activity Feed</h1>
                <p class="mt-1 text-sm text-neutral-400">Track all team activities and changes</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('activity.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Action Filter -->
            <div>
                <label for="action" class="block text-sm font-medium text-neutral-300 mb-2">Action</label>
                <select name="action" id="action" class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $action)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- User Filter -->
            <div>
                <label for="user_id" class="block text-sm font-medium text-neutral-300 mb-2">User</label>
                <select name="user_id" id="user_id" class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Subject Type Filter -->
            <div>
                <label for="subject_type" class="block text-sm font-medium text-neutral-300 mb-2">Resource</label>
                <select name="subject_type" id="subject_type" class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Resources</option>
                    @foreach($subjectTypes as $type)
                        <option value="{{ $type }}" {{ request('subject_type') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Button -->
            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter
                </x-button>
            </div>
        </form>

        @if(request()->hasAny(['action', 'user_id', 'subject_type']))
            <div class="mt-4">
                <x-button href="{{ route('activity.index') }}" variant="ghost" size="sm">
                    Clear Filters
                </x-button>
            </div>
        @endif
    </x-card>

    <!-- Activity Timeline -->
    @if($activities->isEmpty())
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-neutral-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-neutral-300 mb-2">No Activities Found</h3>
                <p class="text-neutral-400">{{ request()->hasAny(['action', 'user_id', 'subject_type']) ? 'Try adjusting your filters' : 'Activities will appear here as your team works' }}</p>
            </div>
        </x-card>
    @else
        <div class="space-y-4">
            @foreach($activities as $activity)
                <x-card class="hover:bg-neutral-800/50 transition">
                    <div class="flex items-start space-x-4">
                        <!-- Avatar -->
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                            {{ substr($activity->user?->name ?? 'S', 0, 1) }}
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm text-neutral-100">
                                    <span class="font-medium">{{ $activity->user?->name ?? 'System' }}</span>
                                    <span class="text-neutral-400 mx-2">·</span>
                                    <span class="text-neutral-400">{{ $activity->description }}</span>
                                </p>
                                <span class="text-xs text-neutral-500 whitespace-nowrap ml-4">
                                    {{ $activity->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <!-- Subject Info -->
                            <div class="flex items-center space-x-3 text-xs text-neutral-400 mt-2">
                                <span class="inline-flex items-center px-2 py-1 rounded bg-neutral-800 text-neutral-300">
                                    {{ $activity->subject_type }}
                                </span>
                                @if($activity->action)
                                    @php
                                        $actionColors = [
                                            'created' => 'bg-success-900/20 text-success-400',
                                            'updated' => 'bg-primary-900/20 text-primary-400',
                                            'deleted' => 'bg-error-900/20 text-error-400',
                                            'deployed' => 'bg-info-900/20 text-info-400',
                                        ];
                                        $color = $actionColors[$activity->action] ?? 'bg-neutral-700 text-neutral-300';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded {{ $color }}">
                                        {{ ucwords(str_replace('_', ' ', $activity->action)) }}
                                    </span>
                                @endif
                                @if($activity->ip_address)
                                    <span>
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                        </svg>
                                        {{ $activity->ip_address }}
                                    </span>
                                @endif
                            </div>

                            <!-- Properties (if any) -->
                            @if($activity->properties && count($activity->properties) > 0)
                                <details class="mt-3">
                                    <summary class="text-xs text-neutral-400 cursor-pointer hover:text-neutral-300">
                                        View Details
                                    </summary>
                                    <div class="mt-2 p-3 bg-neutral-800 rounded-lg text-xs">
                                        <pre class="text-neutral-300 overflow-x-auto">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </details>
                            @endif
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($activities->hasPages())
            <div class="mt-6">
                {{ $activities->links() }}
            </div>
        @endif
    @endif
</x-layout>
