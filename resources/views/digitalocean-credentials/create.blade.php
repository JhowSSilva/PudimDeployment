<x-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('digitalocean-credentials.index') }}" class="text-primary-600 hover:text-primary-700 inline-flex items-center mb-4">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar às credenciais
                </a>
                <h1 class="text-3xl font-bold text-neutral-100">Nova Credencial DigitalOcean</h1>
                <p class="text-neutral-400 mt-1">Adicione uma nova credencial DigitalOcean</p>
            </div>

            <x-card>
                <form action="{{ route('digitalocean-credentials.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">Nome</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                               class="w-full rounded-lg border border-neutral-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('name')
                            <p class="text-error-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="api_token" class="block text-sm font-medium text-neutral-300 mb-2">API Token</label>
                        <input type="password" name="api_token" id="api_token" value="{{ old('api_token') }}" required 
                               class="w-full rounded-lg border border-neutral-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-neutral-500 text-sm mt-1">Token de API do DigitalOcean (gerado no painel de controle)</p>
                        @error('api_token')
                            <p class="text-error-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="region" class="block text-sm font-medium text-neutral-300 mb-2">Região Padrão</label>
                        <select name="region" id="region" required 
                                class="w-full rounded-lg border border-neutral-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @foreach($regions as $code => $name)
                                <option value="{{ $code }}" {{ old('region') === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('region')
                            <p class="text-error-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-4 pt-6">
                        <a href="{{ route('digitalocean-credentials.index') }}" class="px-6 py-2 border border-neutral-600 text-neutral-300 rounded-lg hover:bg-neutral-700 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg font-medium transition-colors">
                            Salvar Credencial
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layout>