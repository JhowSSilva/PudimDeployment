<x-layout title="Editar Load Balancer">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Editar Load Balancer') }}
        </h2>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <form action="{{ route('scaling.load-balancers.update', $loadBalancer) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">Nome</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $loadBalancer->name) }}" required
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                    @error('name')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="server_pool_id" class="block text-sm font-medium text-neutral-300 mb-2">Server Pool</label>
                    <select name="server_pool_id" id="server_pool_id" required
                            class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">Selecione um pool</option>
                        @foreach($serverPools as $pool)
                            <option value="{{ $pool->id }}" {{ old('server_pool_id', $loadBalancer->server_pool_id) == $pool->id ? 'selected' : '' }}>
                                {{ $pool->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('server_pool_id')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="algorithm" class="block text-sm font-medium text-neutral-300 mb-2">Algoritmo</label>
                    <select name="algorithm" id="algorithm" required
                            class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                        <option value="round_robin" {{ old('algorithm', $loadBalancer->algorithm) == 'round_robin' ? 'selected' : '' }}>Round Robin</option>
                        <option value="least_connections" {{ old('algorithm', $loadBalancer->algorithm) == 'least_connections' ? 'selected' : '' }}>Least Connections</option>
                        <option value="ip_hash" {{ old('algorithm', $loadBalancer->algorithm) == 'ip_hash' ? 'selected' : '' }}>IP Hash</option>
                        <option value="weighted" {{ old('algorithm', $loadBalancer->algorithm) == 'weighted' ? 'selected' : '' }}>Weighted</option>
                    </select>
                    @error('algorithm')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-neutral-300 mb-2">Status</label>
                    <select name="status" id="status" required
                            class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                        <option value="active" {{ old('status', $loadBalancer->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ old('status', $loadBalancer->status) == 'inactive' ? 'selected' : '' }}>Inativo</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ip_address" class="block text-sm font-medium text-neutral-300 mb-2">Endereço IP</label>
                    <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', $loadBalancer->ip_address) }}" required
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                    @error('ip_address')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ports" class="block text-sm font-medium text-neutral-300 mb-2">Portas (separadas por vírgula)</label>
                    <input type="text" name="ports" id="ports" value="{{ old('ports', implode(', ', $loadBalancer->ports ?? [])) }}" required
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                    @error('ports')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="ssl_enabled" value="1" {{ old('ssl_enabled', $loadBalancer->ssl_enabled) ? 'checked' : '' }}
                               class="w-4 h-4 text-info-400 bg-neutral-700 border-neutral-600 rounded focus:ring-primary-500">
                        <span class="ml-2 text-sm text-neutral-300">SSL Habilitado</span>
                    </label>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="health_check_enabled" value="1" {{ old('health_check_enabled', $loadBalancer->health_check_enabled) ? 'checked' : '' }}
                               class="w-4 h-4 text-info-400 bg-neutral-700 border-neutral-600 rounded focus:ring-primary-500">
                        <span class="ml-2 text-sm text-neutral-300">Health Check Habilitado</span>
                    </label>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="px-6 py-2 bg-info-600 text-white rounded-lg hover:bg-info-700">
                        Salvar Alterações
                    </button>
                    <a href="{{ route('scaling.load-balancers.show', $loadBalancer) }}" class="px-6 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-7000">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>

        <div class="p-6 border-t border-neutral-700">
            <form action="{{ route('scaling.load-balancers.destroy', $loadBalancer) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este load balancer?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-6 py-2 bg-error-500 text-white rounded-lg hover:bg-error-700">
                    Excluir Load Balancer
                </button>
            </form>
        </div>
    </div>
</x-layout>
