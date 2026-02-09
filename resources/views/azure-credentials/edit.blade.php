<x-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('azure-credentials.index') }}" class="text-primary-600 hover:text-primary-700 inline-flex items-center mb-4">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar às credenciais
                </a>
                <h1 class="text-3xl font-bold text-neutral-100">Editar Credencial Azure</h1>
                <p class="text-neutral-400 mt-1">Atualize as informações da credencial</p>
            </div>

            <x-card>
                <form action="{{ route('azure-credentials.update', $azureCredential) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">Nome</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $azureCredential->name) }}" required 
                               class="w-full rounded-lg border border-neutral-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('name')
                            <p class="text-error-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="subscription_id" class="block text-sm font-medium text-neutral-300 mb-2">Subscription ID</label>
                            <input type="text" name="subscription_id" id="subscription_id" value="{{ old('subscription_id', $azureCredential->subscription_id) }}" required 
                                   class="w-full rounded-lg border border-neutral-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('subscription_id')
                                <p class="text-error-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tenant_id" class="block text-sm font-medium text-neutral-300 mb-2">Tenant ID</label>
                            <input type="text" name="tenant_id" id="tenant_id" value="{{ old('tenant_id', $azureCredential->tenant_id) }}" required 
                                   class="w-full rounded-lg border border-neutral-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('tenant_id')
                                <p class="text-error-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="client_id" class="block text-sm font-medium text-neutral-300 mb-2">Client ID</label>
                        <input type="text" name="client_id" id="client_id" value="{{ old('client_id', $azureCredential->client_id) }}" required 
                               class="w-full rounded-lg border border-neutral-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('client_id')
                            <p class="text-error-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="client_secret" class="block text-sm font-medium text-neutral-300 mb-2">Client Secret</label>
                        <input type="password" name="client_secret" id="client_secret" placeholder="Deixe em branco para manter atual"
                               class="w-full rounded-lg border border-neutral-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-neutral-500 text-sm mt-1">Deixe em branco para manter o valor atual</p>
                        @error('client_secret')
                            <p class="text-error-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="region" class="block text-sm font-medium text-neutral-300 mb-2">Região Padrão</label>
                        <select name="region" id="region" required 
                                class="w-full rounded-lg border border-neutral-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @foreach($regions as $code => $name)
                                <option value="{{ $code }}" {{ old('region', $azureCredential->region) === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('region')
                            <p class="text-error-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default', $azureCredential->is_default) ? 'checked' : '' }}
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-600 rounded">
                        <label for="is_default" class="ml-2 block text-sm text-neutral-300">
                            Definir como credencial padrão
                        </label>
                    </div>

                    <div class="flex justify-end gap-4 pt-6">
                        <a href="{{ route('azure-credentials.index') }}" class="px-6 py-2 border border-neutral-600 text-neutral-300 rounded-lg hover:bg-neutral-700 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg font-medium transition-colors">
                            Atualizar Credencial
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layout>