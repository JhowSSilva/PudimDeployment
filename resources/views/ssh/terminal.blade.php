<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Terminal SSH') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-900 rounded-lg shadow-2xl overflow-hidden">
                <!-- Header com controles -->
                <div class="bg-gray-800 border-b border-gray-700 p-4">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                            <!-- Dropdown de servidores -->
                            <div class="w-full sm:w-auto">
                                <label class="block text-xs text-gray-400 mb-1">Servidor:</label>
                                <select id="server-select" class="bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600 focus:border-yellow-600 focus:ring focus:ring-yellow-600 focus:ring-opacity-50 w-full sm:w-64">
                                    <option value="">Selecione um servidor...</option>
                                </select>
                            </div>
                            
                            <!-- Dropdown de chaves SSH -->
                            <div class="w-full sm:w-auto">
                                <label class="block text-xs text-gray-400 mb-1">Chave SSH:</label>
                                <select id="key-select" class="bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600 focus:border-yellow-600 focus:ring focus:ring-yellow-600 focus:ring-opacity-50 w-full sm:w-64">
                                    <option value="">Selecione uma chave...</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Status de conexão -->
                            <span id="connection-status" class="flex items-center space-x-2 px-3 py-2 bg-gray-700 rounded-lg">
                                <span id="status-indicator" class="h-3 w-3 rounded-full bg-red-500"></span>
                                <span id="status-text" class="text-red-400 text-sm font-medium">Desconectado</span>
                            </span>
                            
                            <!-- Botões de ação -->
                            <button id="btn-connect" class="bg-yellow-600 hover:bg-yellow-700 text-black font-semibold px-4 py-2 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                Conectar
                            </button>
                            <button id="btn-disconnect" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Desconectar
                            </button>
                            <button id="btn-clear" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                Limpar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Área do terminal -->
                <div id="terminal" class="bg-black p-4 h-[600px] lg:h-[700px]"></div>
            </div>

            <!-- Links rápidos -->
            <div class="mt-6 flex flex-wrap gap-4">
                <a href="{{ route('ssh.keys') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white hover:bg-gray-700 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    Gerenciar Chaves SSH
                </a>
                <a href="{{ route('servers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white hover:bg-gray-700 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    Gerenciar Servidores
                </a>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
    <script src="{{ asset('js/ssh-terminal.js') }}"></script>
    @endpush
</x-app-layout>
