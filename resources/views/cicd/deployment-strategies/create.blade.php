<x-layout title="Criar Estratégia de Deployment">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Criar Estratégia de Deployment') }}
        </h2>
        <a href="{{ route('cicd.deployment-strategies.index') }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-info-600 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
            Voltar
        </a>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <form action="{{ route('cicd.deployment-strategies.store') }}" method="POST" class="p-6" x-data="{ strategyType: 'blue_green' }">
            @csrf

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                @error('name')
                    <p class="text-error-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Descrição</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Site</label>
                <select name="site_id" required class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                    <option value="">Selecione um site</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->domain }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Tipo de Estratégia</label>
                <select name="type" x-model="strategyType" required class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                    <option value="blue_green">Blue-Green (Zero Downtime)</option>
                    <option value="canary">Canary (Gradual)</option>
                    <option value="rolling">Rolling (Sequencial)</option>
                    <option value="recreate">Recreate (Simples)</option>
                </select>
            </div>

            <!-- Blue-Green Config -->
            <div class="mb-4 p-4 bg-neutral-700 rounded" x-show="strategyType === 'blue_green'">
                <h3 class="text-white font-semibold mb-3">Configuração Blue-Green</h3>
                <div class="mb-3">
                    <label class="block text-neutral-300 text-sm mb-2">Tempo de espera (segundos)</label>
                    <input type="number" name="config[blue_green][wait_time]" value="30" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
            </div>

            <!-- Canary Config -->
            <div class="mb-4 p-4 bg-neutral-700 rounded" x-show="strategyType === 'canary'">
                <h3 class="text-white font-semibold mb-3">Configuração Canary</h3>
                <div class="mb-3">
                    <label class="block text-neutral-300 text-sm mb-2">Percentage inicial (%)</label>
                    <input type="number" name="config[canary][initial_percentage]" value="10" min="1" max="100" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
                <div class="mb-3">
                    <label class="block text-neutral-300 text-sm mb-2">Incremento (%)</label>
                    <input type="number" name="config[canary][increment]" value="25" min="1" max="100" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
                <div>
                    <label class="block text-neutral-300 text-sm mb-2">Intervalo entre incrementos (minutos)</label>
                    <input type="number" name="config[canary][interval_minutes]" value="5" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
            </div>

            <!-- Rolling Config -->
            <div class="mb-4 p-4 bg-neutral-700 rounded" x-show="strategyType === 'rolling'">
                <h3 class="text-white font-semibold mb-3">Configuração Rolling</h3>
                <div>
                    <label class="block text-neutral-300 text-sm mb-2">Tamanho do lote</label>
                    <input type="number" name="config[rolling][max_batch_size]" value="1" min="1" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
            </div>

            <!-- Health Check -->
            <div class="mb-4 p-4 bg-neutral-700 rounded">
                <h3 class="text-white font-semibold mb-3">Health Check</h3>
                <div class="mb-3">
                    <label class="block text-neutral-300 text-sm mb-2">URL do Health Check</label>
                    <input type="url" name="health_check[url]" value="{{ old('health_check.url') }}" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
                <div class="mb-3">
                    <label class="block text-neutral-300 text-sm mb-2">Tentativas</label>
                    <input type="number" name="health_check[retries]" value="3" min="1" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
                <div>
                    <label class="block text-neutral-300 text-sm mb-2">Timeout (segundos)</label>
                    <input type="number" name="health_check[timeout]" value="30" min="1" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-info-600 text-white rounded-md hover:bg-info-700">
                    Criar Estratégia
                </button>
            </div>
        </form>
    </div>
</x-layout>
