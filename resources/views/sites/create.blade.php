<x-layout>
    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('sites.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Voltar para sites
            </a>
            <h1 class="mt-2 text-3xl font-bold text-gray-900">Novo Site</h1>
            <p class="mt-2 text-sm text-gray-700">Adicione um novo site/aplicação</p>
        </div>

        <form action="{{ route('sites.store') }}" method="POST" class="bg-white shadow sm:rounded-lg">
            @csrf
            
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <!-- Servidor -->
                <div>
                    <label for="server_id" class="block text-sm font-medium text-gray-700">Servidor</label>
                    <select name="server_id" id="server_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('server_id') border-red-300 @enderror">
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
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nome -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome do Site</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror"
                        placeholder="Minha Aplicação">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Domínio -->
                <div>
                    <label for="domain" class="block text-sm font-medium text-gray-700">Domínio</label>
                    <input type="text" name="domain" id="domain" value="{{ old('domain') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('domain') border-red-300 @enderror"
                        placeholder="exemplo.com.br">
                    @error('domain')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Repositório Git -->
                <div>
                    <label for="git_repository" class="block text-sm font-medium text-gray-700">Repositório Git (Opcional)</label>
                    <input type="text" name="git_repository" id="git_repository" value="{{ old('git_repository') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('git_repository') border-red-300 @enderror"
                        placeholder="https://github.com/usuario/projeto.git">
                    @error('git_repository')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Branch Git -->
                    <div>
                        <label for="git_branch" class="block text-sm font-medium text-gray-700">Branch</label>
                        <input type="text" name="git_branch" id="git_branch" value="{{ old('git_branch', 'main') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('git_branch') border-red-300 @enderror">
                        @error('git_branch')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PHP Version -->
                    <div>
                        <label for="php_version" class="block text-sm font-medium text-gray-700">Versão PHP</label>
                        <select name="php_version" id="php_version" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('php_version') border-red-300 @enderror">
                            <option value="8.3" {{ old('php_version') == '8.3' ? 'selected' : '' }}>PHP 8.3</option>
                            <option value="8.2" {{ old('php_version', '8.2') == '8.2' ? 'selected' : '' }}>PHP 8.2</option>
                            <option value="8.1" {{ old('php_version') == '8.1' ? 'selected' : '' }}>PHP 8.1</option>
                            <option value="8.0" {{ old('php_version') == '8.0' ? 'selected' : '' }}>PHP 8.0</option>
                            <option value="7.4" {{ old('php_version') == '7.4' ? 'selected' : '' }}>PHP 7.4</option>
                        </select>
                        @error('php_version')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Git Token -->
                <div>
                    <label for="git_token" class="block text-sm font-medium text-gray-700">Token Git (Opcional)</label>
                    <input type="password" name="git_token" id="git_token" value="{{ old('git_token') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('git_token') border-red-300 @enderror"
                        placeholder="ghp_xxxxxxxxxxxx">
                    <p class="mt-1 text-xs text-gray-500">Necessário para repositórios privados</p>
                    @error('git_token')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Document Root -->
                <div>
                    <label for="document_root" class="block text-sm font-medium text-gray-700">Document Root</label>
                    <input type="text" name="document_root" id="document_root" value="{{ old('document_root', '/public') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('document_root') border-red-300 @enderror"
                        placeholder="/public">
                    <p class="mt-1 text-xs text-gray-500">Pasta pública do projeto (geralmente /public para Laravel)</p>
                    @error('document_root')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- DNS Configuration -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">⚙️ Configuração DNS & SSL</h3>
                    
                    <div class="space-y-4">
                        <!-- Cloudflare Account -->
                        <div>
                            <label for="cloudflare_account_id" class="block text-sm font-medium text-gray-700">Conta Cloudflare</label>
                            <select name="cloudflare_account_id" id="cloudflare_account_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('cloudflare_account_id') border-red-300 @enderror">
                                <option value="">Selecione uma conta Cloudflare (opcional)</option>
                                @foreach(\App\Models\CloudflareAccount::where('is_active', true)->get() as $account)
                                    <option value="{{ $account->id }}" {{ old('cloudflare_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Necessário para DNS e SSL automático. 
                                <a href="{{ route('cloudflare-accounts.create') }}" target="_blank" class="text-indigo-600 hover:text-indigo-500 underline">Adicionar nova conta</a>
                            </p>
                            @error('cloudflare_account_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Auto DNS -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="auto_dns" id="auto_dns" value="1" {{ old('auto_dns', true) ? 'checked' : '' }}
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="auto_dns" class="font-medium text-gray-700">Configurar DNS automaticamente via Cloudflare</label>
                                <p class="text-gray-500">Cria registro A apontando para o IP do servidor</p>
                            </div>
                        </div>

                        <!-- Cloudflare Proxy -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="cloudflare_proxy" id="cloudflare_proxy" value="1" {{ old('cloudflare_proxy', true) ? 'checked' : '' }}
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="cloudflare_proxy" class="font-medium text-gray-700">Ativar Proxy Cloudflare (CDN + DDoS Protection)</label>
                                <p class="text-gray-500">Orange cloud ☁️ - Recomendado para melhor performance</p>
                            </div>
                        </div>

                        <!-- SSL Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Certificado SSL</label>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input type="radio" name="ssl_type" id="ssl_cloudflare" value="cloudflare" {{ old('ssl_type', 'cloudflare') === 'cloudflare' ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="ssl_cloudflare" class="ml-3 block text-sm text-gray-700">
                                        <span class="font-medium">Cloudflare Origin Certificate</span>
                                        <span class="block text-xs text-gray-500">Validade: 15 anos | Sem renovação | Requer proxy ativo</span>
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="ssl_type" id="ssl_letsencrypt" value="letsencrypt" {{ old('ssl_type') === 'letsencrypt' ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="ssl_letsencrypt" class="ml-3 block text-sm text-gray-700">
                                        <span class="font-medium">Let's Encrypt</span>
                                        <span class="block text-xs text-gray-500">Validade: 90 dias | Renovação automática | Funciona sem proxy</span>
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="ssl_type" id="ssl_none" value="none" {{ old('ssl_type') === 'none' ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="ssl_none" class="ml-3 block text-sm text-gray-700">
                                        <span class="font-medium">Sem SSL</span>
                                        <span class="block text-xs text-gray-500">Apenas HTTP (não recomendado para produção)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        <strong>Cloudflare API Token necessário:</strong> Configure CLOUDFLARE_API_TOKEN no arquivo .env
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Criar Site
                </button>
                <a href="{{ route('sites.index') }}" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-layout>
