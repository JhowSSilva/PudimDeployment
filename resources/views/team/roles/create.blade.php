<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                Criar Função Personalizada
            </h2>
            <a href="{{ route('team.roles.index') }}" class="text-sm text-gray-600 hover:text-gray-900 transition">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('team.roles.store') }}" class="space-y-6">
                @csrf

                <!-- Basic Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informações Básicas</h3>
                        
                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome da Função <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                id="name" 
                                value="{{ old('name') }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Ex: Desenvolvedor, Designer, Suporte..."
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição
                            </label>
                            <textarea 
                                name="description" 
                                id="description" 
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Descreva as responsabilidades desta função..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Color -->
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                                Cor da Etiqueta
                            </label>
                            <div class="flex items-center gap-3">
                                <input 
                                    type="color" 
                                    name="color" 
                                    id="color" 
                                    value="{{ old('color', '#3b82f6') }}"
                                    class="h-10 w-20 rounded border border-gray-300 cursor-pointer"
                                >
                                <span class="text-sm text-gray-500">Escolha uma cor para identificar esta função</span>
                            </div>
                            @error('color')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Permissions Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Permissões</h3>
                            <div class="flex gap-2">
                                <button 
                                    type="button" 
                                    onclick="selectAll()"
                                    class="text-xs px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition"
                                >
                                    Selecionar Todas
                                </button>
                                <button 
                                    type="button" 
                                    onclick="deselectAll()"
                                    class="text-xs px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition"
                                >
                                    Desmarcar Todas
                                </button>
                            </div>
                        </div>

                        @error('permissions')
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            </div>
                        @enderror

                        <div class="space-y-6">
                            @foreach($permissions as $category => $categoryPermissions)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                        {{ ucfirst(str_replace('_', ' ', $category)) }}
                                        <span class="ml-2 text-xs text-gray-500">({{ count($categoryPermissions) }} permissões)</span>
                                    </h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($categoryPermissions as $permission)
                                            <label class="flex items-start p-3 hover:bg-gray-50 rounded-md cursor-pointer transition">
                                                <input 
                                                    type="checkbox" 
                                                    name="permissions[]" 
                                                    value="{{ $permission->slug }}"
                                                    {{ in_array($permission->slug, old('permissions', [])) ? 'checked' : '' }}
                                                    class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 permission-checkbox"
                                                >
                                                <div class="ml-3 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-medium text-gray-900">{{ $permission->name }}</span>
                                                        @if($permission->is_dangerous)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                ⚠️ Perigosa
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if($permission->description)
                                                        <p class="text-xs text-gray-500 mt-0.5">{{ $permission->description }}</p>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Atenção:</strong> Permissões marcadas como "Perigosas" podem realizar ações irreversíveis. Atribua com cuidado.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <a 
                        href="{{ route('team.roles.index') }}" 
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition"
                    >
                        Cancelar
                    </a>
                    <button 
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition"
                    >
                        Criar Função
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selectAll() {
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        function deselectAll() {
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    </script>
</x-app-layout>
