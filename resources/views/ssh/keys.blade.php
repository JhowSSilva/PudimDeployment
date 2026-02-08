<x-layout title="Chaves SSH">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Chaves SSH') }}
        </h2>
        <button @click="showModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            Nova Chave SSH
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-900/50 border border-green-500 text-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            @forelse($sshKeys as $key)
                <div class="mb-4 p-4 bg-neutral-700 rounded-lg border border-neutral-600 hover:border-blue-500 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white mb-2">{{ $key->name }}</h3>
                            
                            <div class="space-y-2 text-sm text-neutral-400">
                                <div>
                                    <span>Fingerprint:</span>
                                    <span class="text-white ml-1 font-mono text-xs">{{ $key->fingerprint }}</span>
                                </div>
                                <div>
                                    <span>Criada:</span>
                                    <span class="text-white ml-1">{{ $key->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($key->servers_count > 0)
                                    <div>
                                        <span>Usada em:</span>
                                        <span class="text-white ml-1">{{ $key->servers_count }} servidor(es)</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3">
                                <button @click="showKey{{ $key->id }} = !showKey{{ $key->id }}" class="text-sm text-blue-400 hover:text-blue-300">
                                    <span x-text="showKey{{ $key->id }} ? 'Ocultar chave' : 'Ver chave pública'"></span>
                                </button>
                                <div x-show="showKey{{ $key->id }}" x-cloak class="mt-2 p-3 bg-neutral-900 rounded overflow-x-auto">
                                    <code class="text-xs text-green-400">{{ $key->public_key }}</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ml-4">
                            <form action="{{ route('ssh.keys.destroy', $key) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta chave?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-neutral-500">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <p>Nenhuma chave SSH configurada.</p>
                </div>
            @endforelse

            @if($sshKeys->hasPages())
                <div class="mt-4">
                    {{ $sshKeys->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal -->
    <div x-data="{ showModal: false, @foreach($sshKeys as $key) showKey{{ $key->id }}: false, @endforeach }"
         x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-neutral-900 bg-opacity-75 transition-opacity"
                 @click="showModal = false"></div>

            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-neutral-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <form action="{{ route('ssh.keys.store') }}" method="POST" class="p-6">
                    @csrf
                    <h3 class="text-lg font-semibold text-white mb-4">Nova Chave SSH</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">Nome</label>
                            <input type="text" name="name" id="name" required
                                   class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="public_key" class="block text-sm font-medium text-neutral-300 mb-2">Chave Pública</label>
                            <textarea name="public_key" id="public_key" rows="5" required placeholder="ssh-rsa AAAAB3..."
                                      class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white font-mono text-xs focus:ring-2 focus:ring-blue-500"></textarea>
                            <p class="mt-1 text-xs text-neutral-500">Cole o conteúdo da sua chave pública SSH (normalmente ~/.ssh/id_rsa.pub)</p>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Adicionar Chave
                        </button>
                        <button type="button" @click="showModal = false" class="px-6 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-500">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
