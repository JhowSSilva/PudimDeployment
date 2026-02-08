<x-layout title="Novo Load Balancer">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Novo Load Balancer') }}
        </h2>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <form action="{{ route('scaling.load-balancers.store') }}" method="POST" class="p-6">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">Nome</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="server_pool_id" class="block text-sm font-medium text-neutral-300 mb-2">Server Pool</label>
                    <select name="server_pool_id" id="server_pool_id" required
                            class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione um pool</option>
                        @foreach($serverPools as $pool)
                            <option value="{{ $pool->id }}" {{ old('server_pool_id') == $pool->id ? 'selected' : '' }}>
                                {{ $pool->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('server_pool_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="algorithm" class="block text-sm font-medium text-neutral-300 mb-2">Algoritmo</label>
                    <select name="algorithm" id="algorithm" required
                            class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        <option value="round_robin" {{ old('algorithm') == 'round_robin' ? 'selected' : '' }}>Round Robin</option>
                        <option value="least_connections" {{ old('algorithm') == 'least_connections' ? 'selected' : '' }}>Least Connections</option>
                        <option value="ip_hash" {{ old('algorithm') == 'ip_hash' ? 'selected' : '' }}>IP Hash</option>
                        <option value="weighted" {{ old('algorithm') == 'weighted' ? 'selected' : '' }}>Weighted</option>
                    </select>
                    @error('algorithm')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ip_address" class="block text-sm font-medium text-neutral-300 mb-2">Endereço IP</label>
                    <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address') }}" required
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                    @error('ip_address')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ports" class="block text-sm font-medium text-neutral-300 mb-2">Portas (separadas por vírgula)</label>
                    <input type="text" name="ports" id="ports" value="{{ old('ports', '80, 443') }}" required
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                           placeholder="80, 443">
                    @error('ports')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="ssl_enabled" value="1" {{ old('ssl_enabled') ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 bg-neutral-700 border-neutral-600 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-neutral-300">SSL Habilitado</span>
                    </label>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="health_check_enabled" value="1" {{ old('health_check_enabled', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 bg-neutral-700 border-neutral-600 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-neutral-300">Health Check Habilitado</span>
                    </label>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Criar Load Balancer
                    </button>
                    <a href="{{ route('scaling.load-balancers.index') }}" class="px-6 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-500 transition-colors">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-layout>
