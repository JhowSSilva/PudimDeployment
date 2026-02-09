<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold">
                                1
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Credenciais AWS</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-neutral-300 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-neutral-300 text-neutral-600 font-semibold">
                                2
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-neutral-500">Configurar Instância</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-neutral-300 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-neutral-300 text-neutral-600 font-semibold">
                                3
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-neutral-500">Stack</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-neutral-300 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-neutral-300 text-neutral-600 font-semibold">
                                4
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-neutral-500">Revisar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-8">
                <h2 class="text-2xl font-bold mb-2">Selecione suas Credenciais AWS</h2>
                <p class="text-neutral-600 mb-6">Escolha a conta AWS que será usada para provisionar o servidor</p>

                @if($credentials->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-yellow-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="text-neutral-800 font-medium mb-2">Nenhuma credencial AWS configurada</p>
                        <p class="text-neutral-600 mb-4">Você precisa adicionar uma conta AWS antes de provisionar servidores</p>
                        <a href="{{ route('aws-credentials.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-medium">
                            + Adicionar Credencial AWS
                        </a>
                    </div>
                @else
                    <form action="{{ route('aws-provision.step2') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            @foreach($credentials as $credential)
                                <label class="relative">
                                    <input 
                                        type="radio" 
                                        name="aws_credential_id" 
                                        value="{{ $credential->id }}"
                                        class="peer sr-only"
                                        required
                                    >
                                    <div class="border-2 border-neutral-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 rounded-lg p-4 cursor-pointer hover:border-neutral-400 transition">
                                        <div class="flex items-start justify-between mb-2">
                                            <h3 class="font-semibold text-lg">{{ $credential->name }}</h3>
                                            <svg class="w-6 h-6 text-blue-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-neutral-600 mb-2">{{ $credential->default_region }}</p>
                                        <div class="flex items-center text-sm text-neutral-500">
                                            <code class="text-xs bg-neutral-100 px-2 py-1 rounded">{{ $credential->masked_access_key }}</code>
                                        </div>
                                        @if($credential->description)
                                            <p class="text-sm text-neutral-600 mt-2">{{ $credential->description }}</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @error('aws_credential_id')
                            <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                        @enderror

                        <div class="flex justify-between items-center pt-6 border-t">
                            <a href="{{ route('servers.index') }}" class="text-neutral-600 hover:text-neutral-800">
                                ← Cancelar
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-medium">
                                Próxima Etapa →
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-layout>
