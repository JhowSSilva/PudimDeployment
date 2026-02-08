<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nova Estratégia de Deployment
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('cicd.deployment-strategies.store') }}" method="POST" class="p-6" x-data="{ type: 'blue_green' }">
                    @csrf

                    <div class="space-y-6">
                        <!-- Nome -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nome da Estratégia *</label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descrição -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                        </div>

                        <!-- Site -->
                        <div>
                            <label for="site_id" class="block text-sm font-medium text-gray-700">Site (opcional)</label>
                            <select name="site_id" id="site_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Disponível para todos os sites</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }} ({{ $site->domain }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tipo de Estratégia -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Estratégia *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none" :class="type === 'blue_green' ? 'border-green-500 ring-2 ring-green-500' : ''">
                                    <input type="radio" name="type" value="blue_green" x-model="type" class="sr-only">
                                    <span class="flex flex-1 flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Blue-Green</span>
                                        <span class="mt-1 text-xs text-gray-500">Environment completo novo, troca após validação</span>
                                    </span>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none" :class="type === 'canary' ? 'border-yellow-500 ring-2 ring-yellow-500' : ''">
                                    <input type="radio" name="type" value="canary" x-model="type" class="sr-only">
                                    <span class="flex flex-1 flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Canary</span>
                                        <span class="mt-1 text-xs text-gray-500">Atualização gradual em percentuais</span>
                                    </span>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none" :class="type === 'rolling' ? 'border-blue-500 ring-2 ring-blue-500' : ''">
                                    <input type="radio" name="type" value="rolling" x-model="type" class="sr-only">
                                    <span class="flex flex-1 flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Rolling</span>
                                        <span class="mt-1 text-xs text-gray-500">Atualiza instâncias em lotes</span>
                                    </span>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none" :class="type === 'recreate' ? 'border-gray-500 ring-2 ring-gray-500' : ''">
                                    <input type="radio" name="type" value="recreate" x-model="type" class="sr-only">
                                    <span class="flex flex-1 flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Recreate</span>
                                        <span class="mt-1 text-xs text-gray-500">Para, atualiza, reinicia</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Configurações específicas para Canary -->
                        <div x-show="type === 'canary'" class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                            <h4 class="font-medium text-gray-900 mb-3">Configurações Canary</h4>
                            <div class="space-y-3">
                                <div>
                                    <label for="canary_percentage" class="block text-sm font-medium text-gray-700">Percentual Inicial (%)</label>
                                    <input type="number" name="config[canary_percentage]" id="canary_percentage" min="1" max="100" 
                                        value="{{ old('config.canary_percentage', 10) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="canary_steps" class="block text-sm font-medium text-gray-700">Etapas (separadas por vírgula)</label>
                                    <input type="text" name="config[canary_steps]" id="canary_steps" 
                                        value="{{ old('config.canary_steps', '10,25,50,100') }}"
                                        placeholder="10,25,50,100"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Ex: 10,25,50,100 para 4 etapas</p>
                                </div>
                            </div>
                        </div>

                        <!-- Configurações específicas para Rolling -->
                        <div x-show="type === 'rolling'" class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <h4 class="font-medium text-gray-900 mb-3">Configurações Rolling</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="rolling_batch_size" class="block text-sm font-medium text-gray-700">Tamanho do Batch</label>
                                    <input type="number" name="config[rolling_batch_size]" id="rolling_batch_size" min="1" 
                                        value="{{ old('config.rolling_batch_size', 1) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="rolling_batch_delay" class="block text-sm font-medium text-gray-700">Delay entre Batches (s)</label>
                                    <input type="number" name="config[rolling_batch_delay]" id="rolling_batch_delay" min="0" 
                                        value="{{ old('config.rolling_batch_delay', 30) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Health Check -->
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="font-medium text-gray-900 mb-3">Health Check</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label for="health_check_interval" class="block text-sm font-medium text-gray-700">Intervalo (s)</label>
                                    <input type="number" name="health_check_config[interval]" id="health_check_interval" min="1" 
                                        value="{{ old('health_check_config.interval', 10) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="health_check_timeout" class="block text-sm font-medium text-gray-700">Timeout (s)</label>
                                    <input type="number" name="health_check_config[timeout]" id="health_check_timeout" min="1" 
                                        value="{{ old('health_check_config.timeout', 5) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="health_check_threshold" class="block text-sm font-medium text-gray-700">Threshold</label>
                                    <input type="number" name="health_check_config[threshold]" id="health_check_threshold" min="1" 
                                        value="{{ old('health_check_config.threshold', 3) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Rollback -->
                        <div>
                            <label for="rollback_on_failure_percentage" class="block text-sm font-medium text-gray-700">
                                Auto-rollback se taxa de erro exceder (%)
                            </label>
                            <input type="number" name="rollback_on_failure_percentage" id="rollback_on_failure_percentage" 
                                min="0" max="100" value="{{ old('rollback_on_failure_percentage', 5) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Deixe vazio para desabilitar auto-rollback</p>
                        </div>

                        <!-- Opções -->
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="requires_approval" id="requires_approval" value="1" {{ old('requires_approval') ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="requires_approval" class="ml-2 block text-sm text-gray-700">
                                    Requer aprovação manual antes do deployment
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="is_default" class="ml-2 block text-sm text-gray-700">
                                    Tornar esta a estratégia padrão
                                </label>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end gap-3 pt-6 border-t">
                            <a href="{{ route('cicd.deployment-strategies.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Criar Estratégia
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="//unpkg.com/alpinejs" defer></script>
</x-app-layout>
