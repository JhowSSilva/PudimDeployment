<x-layout title="Editar Server Pool">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Editar Server Pool') }}
        </h2>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <form action="{{ route('scaling.pools.update', $pool) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">Nome</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $pool->name) }}" required
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                    @error('name')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-neutral-300 mb-2">Descrição</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">{{ old('description', $pool->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-neutral-300 mb-2">Status</label>
                    <select name="status" id="status" required
                            class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                        <option value="active" {{ old('status', $pool->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ old('status', $pool->status) == 'inactive' ? 'selected' : '' }}>Inativo</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="min_servers" class="block text-sm font-medium text-neutral-300 mb-2">Servidores Mínimos</label>
                        <input type="number" name="min_servers" id="min_servers" value="{{ old('min_servers', $pool->min_servers) }}" required min="1"
                               class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                        @error('min_servers')
                            <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_servers" class="block text-sm font-medium text-neutral-300 mb-2">Servidores Máximos</label>
                        <input type="number" name="max_servers" id="max_servers" value="{{ old('max_servers', $pool->max_servers) }}" required min="1"
                               class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                        @error('max_servers')
                            <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-300 mb-2">Servidores</label>
                    <div class="bg-neutral-700 rounded-lg p-4 max-h-60 overflow-y-auto">
                        @forelse($servers as $server)
                            <label class="flex items-center py-2 hover:bg-neutral-600 px-2 rounded">
                                <input type="checkbox" name="servers[]" value="{{ $server->id }}"
                                       {{ in_array($server->id, $selectedServers) ? 'checked' : '' }}
                                       class="w-4 h-4 text-info-400 bg-neutral-700 border-neutral-600 rounded focus:ring-primary-500">
                                <span class="ml-2 text-sm text-neutral-300">{{ $server->name }} ({{ $server->ip_address }})</span>
                            </label>
                        @empty
                            <p class="text-neutral-500 text-sm">Nenhum servidor disponível</p>
                        @endforelse
                    </div>
                    @error('servers')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="px-6 py-2 bg-info-600 text-white rounded-lg hover:bg-info-700">
                        Salvar Alterações
                    </button>
                    <a href="{{ route('scaling.pools.show', $pool) }}" class="px-6 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-7000">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>

        <div class="p-6 border-t border-neutral-700">
            <form action="{{ route('scaling.pools.destroy', $pool) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este pool?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-6 py-2 bg-error-500 text-white rounded-lg hover:bg-error-700">
                    Excluir Server Pool
                </button>
            </form>
        </div>
    </div>
</x-layout>
