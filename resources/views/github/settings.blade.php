<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-2">
                    <!-- GitHub Logo -->
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                    <h1 class="text-3xl font-bold text-white">Integração GitHub</h1>
                </div>
                <p class="text-neutral-300 mt-2">Configure sua conexão com o GitHub para gerenciar repositórios e deploys</p>
            </div>

            @if(auth()->user()->hasGitHubConnected())
                <!-- GitHub Conectado -->
                <div class="bg-green-900/40 border border-green-700 rounded-lg p-6 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <svg class="w-12 h-12 text-green-400 mr-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                            </svg>
                            <div>
                                <h3 class="text-lg font-semibold text-green-100">GitHub Conectado</h3>
                                <p class="text-green-200">
                                    @<strong>{{ auth()->user()->github_username }}</strong>
                                </p>
                                <p class="text-sm text-green-300 mt-1">Conexão ativa e funcionando</p>
                            </div>
                        </div>
                        <form action="{{ route('github.disconnect') }}" method="POST" onsubmit="return confirm('Tem certeza que deseja desconectar seu GitHub?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                Desconectar
                            </button>
                        </form>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-green-700">
                        <a href="{{ route('github.repositories.index') }}" class="inline-flex items-center text-green-300 hover:text-green-100 font-medium">
                            📂 Gerenciar Repositórios
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @else
                <!-- Conectar GitHub -->
                <div class="bg-neutral-800 border border-neutral-700 rounded-lg shadow-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-white mb-4">Conecte sua conta GitHub</h2>
                    
                    <!-- Opção 1: Personal Access Token (Recomendado) -->
                    <div class="border border-neutral-700 rounded-lg p-5 mb-4">
                        <div class="flex items-center mb-3">
                            <div class="bg-blue-900/40 rounded-full p-2 mr-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg text-white">Personal Access Token</h3>
                                <span class="text-xs bg-blue-900/40 text-blue-300 px-2 py-1 rounded">Recomendado</span>
                            </div>
                        </div>
                        
                        <div class="bg-blue-900/20 border border-blue-700 rounded p-4 mb-4 text-sm">
                            <h4 class="font-semibold text-blue-200 mb-2">📋 Como gerar seu token:</h4>
                            <ol class="list-decimal list-inside space-y-1 text-blue-300">
                                <li>Acesse <a href="https://github.com/settings/tokens" target="_blank" class="underline font-medium">github.com/settings/tokens</a></li>
                                <li>Clique em "Generate new token (classic)"</li>
                                <li>Nome: "Pudim Deployment"</li>
                                <li>Selecione os scopes:
                                    <ul class="list-disc list-inside ml-6 mt-1">
                                        <li><code class="bg-blue-900/40 px-1 rounded">repo</code> (controle total de repositórios)</li>
                                        <li><code class="bg-blue-900/40 px-1 rounded">workflow</code> (gerenciar GitHub Actions)</li>
                                        <li><code class="bg-blue-900/40 px-1 rounded">admin:repo_hook</code> (gerenciar webhooks)</li>
                                    </ul>
                                </li>
                                <li>Copie o token gerado (aparece apenas uma vez!)</li>
                            </ol>
                        </div>

                        <form action="{{ route('github.personal-token') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="token" class="block text-sm font-medium text-neutral-200 mb-2">
                                    Cole seu Personal Access Token:
                                </label>
                                <input 
                                    type="password" 
                                    name="token" 
                                    id="token"
                                    class="w-full border border-neutral-600 bg-neutral-900 text-white rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('token') border-red-500 @enderror font-mono text-sm"
                                    placeholder="ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                    required
                                >
                                @error('token')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-3 rounded font-medium hover:bg-blue-700 transition">
                                💾 Salvar Token e Conectar
                            </button>
                        </form>
                    </div>

                    @if(config('services.github.client_id'))
                        <!-- Opção 2: OAuth (se configurado) -->
                        <div class="border border-neutral-700 rounded-lg p-5">
                            <div class="flex items-center mb-3">
                                <div class="bg-neutral-700 rounded-full p-2 mr-3">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg text-white">OAuth GitHub</h3>
                                    <span class="text-xs bg-neutral-700 text-neutral-300 px-2 py-1 rounded">Alternativo</span>
                                </div>
                            </div>
                            
                            <p class="text-sm text-neutral-300 mb-4">
                                Conecte com um clique usando OAuth (configurado pelo administrador)
                            </p>

                            <a href="{{ route('github.connect') }}" class="block w-full bg-gray-800 text-white px-4 py-3 rounded font-medium hover:bg-gray-900 transition text-center">
                                <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                                </svg>
                                Conectar com GitHub OAuth
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Informações adicionais -->
                <div class="bg-neutral-900/50 rounded-lg p-5 text-sm text-neutral-300">
                    <h3 class="font-semibold mb-2 text-white">🔒 Segurança</h3>
                    <ul class="space-y-1">
                        <li>• Seus tokens são armazenados <strong>criptografados</strong> no banco de dados</li>
                        <li>• Você tem controle total sobre suas credenciais</li>
                        <li>• Pode desconectar a qualquer momento</li>
                        <li>• Tokens podem ser revogados diretamente no GitHub</li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-layout>
