<x-layout title="Integrações">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Integrações') }}
        </h2>
        <a href="{{ route('cicd.integrations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Criar Integração
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-900/50 border border-green-500 text-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 text-neutral-300">
            @forelse($integrations as $integration)
                <div class="mb-4 p-4 bg-neutral-700 rounded-lg border border-neutral-600 hover:border-blue-500 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-white">{{ $integration->name }}</h3>
                                @if($integration->status === 'active')
                                    <span class="px-2 py-1 rounded text-xs bg-green-900 text-green-300">Ativo</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-neutral-600 text-neutral-400">Inativo</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <span class="px-3 py-1 rounded text-xs font-semibold
                                    {{ $integration->provider === 'slack' ? 'bg-purple-900 text-purple-300' : '' }}
                                    {{ $integration->provider === 'discord' ? 'bg-indigo-900 text-indigo-300' : '' }}
                                    {{ $integration->provider === 'github' ? 'bg-neutral-600 text-neutral-300' : '' }}
                                    {{ $integration->provider === 'gitlab' ? 'bg-orange-900 text-orange-300' : '' }}
                                    {{ $integration->provider === 'email' ? 'bg-blue-900 text-blue-300' : '' }}
                                    {{ $integration->provider === 'webhook' ? 'bg-green-900 text-green-300' : '' }}
                                    {{ $integration->provider === 'custom' ? 'bg-amber-900 text-amber-300' : '' }}">
                                    {{ ucfirst($integration->provider) }}
                                </span>
                            </div>
                            <p class="text-sm text-neutral-400">{{ $integration->description }}</p>
                        </div>
                        <div class="ml-4 flex gap-2">
                            <form action="{{ route('cicd.integrations.toggle', $integration) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 {{ $integration->status === 'active' ? 'bg-yellow-600' : 'bg-green-600' }} text-white rounded text-sm hover:opacity-80">
                                    {{ $integration->status === 'active' ? 'Desativar' : 'Ativar' }}
                                </button>
                            </form>
                            <form action="{{ route('cicd.integrations.test', $integration) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                    Testar
                                </button>
                            </form>
                            <a href="{{ route('cicd.integrations.show', $integration) }}" class="px-3 py-1 bg-neutral-600 text-white rounded text-sm hover:bg-neutral-500">
                                Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-neutral-500">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <p>Nenhuma integração criada ainda.</p>
                    <a href="{{ route('cicd.integrations.create') }}" class="text-blue-400 hover:text-blue-300 mt-2 inline-block">
                        Criar sua primeira integração →
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-layout>
