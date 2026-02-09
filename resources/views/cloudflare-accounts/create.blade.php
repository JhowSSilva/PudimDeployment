<x-layout>
    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('cloudflare-accounts.index') }}" class="text-neutral-400 hover:text-neutral-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-neutral-900">Nova Conta Cloudflare</h1>
            </div>
            <p class="text-sm text-neutral-700">Adicione uma nova conta Cloudflare para gerenciar DNS e SSL</p>
        </div>

        <!-- Info Box -->
        <div class="mb-6 rounded-md bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm text-blue-700">
                        <strong>Como obter o API Token:</strong><br>
                        1. Acesse <a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank" class="underline">Cloudflare Dashboard → API Tokens</a><br>
                        2. Clique em "Create Token"<br>
                        3. Use o template "Edit zone DNS" ou configure permissões personalizadas<br>
                        4. Copie o token gerado (ele será mostrado apenas uma vez)
                    </p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white shadow sm:rounded-lg">
            <form action="{{ route('cloudflare-accounts.store') }}" method="POST" class="space-y-6 p-6">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-700">
                        Nome da Conta <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-sm text-neutral-500">Um nome descritivo para identificar esta conta (ex: "Conta Principal", "Cliente XYZ")</p>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="mt-2 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- API Token -->
                <div>
                    <label for="api_token" class="block text-sm font-medium text-neutral-700">
                        API Token <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-sm text-neutral-500">Token de API da Cloudflare com permissões para DNS e SSL</p>
                    <input type="text" name="api_token" id="api_token" value="{{ old('api_token') }}" required
                        class="mt-2 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm font-mono @error('api_token') border-red-300 @enderror"
                        placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                    @error('api_token')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Account ID -->
                <div>
                    <label for="account_id" class="block text-sm font-medium text-neutral-700">
                        Account ID
                    </label>
                    <p class="mt-1 text-sm text-neutral-500">ID da conta Cloudflare (opcional, encontrado no Dashboard)</p>
                    <input type="text" name="account_id" id="account_id" value="{{ old('account_id') }}"
                        class="mt-2 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm font-mono @error('account_id') border-red-300 @enderror">
                    @error('account_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Zone ID -->
                <div>
                    <label for="zone_id" class="block text-sm font-medium text-neutral-700">
                        Zone ID
                    </label>
                    <p class="mt-1 text-sm text-neutral-500">ID da zona padrão (opcional, pode ser detectado automaticamente pelo domínio)</p>
                    <input type="text" name="zone_id" id="zone_id" value="{{ old('zone_id') }}"
                        class="mt-2 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm font-mono @error('zone_id') border-red-300 @enderror">
                    @error('zone_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-neutral-700">
                        Descrição
                    </label>
                    <textarea name="description" id="description" rows="3"
                        class="mt-2 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                            class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-neutral-700">Conta ativa</label>
                        <p class="text-neutral-500">Desmarque para desativar temporariamente esta conta sem removê-la</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('cloudflare-accounts.index') }}" class="px-4 py-2 border border-neutral-300 rounded-md shadow-sm text-sm font-medium text-neutral-700 bg-white hover:bg-neutral-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Salvar Conta
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
