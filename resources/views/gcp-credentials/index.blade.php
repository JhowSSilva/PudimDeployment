<x-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900">Credenciais Google Cloud</h1>
                    <p class="text-neutral-600 mt-1">Gerencie suas credenciais Google Cloud Platform</p>
                </div>
                <a href="{{ route('gcp-credentials.create') }}" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nova Credencial
                </a>
            </div>

            @if(session('success'))
                <div class="bg-success-50 border border-success-200 text-success-800 px-5 py-4 rounded-xl mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-error-50 border border-error-200 text-error-700 px-5 py-4 rounded-xl mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            @if($credentials->isEmpty())
                <x-empty-state title="Nenhuma credencial GCP">
                    <x-slot:icon>
                        <svg class="w-12 h-12" viewBox="0 0 24 24">
                            <path fill="#EA4335" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#4285F4" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#34A853" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    </x-slot:icon>
                    <p class="text-neutral-600 mb-6">Adicione suas credenciais GCP para provisionar Compute Engine</p>
                    <x-button href="{{ route('gcp-credentials.create') }}" variant="primary">
                        Adicionar Credencial
                    </x-button>
                </x-empty-state>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($credentials as $credential)
                        <x-card class="relative">
                            @if($credential->is_default)
                                <div class="absolute top-4 right-4">
                                    <span class="bg-primary-100 text-primary-800 text-xs font-semibold px-2 py-1 rounded-full">Padrão</span>
                                </div>
                            @endif
                            
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-100 to-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24">
                                        <path fill="#EA4335" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#4285F4" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#34A853" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-neutral-900">{{ $credential->name }}</h3>
                                    <p class="text-sm text-neutral-500">{{ $credential->region_name }}</p>
                                </div>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-neutral-500">Project ID:</span>
                                    <span class="font-mono text-xs">{{ $credential->project_id }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-neutral-500">Criado:</span>
                                    <span class="text-xs">{{ $credential->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('gcp-credentials.edit', $credential) }}" class="flex-1 text-center bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                                    Editar
                                </a>
                                <form action="{{ route('gcp-credentials.destroy', $credential) }}" method="POST" class="flex-1" onsubmit="return confirm('Tem certeza?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-error-100 hover:bg-error-200 text-error-700 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layout>