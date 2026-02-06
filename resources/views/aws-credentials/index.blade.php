<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-turquoise-600 to-turquoise-500 bg-clip-text text-transparent">Credenciais AWS</h1>
                <p class="text-gray-600 mt-1">Gerencie suas chaves de acesso AWS EC2</p>
            </div>
            <a href="{{ route('aws-credentials.create') }}" class="inline-flex items-center bg-gradient-to-r from-turquoise-500 to-turquoise-600 hover:from-turquoise-600 hover:to-turquoise-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg shadow-turquoise-500/30 hover:shadow-xl hover:shadow-turquoise-500/40 transition-all transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nova Credencial
            </a>
        </div>

        @if(session('success'))
            <div class="bg-gradient-to-r from-turquoise-50 to-green-50 border border-turquoise-200 text-turquoise-800 px-5 py-4 rounded-xl mb-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-turquoise-500 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span class="font-semibold">{{ $errors->first() }}</span>
                </div>
            </div>
        @endif

        @if($credentials->isEmpty())
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-12 text-center border border-gray-200">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-turquoise-100 to-turquoise-200 flex items-center justify-center">
                    <svg class="w-10 h-10 text-turquoise-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Nenhuma credencial AWS configurada</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">Adicione suas credenciais AWS para começar a provisionar servidores EC2 automaticamente</p>
                <a href="{{ route('aws-credentials.create') }}" class="inline-flex items-center bg-gradient-to-r from-turquoise-500 to-turquoise-600 hover:from-turquoise-600 hover:to-turquoise-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg shadow-turquoise-500/30 hover:shadow-xl hover:shadow-turquoise-500/40 transition-all transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Adicionar primeira credencial
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($credentials as $credential)
                    <div class="group bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 border border-gray-200 hover:border-turquoise-300 transform hover:scale-[1.02]">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $credential->name }}</h3>
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1.5 text-turquoise-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $credential->default_region }}
                                </div>
                            </div>
                            @if($credential->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-green-100 to-emerald-100 text-green-700 border border-green-200">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                    Ativa
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                    Inativa
                                </span>
                            @endif
                        </div>

                        @if($credential->description)
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $credential->description }}</p>
                        @endif

                        <div class="space-y-3 mb-5">
                            <div class="flex items-center text-sm bg-gray-50 rounded-lg p-2.5">
                                <svg class="w-4 h-4 text-turquoise-500 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                <code class="text-xs bg-white px-2.5 py-1 rounded font-mono text-gray-700 border border-gray-200">{{ $credential->masked_access_key }}</code>
                            </div>
                            
                            <div class="flex items-center text-sm">
                                <div class="w-8 h-8 rounded-lg bg-turquoise-100 flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-turquoise-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-gray-900 font-semibold">{{ $credential->servers_count }}</span>
                                    <span class="text-gray-600 ml-1">servidor(es)</span>
                                </div>
                            </div>

                            @if($credential->last_validated_at)
                                <div class="flex items-center text-xs text-gray-500">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Validado {{ $credential->last_validated_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>

                        <div class="flex space-x-2 pt-4 border-t border-gray-100">
                            <a href="{{ route('aws-credentials.edit', $credential) }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
                                Editar
                            </a>
                            <form action="{{ route('aws-credentials.destroy', $credential) }}" method="POST" class="flex-1" onsubmit="return confirm('Tem certeza que deseja excluir esta credencial?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layout>
