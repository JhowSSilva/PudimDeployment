<x-layout>
    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('sites.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Voltar para sites
            </a>
            <h1 class="mt-2 text-3xl font-bold text-gray-900">Editar Site</h1>
            <p class="mt-2 text-sm text-gray-700">Atualize as informações do site</p>
        </div>

        <form action="{{ route('sites.update', $site) }}" method="POST" class="bg-white shadow sm:rounded-lg">
            @csrf
            @method('PUT')
            
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <div>
                    <label for="server_id" class="block text-sm font-medium text-gray-700">Servidor</label>
                    <select name="server_id" id="server_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('server_id') border-red-300 @enderror">
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" {{ old('server_id', $site->server_id) == $server->id ? 'selected' : '' }}>
                                {{ $server->name }} ({{ $server->ip_address }})
                            </option>
                        @endforeach
                    </select>
                    @error('server_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome do Site</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $site->name) }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="domain" class="block text-sm font-medium text-gray-700">Domínio</label>
                    <input type="text" name="domain" id="domain" value="{{ old('domain', $site->domain) }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('domain') border-red-300 @enderror">
                    @error('domain')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="git_repository" class="block text-sm font-medium text-gray-700">Repositório Git</label>
                    <input type="text" name="git_repository" id="git_repository" value="{{ old('git_repository', $site->git_repository) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('git_repository') border-red-300 @enderror">
                    @error('git_repository')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="git_branch" class="block text-sm font-medium text-gray-700">Branch</label>
                        <input type="text" name="git_branch" id="git_branch" value="{{ old('git_branch', $site->git_branch) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('git_branch') border-red-300 @enderror">
                        @error('git_branch')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="php_version" class="block text-sm font-medium text-gray-700">Versão PHP</label>
                        <select name="php_version" id="php_version" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('php_version') border-red-300 @enderror">
                            <option value="8.3" {{ old('php_version', $site->php_version) == '8.3' ? 'selected' : '' }}>PHP 8.3</option>
                            <option value="8.2" {{ old('php_version', $site->php_version) == '8.2' ? 'selected' : '' }}>PHP 8.2</option>
                            <option value="8.1" {{ old('php_version', $site->php_version) == '8.1' ? 'selected' : '' }}>PHP 8.1</option>
                            <option value="8.0" {{ old('php_version', $site->php_version) == '8.0' ? 'selected' : '' }}>PHP 8.0</option>
                            <option value="7.4" {{ old('php_version', $site->php_version) == '7.4' ? 'selected' : '' }}>PHP 7.4</option>
                        </select>
                        @error('php_version')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="git_token" class="block text-sm font-medium text-gray-700">Token Git</label>
                    <input type="password" name="git_token" id="git_token" placeholder="Deixe em branco para manter o token atual"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('git_token') border-red-300 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Deixe em branco para manter o token atual</p>
                    @error('git_token')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="document_root" class="block text-sm font-medium text-gray-700">Document Root</label>
                    <input type="text" name="document_root" id="document_root" value="{{ old('document_root', $site->document_root) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('document_root') border-red-300 @enderror">
                    @error('document_root')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                    Salvar Alterações
                </button>
                <a href="{{ route('sites.index') }}" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-layout>