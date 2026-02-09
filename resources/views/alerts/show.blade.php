<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-2">
            <a href="{{ route('alerts.index') }}" class="text-neutral-400 hover:text-neutral-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-neutral-100">Alert Details</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Alert Information -->
            <x-card>
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-neutral-100 mb-2">{{ $alert->title }}</h2>
                        <p class="text-neutral-400">{{ $alert->message }}</p>
                    </div>
                    @php
                        $statusColors = [
                            'open' => 'bg-error-900/20 text-error-400 ring-error-500/30',
                            'acknowledged' => 'bg-warning-900/20 text-warning-400 ring-warning-500/30',
                            'resolved' => 'bg-success-900/20 text-success-400 ring-success-500/30',
                        ];
                        $severityColors = [
                            'critical' => 'bg-error-900/20 text-error-400 ring-error-500/30',
                            'warning' => 'bg-warning-900/20 text-warning-400 ring-warning-500/30',
                            'info' => 'bg-primary-900/20 text-primary-400 ring-primary-500/30',
                        ];
                    @endphp
                    <div class="flex flex-col items-end space-y-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ring-1 {{ $statusColors[$alert->status] }}">
                            {{ ucfirst($alert->status) }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ring-1 {{ $severityColors[$alert->severity] }}">
                            {{ ucfirst($alert->severity) }}
                        </span>
                    </div>
                </div>

                <!-- Metrics -->
                <div class="grid grid-cols-2 gap-4 p-4 bg-neutral-800 rounded-lg">
                    <div>
                        <p class="text-sm text-neutral-400">Current Value</p>
                        <p class="text-2xl font-bold text-neutral-100">{{ number_format($alert->current_value, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-400">Threshold</p>
                        <p class="text-2xl font-bold text-neutral-100">{{ number_format($alert->threshold_value, 2) }}</p>
                    </div>
                </div>
            </x-card>

            <!-- Timeline -->
            <x-card>
                <h3 class="text-lg font-semibold text-neutral-100 mb-4">Timeline</h3>
                <div class="space-y-4">
                    <!-- Created -->
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-primary-900/30 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-100">Alert Created</p>
                            <p class="text-xs text-neutral-400 mt-1">{{ $alert->created_at->format('M d, Y H:i:s') }} ({{ $alert->created_at->diffForHumans() }})</p>
                        </div>
                    </div>

                    @if($alert->acknowledged_at)
                        <!-- Acknowledged -->
                        <div class="flex items-start border-l-2 border-neutral-700 pl-4 ml-5">
                            <div class="w-10 h-10 bg-warning-900/30 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-5 h-5 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-neutral-100">Acknowledged by {{ $alert->acknowledgedByUser?->name ?? 'System' }}</p>
                                <p class="text-xs text-neutral-400 mt-1">{{ $alert->acknowledged_at->format('M d, Y H:i:s') }} ({{ $alert->acknowledged_at->diffForHumans() }})</p>
                                @if($alert->acknowledgment_note)
                                    <div class="mt-2 p-3 bg-neutral-800 rounded-lg">
                                        <p class="text-sm text-neutral-300">{{ $alert->acknowledgment_note }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($alert->resolved_at)
                        <!-- Resolved -->
                        <div class="flex items-start border-l-2 border-neutral-700 pl-4 ml-5">
                            <div class="w-10 h-10 bg-success-900/30 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-neutral-100">Resolved</p>
                                <p class="text-xs text-neutral-400 mt-1">{{ $alert->resolved_at->format('M d, Y H:i:s') }} ({{ $alert->resolved_at->diffForHumans() }})</p>
                                @if($alert->resolution_note)
                                    <div class="mt-2 p-3 bg-neutral-800 rounded-lg">
                                        <p class="text-sm text-neutral-300">{{ $alert->resolution_note }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Actions -->
            @if($alert->isOpen() || $alert->status === 'acknowledged')
                <x-card>
                    <h3 class="text-lg font-semibold text-neutral-100 mb-4">Actions</h3>
                    
                    @if($alert->isOpen())
                        <!-- Acknowledge Form -->
                        <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST" class="mb-6">
                            @csrf
                            <label for="acknowledgment_note" class="block text-sm font-medium text-neutral-300 mb-2">
                                Acknowledge Alert
                            </label>
                            <textarea 
                                name="note" 
                                id="acknowledgment_note" 
                                rows="3" 
                                class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                                placeholder="Add a note (optional)..."></textarea>
                            @error('note')
                                <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                            @enderror
                            <x-button type="submit" variant="warning" class="w-full mt-3">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Acknowledge Alert
                            </x-button>
                        </form>
                    @endif

                    <!-- Resolve Form -->
                    <form action="{{ route('alerts.resolve', $alert) }}" method="POST">
                        @csrf
                        <label for="resolution_note" class="block text-sm font-medium text-neutral-300 mb-2">
                            Resolve Alert
                        </label>
                        <textarea 
                            name="note" 
                            id="resolution_note" 
                            rows="3" 
                            class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                            placeholder="Describe the resolution (optional)..."></textarea>
                        @error('note')
                            <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                        @enderror
                        <x-button type="submit" variant="success" class="w-full mt-3">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Resolve Alert
                        </x-button>
                    </form>
                </x-card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Related Resources -->
            <x-card>
                <h3 class="text-lg font-semibold text-neutral-100 mb-4">Related Resources</h3>
                <div class="space-y-3">
                    @if($alert->server)
                        <a href="{{ route('servers.show', $alert->server) }}" class="block p-3 bg-neutral-800 hover:bg-neutral-700 rounded-lg transition">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-primary-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-neutral-100">{{ $alert->server->name }}</p>
                                    <p class="text-xs text-neutral-400">Server</p>
                                </div>
                            </div>
                        </a>
                    @endif

                    @if($alert->site)
                        <a href="{{ route('sites.show', $alert->site) }}" class="block p-3 bg-neutral-800 hover:bg-neutral-700 rounded-lg transition">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-success-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-neutral-100">{{ $alert->site->domain }}</p>
                                    <p class="text-xs text-neutral-400">Site</p>
                                </div>
                            </div>
                        </a>
                    @endif

                    @if($alert->alertRule)
                        <div class="p-3 bg-neutral-800 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-warning-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-neutral-100">{{ $alert->alertRule->name }}</p>
                                    <p class="text-xs text-neutral-400">Alert Rule</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Quick Stats -->
            <x-card>
                <h3 class="text-lg font-semibold text-neutral-100 mb-4">Quick Stats</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-neutral-400">Created</span>
                        <span class="text-sm font-medium text-neutral-100">{{ $alert->created_at->diffForHumans() }}</span>
                    </div>
                    @if($alert->acknowledged_at)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-neutral-400">Acknowledged</span>
                            <span class="text-sm font-medium text-neutral-100">{{ $alert->acknowledged_at->diffForHumans() }}</span>
                        </div>
                    @endif
                    @if($alert->resolved_at)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-neutral-400">Resolved</span>
                            <span class="text-sm font-medium text-neutral-100">{{ $alert->resolved_at->diffForHumans() }}</span>
                        </div>
                    @endif
                    @if($alert->notification_sent)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-neutral-400">Notifications Sent</span>
                            <span class="text-sm font-medium text-neutral-100">{{ count($alert->notification_sent) }}</span>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Comments Section -->
            <x-card class="mt-6">
                <h3 class="text-lg font-semibold text-neutral-100 mb-6">Comentários</h3>
                
                <!-- Comment Form -->
                <div class="mb-6">
                    <x-comment-form 
                        commentable-type="App\Models\Alert" 
                        :commentable-id="$alert->id" 
                    />
                </div>

                <!-- Comments List -->
                <div id="comments-container" class="space-y-4">
                    <div class="text-center py-8 text-neutral-400">
                        <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="mt-2">Carregando comentários...</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <script>
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function loadComments(commentableType, commentableId) {
            fetch(`/comments/get?commentable_type=${commentableType}&commentable_id=${commentableId}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('comments-container');
                    
                    if (data.comments && data.comments.length > 0) {
                        container.innerHTML = '';
                        data.comments.forEach(comment => {
                            container.innerHTML += renderComment(comment);
                        });
                    } else {
                        container.innerHTML = `
                            <div class="text-center py-8 text-neutral-400">
                                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <p class="mt-2">Nenhum comentário ainda. Seja o primeiro!</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading comments:', error);
                });
        }

        function renderComment(comment, depth = 0) {
            const marginLeft = depth > 0 ? 'ml-12' : '';
            const replies = comment.replies ? comment.replies.map(reply => renderComment(reply, depth + 1)).join('') : '';
            
            return `
                <div class="comment-item ${marginLeft}" data-comment-id="${comment.id}">
                    <div class="flex gap-3 p-4 bg-neutral-800 rounded-lg border border-neutral-700 hover:border-neutral-600 transition">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold">
                                ${escapeHtml(comment.user.name.charAt(0).toUpperCase())}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-medium text-neutral-100">${escapeHtml(comment.user.name)}</span>
                                <span class="text-xs text-neutral-400">${escapeHtml(comment.time_since)}</span>
                                ${comment.is_edited ? '<span class="text-xs text-neutral-500 italic">(editado)</span>' : ''}
                            </div>
                            <div class="comment-body text-sm text-neutral-300 whitespace-pre-wrap">${escapeHtml(comment.body)}</div>
                        </div>
                    </div>
                    ${replies}
                </div>
            `;
        }

        // Load comments on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadComments('App\\\\Models\\\\Alert', {{ $alert->id }});
        });
    </script>
</x-layout>
