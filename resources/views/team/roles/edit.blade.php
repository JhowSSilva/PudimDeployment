<x-layout title="Editar Role">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Editar Role') }}
        </h2>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <form action="{{ route('team.roles.update', $role) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">Nome</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-neutral-300 mb-2">Descrição</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">{{ old('description', $role->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Permissões</h3>
                    <div class="bg-neutral-700 rounded-lg p-4 space-y-4">
                        @foreach($permissionGroups as $group => $permissions)
                            <div>
                                <h4 class="text-white font-medium mb-2">{{ $group }}</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($permissions as $permission)
                                        <label class="flex items-center py-2 px-3 hover:bg-neutral-600 rounded">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission['value'] }}"
                                                   {{ in_array($permission['value'], old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                   class="w-4 h-4 text-blue-600 bg-neutral-700 border-neutral-600 rounded focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-neutral-300">{{ $permission['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Salvar Alterações
                    </button>
                    <a href="{{ route('team.roles.index') }}" class="px-6 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-500">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>

        @if(!$role->is_default)
            <div class="p-6 border-t border-neutral-700">
                <form action="{{ route('team.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este role? Usuários com este role terão suas permissões removidas.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Excluir Role
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-layout>
