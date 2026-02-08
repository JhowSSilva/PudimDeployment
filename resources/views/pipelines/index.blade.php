<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pipelines CI/CD') }}
            </h2>
            <a href="{{ route('cicd.pipelines.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Novo Pipeline
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($pipelines->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum pipeline criado</h3>
                    <p class="mt-1 text-sm text-gray-500">Comece criando seu primeiro pipeline CI/CD.</p>
                    <div class="mt-6">
                        <a href="{{ route('cicd.pipelines.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Novo Pipeline
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pipelines as $pipeline)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <a href="{{ route('cicd.pipelines.show', $pipeline) }}" class="text-lg font-semibold text-gray-900 hover:text-blue-600">
                                            {{ $pipeline->name }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-1">
                                            @if($pipeline->status === 'active')
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Ativo</span>
                                            @elseif($pipeline->status === 'paused')
                                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pausado</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Desabilitado</span>
                                            @endif
                                            
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                {{ ucfirst($pipeline->trigger_type) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                @if($pipeline->description)
                                    <p class="text-sm text-gray-600 mb-4">{{ Str::limit($pipeline->description, 80) }}</p>
                                @endif

                                <div class="border-t pt-4 mt-4">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <div class="text-gray-500">Stages</div>
                                            <div class="font-semibold">{{ $pipeline->stages->count() }}</div>
                                        </div>
                                        <div>
                                            <div class="text-gray-500">Execuções</div>
                                            <div class="font-semibold">{{ $pipeline->runs_count }}</div>
                                        </div>
                                        <div class="col-span-2">
                                            <div class="text-gray-500">Taxa de Sucesso</div>
                                            <div class="font-semibold">{{ number_format($pipeline->getSuccessRate(), 1) }}%</div>
                                        </div>
                                    </div>

                                    @if($pipeline->last_run_at)
                                        <div class="mt-3 text-xs text-gray-500">
                                            Última execução: {{ $pipeline->last_run_at->diffForHumans() }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex gap-2 mt-4">
                                    @if($pipeline->canRun())
                                        <form action="{{ route('cicd.pipelines.run', $pipeline) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
                                                ▶ Executar
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('cicd.pipelines.show', $pipeline) }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm text-center">
                                        Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $pipelines->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
