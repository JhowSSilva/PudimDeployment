<x-layout>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('servers.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                ← Voltar para servidores
            </a>
            <div class="mt-2 md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-neutral-900">{{ $server->name }}</h1>
                    <p class="mt-2 text-sm text-neutral-700">{{ $server->ip_address }}</p>
                </div>
                <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
                    <a href="{{ route('servers.sites.create', $server) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Novo Site
                    </a>
                    <a href="{{ route('servers.edit', $server) }}" class="inline-flex items-center px-4 py-2 border border-neutral-300 rounded-md shadow-sm text-sm font-medium text-neutral-700 bg-white hover:bg-neutral-50">
                        Editar
                    </a>
                </div>
            </div>
        </div>

        <!-- Server Info & Metrics -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
            <div class="lg:col-span-2">
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-neutral-900 mb-4">Informações do Servidor</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Endereço IP</dt>
                                <dd class="mt-1 text-sm text-neutral-900">{{ $server->ip_address }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($server->status === 'online') bg-green-100 text-green-800
                                        @elseif($server->status === 'offline') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst($server->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Porta SSH</dt>
                                <dd class="mt-1 text-sm text-neutral-900">{{ $server->ssh_port }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Usuário SSH</dt>
                                <dd class="mt-1 text-sm text-neutral-900">{{ $server->ssh_user }}</dd>
                            </div>
                            @if($server->os_type)
                                <div>
                                    <dt class="text-sm font-medium text-neutral-500">Sistema Operacional</dt>
                                    <dd class="mt-1 text-sm text-neutral-900">{{ $server->os_type }} {{ $server->os_version }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Autenticação</dt>
                                <dd class="mt-1 text-sm text-neutral-900">{{ $server->auth_type === 'password' ? 'Senha' : 'Chave SSH' }}</dd>
                            </div>
                            @if($server->last_ping_at)
                                <div>
                                    <dt class="text-sm font-medium text-neutral-500">Último Ping</dt>
                                    <dd class="mt-1 text-sm text-neutral-900">{{ $server->last_ping_at->diffForHumans() }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Criado em</dt>
                                <dd class="mt-1 text-sm text-neutral-900">{{ $server->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Latest Metrics -->
            <div>
                @php
                    $metric = $server->latestMetric();
                @endphp
                @if($metric)
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-neutral-900 mb-4">Métricas Atuais</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-neutral-700">CPU</span>
                                        <span class="text-sm font-semibold text-neutral-900">{{ number_format($metric->cpu_usage, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-neutral-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $metric->cpu_usage > 80 ? 'bg-red-600' : ($metric->cpu_usage > 60 ? 'bg-yellow-600' : 'bg-green-600') }}" 
                                             style="width: {{ min($metric->cpu_usage, 100) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-neutral-700">RAM</span>
                                        <span class="text-sm font-semibold text-neutral-900">{{ number_format($metric->memory_usage_percentage, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-neutral-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $metric->memory_usage_percentage > 80 ? 'bg-red-600' : ($metric->memory_usage_percentage > 60 ? 'bg-yellow-600' : 'bg-green-600') }}" 
                                             style="width: {{ min($metric->memory_usage_percentage, 100) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-neutral-700">Disco</span>
                                        <span class="text-sm font-semibold text-neutral-900">{{ number_format($metric->disk_usage_percentage, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-neutral-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $metric->disk_usage_percentage > 80 ? 'bg-red-600' : ($metric->disk_usage_percentage > 60 ? 'bg-yellow-600' : 'bg-green-600') }}" 
                                             style="width: {{ min($metric->disk_usage_percentage, 100) }}%"></div>
                                    </div>
                                </div>
                                <div class="pt-4 border-t border-neutral-200">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-neutral-500">Uptime</span>
                                        <span class="font-medium text-neutral-900">{{ $metric->uptime_human }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6 text-center">
                            <p class="text-sm text-neutral-500">Nenhuma métrica disponível</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sites -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium leading-6 text-neutral-900">Sites</h3>
                    <a href="{{ route('servers.sites.create', $server) }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                        + Adicionar Site
                    </a>
                </div>

                @if($server->sites->count() > 0)
                    <div class="overflow-hidden">
                        <ul role="list" class="divide-y divide-neutral-200">
                            @foreach($server->sites as $site)
                                <li class="py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-neutral-900 truncate">{{ $site->name }}</p>
                                            <p class="text-sm text-neutral-500 truncate">{{ $site->domain }}</p>
                                        </div>
                                        <div class="ml-4 flex-shrink-0 flex items-center space-x-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $site->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-neutral-100 text-neutral-800' }}">
                                                {{ ucfirst($site->status) }}
                                            </span>
                                            <a href="{{ route('sites.show', $site) }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                                                Ver →
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-sm text-neutral-500">Nenhum site configurado neste servidor.</p>
                @endif
            </div>
        </div>

        <!-- Comments Section -->
        <div class="mt-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-neutral-900 mb-6">Comentários</h3>
                    
                    <!-- Comment Form -->
                    <div class="mb-6">
                        <x-comment-form 
                            commentable-type="App\Models\Server" 
                            :commentable-id="$server->id" 
                        />
                    </div>

                    <!-- Comments List -->
                    <div id="comments-container" class="space-y-4">
                        <div class="text-center py-8 text-neutral-500">
                            <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <p class="mt-2">Carregando comentários...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
                            <div class="text-center py-8 text-neutral-500">
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
                    <div class="flex gap-3 p-4 bg-white rounded-lg border border-neutral-200 hover:border-neutral-300 transition">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold">
                                ${comment.user.name.charAt(0).toUpperCase()}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-medium text-neutral-900">${comment.user.name}</span>
                                <span class="text-xs text-neutral-500">${comment.time_since}</span>
                                ${comment.is_edited ? '<span class="text-xs text-neutral-400 italic">(editado)</span>' : ''}
                            </div>
                            <div class="comment-body text-sm text-neutral-700 whitespace-pre-wrap">${comment.body}</div>
                        </div>
                    </div>
                    ${replies}
                </div>
            `;
        }

        // Load comments on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadComments('App\\\\Models\\\\Server', {{ $server->id }});
        });
    </script>
</x-layout>
