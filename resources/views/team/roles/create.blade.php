<x-layout title="Criar Role">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Criar Role') }}
        </h2>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <form action="{{ route('team.roles.store') }}" method="POST" class="p-6">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">Nome</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                    @error('name')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-neutral-300 mb-2">Descrição</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
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
                                                   {{ in_array($permission['value'], old('permissions', [])) ? 'checked' : '' }}
                                                   class="w-4 h-4 text-info-400 bg-neutral-700 border-neutral-600 rounded focus:ring-primary-500">
                                            <span class="ml-2 text-sm text-neutral-300">{{ $permission['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions')
                        <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="px-6 py-2 bg-info-600 text-white rounded-lg hover:bg-info-700">
                        Criar Role
                    </button>
                    <a href="{{ route('team.roles.index') }}" class="px-6 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-7000">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-layout>
