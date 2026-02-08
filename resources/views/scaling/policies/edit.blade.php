<x-layout title="Editar Política de Auto-scaling">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Editar Política de Auto-scaling') }}
        </h2>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <form action="{{ route('scaling.policies.update', $policy) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">Nome</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $policy->name) }}" required
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-neutral-300 mb-2">Descrição</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">{{ old('description', $policy->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="server_pool_id" class="block text-sm font-medium text-neutral-300 mb-2">Server Pool</label>
                    <select name="server_pool_id" id="server_pool_id" required
                            class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione um pool</option>
                        @foreach($pools as $pool)
                            <option value="{{ $pool->id }}" {{ old('server_pool_id', $policy->server_pool_id) == $pool->id ? 'selected' : '' }}>
                                {{ $pool->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('server_pool_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-neutral-300 mb-2">Tipo de Política</label>
                    <select name="type" id="type" required
                            class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        <option value="cpu" {{ old('type', $policy->type) == 'cpu' ? 'selected' : '' }}>CPU</option>
                        <option value="memory" {{ old('type', $policy->type) == 'memory' ? 'selected' : '' }}>Memória</option>
                        <option value="schedule" {{ old('type', $policy->type) == 'schedule' ? 'selected' : '' }}>Agendamento</option>
                        <option value="custom" {{ old('type', $policy->type) == 'custom' ? 'selected' : '' }}>Customizado</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="threshold_up" class="block text-sm font-medium text-neutral-300 mb-2">Threshold Up (%)</label>
                        <input type="number" name="threshold_up" id="threshold_up" value="{{ old('threshold_up', $policy->threshold_up) }}" step="0.01" max="100"
                               class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        @error('threshold_up')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="threshold_down" class="block text-sm font-medium text-neutral-300 mb-2">Threshold Down (%)</label>
                        <input type="number" name="threshold_down" id="threshold_down" value="{{ old('threshold_down', $policy->threshold_down) }}" step="0.01" max="100"
                               class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        @error('threshold_down')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="scale_up_by" class="block text-sm font-medium text-neutral-300 mb-2">Scale Up (adicionar)</label>
                        <input type="number" name="scale_up_by" id="scale_up_by" value="{{ old('scale_up_by', $policy->scale_up_by) }}" required min="1"
                               class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        @error('scale_up_by')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="scale_down_by" class="block text-sm font-medium text-neutral-300 mb-2">Scale Down (remover)</label>
                        <input type="number" name="scale_down_by" id="scale_down_by" value="{{ old('scale_down_by', $policy->scale_down_by) }}" required min="1"
                               class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        @error('scale_down_by')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="min_servers" class="block text-sm font-medium text-neutral-300 mb-2">Servidores Mín</label>
                        <input type="number" name="min_servers" id="min_servers" value="{{ old('min_servers', $policy->min_servers) }}" required min="1"
                               class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        @error('min_servers')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_servers" class="block text-sm font-medium text-neutral-300 mb-2">Servidores Máx</label>
                        <input type="number" name="max_servers" id="max_servers" value="{{ old('max_servers', $policy->max_servers) }}" required min="1"
                               class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        @error('max_servers')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="cooldown_minutes" class="block text-sm font-medium text-neutral-300 mb-2">Cooldown (minutos)</label>
                    <input type="number" name="cooldown_minutes" id="cooldown_minutes" value="{{ old('cooldown_minutes', $policy->cooldown_minutes) }}" required min="1"
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-neutral-500">Tempo de espera antes de executar outra ação de scaling</p>
                    @error('cooldown_minutes')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $policy->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 bg-neutral-700 border-neutral-600 rounded focus:ring-blue-500">
                    <label for="is_active" class="ml-2 text-sm text-neutral-300">Ativar política</label>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Salvar Alterações
                    </button>
                    <a href="{{ route('scaling.policies.show', $policy) }}" class="px-6 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-500">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>

        <div class="p-6 border-t border-neutral-700">
            <form action="{{ route('scaling.policies.destroy', $policy) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta política?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Excluir Política
                </button>
            </form>
        </div>
    </div>
</x-layout>
