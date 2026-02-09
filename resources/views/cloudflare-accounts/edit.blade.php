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
                <h1 class="text-3xl font-bold text-neutral-900">Editar Conta Cloudflare</h1>
            </div>
            <p class="text-sm text-neutral-700">{{ $cloudflareAccount->name }}</p>
        </div>

        <!-- Form -->
        <div class="bg-white shadow sm:rounded-lg">
            <form action="{{ route('cloudflare-accounts.update', $cloudflareAccount) }}" method="POST" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-700">
                        Nome da Conta <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-sm text-neutral-500">Um nome descritivo para identificar esta conta</p>
                    <input type="text" name="name" id="name" value="{{ old('name', $cloudflareAccount->name) }}" required
                        class="mt-2 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- API Token -->
                <div>
                    <label for="api_token" class="block text-sm font-medium text-neutral-700">
                        API Token
                    </label>
                    <p class="mt-1 text-sm text-neutral-500">Deixe em branco para manter o token atual</p>
                    <input type="text" name="api_token" id="api_token" value="{{ old('api_token') }}"
                        class="mt-2 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm font-mono @error('api_token') border-red-300 @enderror"
                        placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                    @error('api_token')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-neutral-500">🔒 Token atual está criptografado e oculto por segurança</p>
                </div>

                <!-- Account ID -->
                <div>
                    <label for="account_id" class="block text-sm font-medium text-neutral-700">
                        Account ID
                    </label>
                    <p class="mt-1 text-sm text-neutral-500">ID da conta Cloudflare (opcional)</p>
                    <input type="text" name="account_id" id="account_id" value="{{ old('account_id', $cloudflareAccount->account_id) }}"
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
                    <p class="mt-1 text-sm text-neutral-500">ID da zona padrão (opcional)</p>
                    <input type="text" name="zone_id" id="zone_id" value="{{ old('zone_id', $cloudflareAccount->zone_id) }}"
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
                        class="mt-2 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description', $cloudflareAccount->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $cloudflareAccount->is_active) ? 'checked' : '' }}
                            class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-neutral-700">Conta ativa</label>
                        <p class="text-neutral-500">Desmarque para desativar temporariamente esta conta</p>
                    </div>
                </div>

                <!-- Sites Count -->
                @if($cloudflareAccount->sites_count > 0)
                    <div class="rounded-md bg-yellow-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Esta conta está sendo usada por <strong>{{ $cloudflareAccount->sites_count }} {{ Str::plural('site', $cloudflareAccount->sites_count) }}</strong>.
                                    Alterações podem afetar esses sites.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('cloudflare-accounts.index') }}" class="px-4 py-2 border border-neutral-300 rounded-md shadow-sm text-sm font-medium text-neutral-700 bg-white hover:bg-neutral-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Atualizar Conta
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
