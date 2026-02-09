<x-layout>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('sites.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                ← Voltar para sites
            </a>
            <div class="mt-2 md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-neutral-100">{{ $site->name }}</h1>
                    <p class="mt-2 text-sm text-neutral-300">{{ $site->domain }}</p>
                </div>
                <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
                    <a href="{{ route('sites.edit', $site) }}" class="inline-flex items-center px-4 py-2 border border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-300 bg-neutral-800 hover:bg-neutral-700">
                        Editar
                    </a>
                    <form action="{{ route('sites.destroy', $site) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja deletar este site?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-error-600 rounded-md shadow-sm text-sm font-medium text-error-400 bg-neutral-800 hover:bg-error-900/30">
                            Deletar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Informações Principais -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Info Card -->
                <div class="bg-neutral-800 shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-neutral-100 mb-4">Informações do Site</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Servidor</dt>
                                <dd class="mt-1 text-sm text-neutral-100">
                                    <a href="{{ route('servers.show', $site->server) }}" class="text-primary-600 hover:text-primary-500">
                                        {{ $site->server->name }}
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($site->status === 'active') bg-success-900/30 text-success-400
                                        @elseif($site->status === 'inactive') bg-neutral-700 text-neutral-800
                                        @else bg-warning-900/30 text-warning-400
                                        @endif">
                                        {{ ucfirst($site->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Domínio</dt>
                                <dd class="mt-1 text-sm text-neutral-100">
                                    <a href="http://{{ $site->domain }}" target="_blank" class="text-primary-600 hover:text-primary-500">
                                        {{ $site->domain }} ↗
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Versão PHP</dt>
                                <dd class="mt-1 text-sm text-neutral-100">{{ $site->php_version }}</dd>
                            </div>
                            @if($site->git_repository)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-neutral-500">Repositório Git</dt>
                                    <dd class="mt-1 text-sm text-neutral-100 break-all">{{ $site->git_repository }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-neutral-500">Branch</dt>
                                    <dd class="mt-1 text-sm text-neutral-100">{{ $site->git_branch ?? 'main' }}</dd>
                                </div>
                            @endif
                            @if($site->document_root)
                                <div>
                                    <dt class="text-sm font-medium text-neutral-500">Document Root</dt>
                                    <dd class="mt-1 text-sm text-neutral-100">{{ $site->document_root }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Criado em</dt>
                                <dd class="mt-1 text-sm text-neutral-100">{{ $site->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Atualizado em</dt>
                                <dd class="mt-1 text-sm text-neutral-100">{{ $site->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Deployments -->
                <div class="bg-neutral-800 shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-neutral-100 mb-4">Deployments Recentes</h3>
                        @if($site->deployments->count() > 0)
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    @foreach($site->deployments->take(10) as $deployment)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-neutral-700" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-neutral-800
                                                            @if($deployment->status === 'success') bg-success-500
                                                            @elseif($deployment->status === 'failed') bg-error-500
                                                            @elseif($deployment->status === 'running') bg-info-500
                                                            @else bg-warning-500
                                                            @endif">
                                                            @if($deployment->status === 'success')
                                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                </svg>
                                                            @elseif($deployment->status === 'failed')
                                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                </svg>
                                                            @else
                                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                                </svg>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                        <div>
                                                            <p class="text-sm text-neutral-100">
                                                                {{ $deployment->commit_message ?? 'Deploy #' . $deployment->id }}
                                                            </p>
                                                            <p class="text-xs text-neutral-500">
                                                                {{ $deployment->commit_hash ? substr($deployment->commit_hash, 0, 7) : '' }}
                                                                @if($deployment->duration_seconds)
                                                                    · {{ $deployment->duration_seconds }}s
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="whitespace-nowrap text-right text-sm text-neutral-500">
                                                            <time>{{ $deployment->created_at->diffForHumans() }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="text-sm text-neutral-500">Nenhum deployment realizado ainda.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="space-y-6">
                <div class="bg-neutral-800 shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-neutral-100 mb-4">Ações Rápidas</h3>
                        <div class="space-y-3">
                            <button type="button" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Deploy
                            </button>
                            <button type="button" class="w-full inline-flex items-center justify-center px-4 py-2 border border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-300 bg-neutral-800 hover:bg-neutral-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reiniciar
                            </button>
                            <a href="http://{{ $site->domain }}" target="_blank" class="w-full inline-flex items-center justify-center px-4 py-2 border border-neutral-600 rounded-md shadow-sm text-sm font-medium text-neutral-300 bg-neutral-800 hover:bg-neutral-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Abrir Site
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="mt-8">
            <div class="bg-neutral-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-neutral-100 mb-6">Comentários</h3>
                    
                    <!-- Comment Form -->
                    <div class="mb-6">
                        <x-comment-form 
                            commentable-type="App\Models\Site" 
                            :commentable-id="$site->id" 
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
                    <div class="flex gap-3 p-4 bg-neutral-800 rounded-lg border border-neutral-700 hover:border-neutral-600 transition">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-semibold">
                                ${escapeHtml(comment.user.name.charAt(0).toUpperCase())}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-medium text-neutral-100">${escapeHtml(comment.user.name)}</span>
                                <span class="text-xs text-neutral-500">${escapeHtml(comment.time_since)}</span>
                                ${comment.is_edited ? '<span class="text-xs text-neutral-400 italic">(editado)</span>' : ''}
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
            loadComments('App\\\\Models\\\\Site', {{ $site->id }});
        });
    </script>
</x-layout>
