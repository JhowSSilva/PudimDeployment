<x-layout>
    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8" 
         x-data="{
             loadingRepos: false,
             repositories: [],
             selectedRepo: '',
             async loadRepositories() {
                 this.loadingRepos = true;
                 try {
                     const response = await fetch('/api/github/repositories', {
                         headers: {
                             'Authorization': 'Bearer {{ auth()->user()->api_token ?? '' }}',
                             'Accept': 'application/json'
                         }
                     });
                     const data = await response.json();
                     if (data.repositories) {
                         this.repositories = data.repositories;
                     }
                 } catch (error) {
                     console.error('Erro ao carregar repositórios:', error);
                 } finally {
                     this.loadingRepos = false;
                 }
             },
             selectRepository(repo) {
                 if (repo) {
                     document.getElementById('git_repository').value = repo.clone_url;
                     document.getElementById('git_branch').value = repo.default_branch || 'main';
                 }
             }
         }"
         x-init="loadRepositories()">
        <div class="mb-8">
            <a href="{{ route('sites.index') }}" class="text-sm font-medium text-primary-400 hover:text-primary-300">
                ← Voltar para sites
            </a>
            <h1 class="mt-2 text-3xl font-bold text-white">Novo Site</h1>
            <p class="mt-2 text-sm text-neutral-300">Adicione um novo site/aplicação</p>
        </div>

        <form action="{{ route('sites.store') }}" method="POST" class="bg-neutral-800 border border-neutral-700 shadow-lg sm:rounded-lg">
            @csrf
            
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <!-- Servidor -->
                <div>
                    <label for="server_id" class="block text-sm font-medium text-neutral-200">Servidor</label>
                    <select name="server_id" id="server_id" required
                        class="mt-1 block w-full rounded-md border-neutral-600 bg-neutral-900 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('server_id') border-red-500 @enderror">
                        @if(isset($server))
                            <option value="{{ $server->id }}" selected>{{ $server->name }} ({{ $server->ip_address }})</option>
                        @else
                            <option value="">Selecione um servidor</option>
                            @foreach($servers as $srv)
                                <option value="{{ $srv->id }}" {{ old('server_id') == $srv->id ? 'selected' : '' }}>
                                    {{ $srv->name }} ({{ $srv->ip_address }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('server_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nome -->
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-200">Nome do Site</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full rounded-md border-neutral-600 bg-neutral-900 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('name') border-red-500 @enderror"
                        placeholder="Minha Aplicação">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Domínio -->
                <div>
                    <label for="domain" class="block text-sm font-medium text-neutral-200">Domínio</label>
                    <input type="text" name="domain" id="domain" value="{{ old('domain') }}" required
                        class="mt-1 block w-full rounded-md border-neutral-600 bg-neutral-900 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('domain') border-red-500 @enderror"
                        placeholder="exemplo.com.br">
                    @error('domain')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Repositório GitHub (Seleção Automática) -->
                <div>
                    <label for="github_repo_select" class="block text-sm font-medium text-neutral-200 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                        </svg>
                        Repositório GitHub
                    </label>
                    
                    <template x-if="loadingRepos">
                        <div class="flex items-center gap-2 text-neutral-400 text-sm">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Carregando repositórios...
                        </div>
                    </template>
                    
                    <template x-if="!loadingRepos && repositories.length > 0">
                        <select x-model="selectedRepo" 
                                @change="selectRepository(repositories.find(r => r.full_name === selectedRepo))"
                                class="mt-1 block w-full rounded-md border-neutral-600 bg-neutral-900 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="">Selecione um repositório</option>
                            <template x-for="repo in repositories" :key="repo.id">
                                <option :value="repo.full_name" x-text="repo.full_name + (repo.description ? ' - ' + repo.description : '')"></option>
                            </template>
                        </select>
                    </template>
                    
                    <template x-if="!loadingRepos && repositories.length === 0">
                        <div class="mt-1 text-sm text-neutral-400">
                            Nenhum repositório GitHub encontrado. 
                            <a href="{{ route('github.settings') }}" class="text-primary-400 hover:text-primary-300 underline">Conectar GitHub</a>
                        </div>
                    </template>
                </div>

                <!-- Repositório Git -->
                <div>
                    <label for="git_repository" class="block text-sm font-medium text-neutral-200">URL do Repositório Git (Opcional)</label>
                    <input type="text" name="git_repository" id="git_repository" value="{{ old('git_repository') }}"
                        class="mt-1 block w-full rounded-md border-neutral-600 bg-neutral-900 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('git_repository') border-red-500 @enderror"
                        placeholder="https://github.com/usuario/projeto.git">
                    <p class="mt-1 text-xs text-neutral-400">Preenche automaticamente ao selecionar um repositório GitHub acima</p>
                    @error('git_repository')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Branch Git -->
                    <div>
                        <label for="git_branch" class="block text-sm font-medium text-neutral-200">Branch</label>
                        <input type="text" name="git_branch" id="git_branch" value="{{ old('git_branch', 'main') }}"
                            class="mt-1 block w-full rounded-md border-neutral-600 bg-neutral-900 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('git_branch') border-red-500 @enderror">
                        @error('git_branch')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PHP Version -->
                    <div>
                        <label for="php_version" class="block text-sm font-medium text-neutral-200">Versão PHP</label>
                        <select name="php_version" id="php_version" required
                            class="mt-1 block w-full rounded-md border-neutral-600 bg-neutral-900 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('php_version') border-red-500 @enderror">
                            <option value="8.3" {{ old('php_version') == '8.3' ? 'selected' : '' }}>PHP 8.3</option>
                            <option value="8.2" {{ old('php_version', '8.2') == '8.2' ? 'selected' : '' }}>PHP 8.2</option>
                            <option value="8.1" {{ old('php_version') == '8.1' ? 'selected' : '' }}>PHP 8.1</option>
                            <option value="8.0" {{ old('php_version') == '8.0' ? 'selected' : '' }}>PHP 8.0</option>
                            <option value="7.4" {{ old('php_version') == '7.4' ? 'selected' : '' }}>PHP 7.4</option>
                        </select>
                        @error('php_version')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Git Token -->
                <div>
                    <label for="git_token" class="block text-sm font-medium text-neutral-200">Token Git (Opcional)</label>
                    <input type="password" name="git_token" id="git_token" value="{{ old('git_token') }}"
                        class="mt-1 block w-full rounded-md border-neutral-600 bg-neutral-900 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('git_token') border-red-500 @enderror"
                        placeholder="ghp_xxxxxxxxxxxx">
                    <p class="mt-1 text-xs text-neutral-400">Necessário para repositórios privados</p>
                    @error('git_token')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Document Root -->
                <div>
                    <label for="document_root" class="block text-sm font-medium text-neutral-200">Document Root</label>
                    <input type="text" name="document_root" id="document_root" value="{{ old('document_root', '/public') }}"
                        class="mt-1 block w-full rounded-md border-neutral-600 bg-neutral-900 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('document_root') border-red-500 @enderror"
                        placeholder="/public">
                    <p class="mt-1 text-xs text-neutral-400">Pasta pública do projeto (geralmente /public para Laravel)</p>
                    @error('document_root')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- DNS Configuration -->
                <div class="border-t border-neutral-700 pt-6">
                    <h3 class="text-lg font-medium text-white mb-4">⚙️ Configuração DNS & SSL</h3>
                    
                    <div class="space-y-4">
                        <!-- Cloudflare Account -->
                        <div>
                            <label for="cloudflare_account_id" class="block text-sm font-medium text-neutral-200">Conta Cloudflare</label>
                            <select name="cloudflare_account_id" id="cloudflare_account_id"
                                class="mt-1 block w-full rounded-md border-neutral-600 bg-neutral-900 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('cloudflare_account_id') border-red-500 @enderror">
                                <option value="">Selecione uma conta Cloudflare (opcional)</option>
                                @foreach(\App\Models\CloudflareAccount::where('is_active', true)->get() as $account)
                                    <option value="{{ $account->id }}" {{ old('cloudflare_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-neutral-400">
                                Necessário para DNS e SSL automático. 
                                <a href="{{ route('cloudflare-accounts.create') }}" target="_blank" class="text-primary-400 hover:text-primary-300 underline">Adicionar nova conta</a>
                            </p>
                            @error('cloudflare_account_id')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Auto DNS -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="auto_dns" id="auto_dns" value="1" {{ old('auto_dns', true) ? 'checked' : '' }}
                                    class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-600 bg-neutral-900 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="auto_dns" class="font-medium text-neutral-200">Configurar DNS automaticamente via Cloudflare</label>
                                <p class="text-neutral-400">Cria registro A apontando para o IP do servidor</p>
                            </div>
                        </div>

                        <!-- Cloudflare Proxy -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="cloudflare_proxy" id="cloudflare_proxy" value="1" {{ old('cloudflare_proxy', true) ? 'checked' : '' }}
                                    class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-600 bg-neutral-900 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="cloudflare_proxy" class="font-medium text-neutral-200">Ativar Proxy Cloudflare (CDN + DDoS Protection)</label>
                                <p class="text-neutral-400">Orange cloud ☁️ - Recomendado para melhor performance</p>
                            </div>
                        </div>

                        <!-- SSL Type -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-200 mb-2">Tipo de Certificado SSL</label>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input type="radio" name="ssl_type" id="ssl_cloudflare" value="cloudflare" {{ old('ssl_type', 'cloudflare') === 'cloudflare' ? 'checked' : '' }}
                                        class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-600 bg-neutral-900">
                                    <label for="ssl_cloudflare" class="ml-3 block text-sm text-neutral-200">
                                        <span class="font-medium">Cloudflare Origin Certificate</span>
                                        <span class="block text-xs text-neutral-400">Validade: 15 anos | Sem renovação | Requer proxy ativo</span>
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="ssl_type" id="ssl_letsencrypt" value="letsencrypt" {{ old('ssl_type') === 'letsencrypt' ? 'checked' : '' }}
                                        class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-600 bg-neutral-900">
                                    <label for="ssl_letsencrypt" class="ml-3 block text-sm text-neutral-200">
                                        <span class="font-medium">Let's Encrypt</span>
                                        <span class="block text-xs text-neutral-400">Validade: 90 dias | Renovação automática | Funciona sem proxy</span>
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="ssl_type" id="ssl_none" value="none" {{ old('ssl_type') === 'none' ? 'checked' : '' }}
                                        class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-neutral-600 bg-neutral-900">
                                    <label for="ssl_none" class="ml-3 block text-sm text-neutral-200">
                                        <span class="font-medium">Sem SSL</span>
                                        <span class="block text-xs text-neutral-400">Apenas HTTP (não recomendado para produção)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-900/20 border-l-4 border-blue-500 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-300">
                                        <strong>Cloudflare API Token necessário:</strong> Configure CLOUDFLARE_API_TOKEN no arquivo .env
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-neutral-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-neutral-700">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Criar Site
                </button>
                <a href="{{ route('sites.index') }}" class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-600 shadow-sm px-4 py-2 bg-neutral-800 text-base font-medium text-neutral-200 hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-layout>
