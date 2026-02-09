<x-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-100">Credenciais DigitalOcean</h1>
                    <p class="text-neutral-400 mt-1">Gerencie suas credenciais DigitalOcean</p>
                </div>
                <a href="{{ route('digitalocean-credentials.create') }}" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg transition-all">
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
                <x-empty-state title="Nenhuma credencial DigitalOcean">
                    <x-slot:icon>
                        <svg class="w-12 h-12 text-info-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 24v-5.294h5.294C17.294 23.988 12 24 12 24zm0-5.294H6.706v-5.294H12v5.294zM6.706 13.412H1.412v-5.294h5.294v5.294zm5.294-5.294H6.706V2.824h5.294v5.294z"/>
                        </svg>
                    </x-slot:icon>
                    <p class="text-neutral-400 mb-6">Adicione suas credenciais DigitalOcean para provisionar Droplets</p>
                    <x-button href="{{ route('digitalocean-credentials.create') }}" variant="primary">
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
                                <div class="w-10 h-10 bg-info-900/30 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-info-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 24v-5.294h5.294C17.294 23.988 12 24 12 24zm0-5.294H6.706v-5.294H12v5.294zM6.706 13.412H1.412v-5.294h5.294v5.294zm5.294-5.294H6.706V2.824h5.294v5.294z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-neutral-100">{{ $credential->name }}</h3>
                                    <p class="text-sm text-neutral-500">{{ $credential->region_name }}</p>
                                </div>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-neutral-500">API Token:</span>
                                    <span class="font-mono text-xs">{{ Str::mask($credential->api_token, '*', 8) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-neutral-500">Criado:</span>
                                    <span class="text-xs">{{ $credential->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('digitalocean-credentials.edit', $credential) }}" class="flex-1 text-center bg-neutral-700 hover:bg-neutral-200 text-neutral-300 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                                    Editar
                                </a>
                                <form action="{{ route('digitalocean-credentials.destroy', $credential) }}" method="POST" class="flex-1" onsubmit="return confirm('Tem certeza?')">
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