<x-layout>
    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('servers.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                ← Voltar para servidores
            </a>
            <h1 class="mt-2 text-3xl font-bold text-neutral-900">Editar Servidor</h1>
            <p class="mt-2 text-sm text-neutral-700">Atualize as informações do servidor</p>
        </div>

        <form action="{{ route('servers.update', $server) }}" method="POST" class="bg-white shadow sm:rounded-lg">
            @csrf
            @method('PUT')
            
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <!-- Nome -->
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-700">Nome do Servidor</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $server->name) }}" required
                        class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- IP Address -->
                <div>
                    <label for="ip_address" class="block text-sm font-medium text-neutral-700">Endereço IP</label>
                    <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', $server->ip_address) }}" required
                        class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('ip_address') border-red-300 @enderror">
                    @error('ip_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- SSH Port -->
                    <div>
                        <label for="ssh_port" class="block text-sm font-medium text-neutral-700">Porta SSH</label>
                        <input type="number" name="ssh_port" id="ssh_port" value="{{ old('ssh_port', $server->ssh_port) }}" required
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('ssh_port') border-red-300 @enderror">
                        @error('ssh_port')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SSH User -->
                    <div>
                        <label for="ssh_user" class="block text-sm font-medium text-neutral-700">Usuário SSH</label>
                        <input type="text" name="ssh_user" id="ssh_user" value="{{ old('ssh_user', $server->ssh_user) }}" required
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('ssh_user') border-red-300 @enderror">
                        @error('ssh_user')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Auth Type -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Tipo de Autenticação</label>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input id="auth_password" name="auth_type" type="radio" value="password" {{ old('auth_type', $server->auth_type) === 'password' ? 'checked' : '' }}
                                class="h-4 w-4 border-neutral-300 text-primary-600 focus:ring-primary-500" onclick="toggleAuthFields()">
                            <label for="auth_password" class="ml-3 block text-sm font-medium text-neutral-700">Senha</label>
                        </div>
                        <div class="flex items-center">
                            <input id="auth_key" name="auth_type" type="radio" value="key" {{ old('auth_type', $server->auth_type) === 'key' ? 'checked' : '' }}
                                class="h-4 w-4 border-neutral-300 text-primary-600 focus:ring-primary-500" onclick="toggleAuthFields()">
                            <label for="auth_key" class="ml-3 block text-sm font-medium text-neutral-700">Chave Privada</label>
                        </div>
                    </div>
                </div>

                <!-- SSH Password -->
                <div id="password_field" style="display: {{ old('auth_type', $server->auth_type) === 'password' ? 'block' : 'none' }}">
                    <label for="ssh_password" class="block text-sm font-medium text-neutral-700">Senha SSH</label>
                    <input type="password" name="ssh_password" id="ssh_password" placeholder="Deixe em branco para manter a senha atual"
                        class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('ssh_password') border-red-300 @enderror">
                    <p class="mt-1 text-xs text-neutral-500">Deixe em branco para manter a senha atual</p>
                    @error('ssh_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SSH Key -->
                <div id="key_field" style="display: {{ old('auth_type', $server->auth_type) === 'key' ? 'block' : 'none' }}">
                    <label for="ssh_key" class="block text-sm font-medium text-neutral-700">Chave Privada SSH</label>
                    <textarea name="ssh_key" id="ssh_key" rows="6" placeholder="Deixe em branco para manter a chave atual"
                        class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('ssh_key') border-red-300 @enderror">{{ old('ssh_key') }}</textarea>
                    <p class="mt-1 text-xs text-neutral-500">Deixe em branco para manter a chave atual</p>
                    @error('ssh_key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-neutral-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Salvar Alterações
                </button>
                <a href="{{ route('servers.index') }}" class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-neutral-700 hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        function toggleAuthFields() {
            const authType = document.querySelector('input[name="auth_type"]:checked').value;
            const passwordField = document.getElementById('password_field');
            const keyField = document.getElementById('key_field');
            
            if (authType === 'password') {
                passwordField.style.display = 'block';
                keyField.style.display = 'none';
            } else {
                passwordField.style.display = 'none';
                keyField.style.display = 'block';
            }
        }
    </script>
</x-layout>
