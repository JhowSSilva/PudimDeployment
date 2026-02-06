<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold">
                                ✓
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-600">Credenciais AWS</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-green-600 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold">
                                ✓
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-600">Configurar Instância</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-green-600 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold">
                                3
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Stack</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-gray-300 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">
                                4
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Revisar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-8">
                <h2 class="text-2xl font-bold mb-2">Configure o Stack LEMP</h2>
                <p class="text-gray-600 mb-6">Escolha os componentes que serão instalados no servidor</p>

                <form action="{{ route('aws-provision.step4') }}" method="POST">
                    @csrf
                    <input type="hidden" name="aws_credential_id" value="{{ request('aws_credential_id') }}">
                    <input type="hidden" name="region" value="{{ request('region') }}">
                    <input type="hidden" name="instance_type" value="{{ request('instance_type') }}">
                    <input type="hidden" name="disk_size" value="{{ request('disk_size') }}">

                    <div class="space-y-6">
                        <!-- Webserver -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Servidor Web *
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative">
                                    <input type="radio" name="webserver" value="nginx" class="peer sr-only" checked required>
                                    <div class="border-2 border-gray-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 rounded-lg p-4 cursor-pointer">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-semibold text-lg">NGINX</h4>
                                            <svg class="w-6 h-6 text-blue-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-600">Recomendado para produção</p>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="webserver" value="apache" class="peer sr-only" required>
                                    <div class="border-2 border-gray-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 rounded-lg p-4 cursor-pointer">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-semibold text-lg">Apache</h4>
                                            <svg class="w-6 h-6 text-blue-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-600">Configuração via .htaccess</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- PHP Version -->
                        <div>
                            <label for="php_version" class="block text-sm font-medium text-gray-700 mb-2">
                                Versão do PHP *
                            </label>
                            <select name="php_version" id="php_version" class="w-full border border-gray-300 rounded px-3 py-2" required>
                                <option value="8.4">PHP 8.4 (Mais recente)</option>
                                <option value="8.3" selected>PHP 8.3 (Recomendado)</option>
                                <option value="8.2">PHP 8.2</option>
                                <option value="8.1">PHP 8.1</option>
                            </select>
                        </div>

                        <!-- Database -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Banco de Dados *
                            </label>
                            <div class="grid grid-cols-3 gap-4">
                                <label class="relative">
                                    <input type="radio" name="database" value="mysql" class="peer sr-only" checked required>
                                    <div class="border-2 border-gray-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 rounded-lg p-4 cursor-pointer text-center">
                                        <h4 class="font-semibold mb-1">MySQL 8.0</h4>
                                        <svg class="w-6 h-6 text-blue-600 mx-auto hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="database" value="postgresql" class="peer sr-only" required>
                                    <div class="border-2 border-gray-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 rounded-lg p-4 cursor-pointer text-center">
                                        <h4 class="font-semibold mb-1">PostgreSQL 15</h4>
                                        <svg class="w-6 h-6 text-blue-600 mx-auto hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="database" value="none" class="peer sr-only" required>
                                    <div class="border-2 border-gray-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 rounded-lg p-4 cursor-pointer text-center">
                                        <h4 class="font-semibold mb-1">Nenhum</h4>
                                        <svg class="w-6 h-6 text-blue-600 mx-auto hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Cache -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Cache *
                            </label>
                            <div class="grid grid-cols-3 gap-4">
                                <label class="relative">
                                    <input type="radio" name="cache" value="redis" class="peer sr-only" checked required>
                                    <div class="border-2 border-gray-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 rounded-lg p-4 cursor-pointer text-center">
                                        <h4 class="font-semibold mb-1">Redis</h4>
                                        <svg class="w-6 h-6 text-blue-600 mx-auto hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="cache" value="memcached" class="peer sr-only" required>
                                    <div class="border-2 border-gray-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 rounded-lg p-4 cursor-pointer text-center">
                                        <h4 class="font-semibold mb-1">Memcached</h4>
                                        <svg class="w-6 h-6 text-blue-600 mx-auto hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="cache" value="none" class="peer sr-only" required>
                                    <div class="border-2 border-gray-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 rounded-lg p-4 cursor-pointer text-center">
                                        <h4 class="font-semibold mb-1">Nenhum</h4>
                                        <svg class="w-6 h-6 text-blue-600 mx-auto hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Node.js (Optional) -->
                        <div>
                            <label for="nodejs" class="block text-sm font-medium text-gray-700 mb-2">
                                Node.js (Opcional)
                            </label>
                            <select name="nodejs" id="nodejs" class="w-full border border-gray-300 rounded px-3 py-2">
                                <option value="">Não instalar</option>
                                <option value="22">Node.js 22.x (LTS)</option>
                                <option value="20" selected>Node.js 20.x (LTS)</option>
                                <option value="18">Node.js 18.x (LTS)</option>
                            </select>
                        </div>

                        <!-- Extras -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Extras (Opcional)
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="extras[]" value="supervisor" class="rounded border-gray-300 text-blue-600" checked>
                                    <span class="ml-2 text-sm">Supervisor (gerenciador de processos)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="extras[]" value="docker" class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm">Docker (containers)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-6 border-t mt-8">
                        <button type="button" onclick="history.back()" class="text-gray-600 hover:text-gray-800">
                            ← Voltar
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-medium">
                            Revisar Configuração →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
