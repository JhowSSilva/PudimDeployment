<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Meus Repositórios GitHub</h1>
                    <p class="text-neutral-300 mt-1">Gerencie e configure deploys para seus repositórios</p>
                </div>
                <form action="{{ route('github.repositories.sync') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        🔄 Sincronizar Repositórios
                    </button>
                </form>
            </div>
        </div>

        @if($repositories->isEmpty())
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Nenhum repositório encontrado</h3>
                <p class="text-gray-500 mb-4">Clique em "Sincronizar Repositórios" para importar seus repositórios do GitHub</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($repositories as $repo)
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center">
                                @if($repo->private)
                                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                    </svg>
                                @endif
                                <h3 class="font-semibold text-lg truncate">{{ $repo->name }}</h3>
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                            {{ $repo->description ?? 'Sem descrição' }}
                        </p>

                        <div class="flex items-center gap-3 text-xs text-gray-500 mb-4">
                            @if($repo->language)
                                <span class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-blue-500 mr-1"></span>
                                    {{ $repo->language }}
                                </span>
                            @endif
                            <span>⭐ {{ $repo->stars_count ?? 0 }}</span>
                            <span>🍴 {{ $repo->forks_count ?? 0 }}</span>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('github.repositories.show', $repo) }}" class="flex-1 bg-blue-600 text-white text-center px-3 py-2 rounded text-sm hover:bg-blue-700 transition">
                                Gerenciar
                            </a>
                            <a href="https://github.com/{{ $repo->full_name }}" target="_blank" class="bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-300 transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $repositories->links() }}
            </div>
        @endif
    </div>
</x-layout>
