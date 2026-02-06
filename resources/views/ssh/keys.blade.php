<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Chaves SSH') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Minhas Chaves SSH</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Gerencie suas chaves SSH para autenticação em servidores</p>
                    </div>
                    <div class="flex space-x-2">
                        <button id="btn-import-key" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-4 py-2 rounded-lg transition duration-200">
                            Importar Chave
                        </button>
                        <button id="btn-generate-key" class="bg-yellow-600 hover:bg-yellow-700 text-black font-semibold px-4 py-2 rounded-lg transition duration-200">
                            + Gerar Nova Chave
                        </button>
                    </div>
                </div>
                
                <!-- Lista de chaves -->
                <div id="keys-list" class="space-y-4">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Carregando chaves SSH...</p>
                    </div>
                </div>
            </div>

            <!-- Link para terminal -->
            <div class="mt-6">
                <a href="{{ route('ssh.terminal') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white hover:bg-gray-700 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Abrir Terminal SSH
                </a>
            </div>
        </div>
    </div>

    <!-- Modal: Gerar Nova Chave -->
    <div id="modal-generate" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-2xl p-6 w-full max-w-md m-4">
            <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Gerar Nova Chave SSH</h3>
            
            <form id="form-generate-key" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nome da Chave *
                    </label>
                    <input type="text" name="name" required class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:border-yellow-600 focus:ring focus:ring-yellow-600 focus:ring-opacity-50 text-gray-900 dark:text-white" placeholder="ex: producao_server">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tipo de Chave *
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                            <input type="radio" name="type" value="rsa" checked class="mr-3 text-yellow-600 focus:ring-yellow-600">
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">RSA 4096 bits</span>
                                <p class="text-xs text-gray-500">Compatível e seguro (recomendado)</p>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                            <input type="radio" name="type" value="ed25519" class="mr-3 text-yellow-600 focus:ring-yellow-600">
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">ED25519</span>
                                <p class="text-xs text-gray-500">Moderno e mais rápido</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Passphrase (Opcional, recomendado)
                    </label>
                    <input type="password" name="passphrase" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:border-yellow-600 focus:ring focus:ring-yellow-600 focus:ring-opacity-50 text-gray-900 dark:text-white" placeholder="Deixe em branco para não usar">
                    <p class="text-xs text-gray-500 mt-1">Adiciona uma camada extra de segurança</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Comentário/Email
                    </label>
                    <input type="text" name="comment" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:border-yellow-600 focus:ring focus:ring-yellow-600 focus:ring-opacity-50 text-gray-900 dark:text-white" placeholder="admin@pudimdeployment.com">
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-black font-semibold px-4 py-2 rounded-lg transition duration-200">
                        Gerar Chave
                    </button>
                    <button type="button" id="btn-cancel-generate" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg transition duration-200">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Importar Chave -->
    <div id="modal-import" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-2xl p-6 w-full max-w-md m-4">
            <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Importar Chave SSH</h3>
            
            <form id="form-import-key" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nome da Chave *
                    </label>
                    <input type="text" name="name" required class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:border-yellow-600 focus:ring focus:ring-yellow-600 focus:ring-opacity-50 text-gray-900 dark:text-white" placeholder="ex: minha_chave">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Chave Privada *
                    </label>
                    <textarea name="private_key" required rows="8" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:border-yellow-600 focus:ring focus:ring-yellow-600 focus:ring-opacity-50 font-mono text-xs text-gray-900 dark:text-white" placeholder="-----BEGIN RSA PRIVATE KEY-----&#10;...&#10;-----END RSA PRIVATE KEY-----"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Cole o conteúdo completo da chave privada</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Passphrase (se houver)
                    </label>
                    <input type="password" name="passphrase" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:border-yellow-600 focus:ring focus:ring-yellow-600 focus:ring-opacity-50 text-gray-900 dark:text-white">
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-black font-semibold px-4 py-2 rounded-lg transition duration-200">
                        Importar
                    </button>
                    <button type="button" id="btn-cancel-import" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg transition duration-200">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Ver Chave Pública -->
    <div id="modal-view-public" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-2xl p-6 w-full max-w-2xl m-4">
            <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Chave Pública SSH</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Chave Pública
                    </label>
                    <textarea id="public-key-content" readonly rows="6" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 font-mono text-xs text-gray-900 dark:text-white"></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button id="btn-copy-public" class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-black font-semibold px-4 py-2 rounded-lg transition duration-200">
                        Copiar
                    </button>
                    <button id="btn-close-public" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg transition duration-200">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/ssh-keys.js') }}"></script>
    @endpush
</x-app-layout>
