<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('aws-credentials.index') }}" class="text-info-400 hover:text-info-400 mb-2 inline-block">
                    ← Voltar
                </a>
                <h1 class="text-3xl font-bold">Editar Credencial AWS</h1>
            </div>

            <form action="{{ route('aws-credentials.update', $awsCredential) }}" method="POST" class="bg-neutral-800 rounded-lg shadow p-6">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-300 mb-1">
                            Nome da Credencial *
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name"
                            value="{{ old('name', $awsCredential->name) }}"
                            class="w-full border border-neutral-600 rounded px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror"
                            required
                        >
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-neutral-900 border border-neutral-700 rounded p-4">
                        <p class="text-sm text-neutral-300 mb-3">
                            <strong>Chave atual:</strong> <code class="bg-neutral-700 px-2 py-1 rounded text-xs">{{ $awsCredential->masked_access_key }}</code>
                        </p>
                        <p class="text-sm text-neutral-400 mb-3">
                            Deixe os campos abaixo vazios para manter as credenciais atuais.
                        </p>

                        <div class="space-y-3">
                            <div>
                                <label for="access_key_id" class="block text-sm font-medium text-neutral-300 mb-1">
                                    Novo Access Key ID (opcional)
                                </label>
                                <input 
                                    type="text" 
                                    name="access_key_id" 
                                    id="access_key_id"
                                    value="{{ old('access_key_id') }}"
                                    class="w-full border border-neutral-600 rounded px-3 py-2 font-mono text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('access_key_id') border-red-500 @enderror"
                                    placeholder="AKIAIOSFODNN7EXAMPLE"
                                >
                                @error('access_key_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="secret_access_key" class="block text-sm font-medium text-neutral-300 mb-1">
                                    Novo Secret Access Key (opcional)
                                </label>
                                <input 
                                    type="password" 
                                    name="secret_access_key" 
                                    id="secret_access_key"
                                    value="{{ old('secret_access_key') }}"
                                    class="w-full border border-neutral-600 rounded px-3 py-2 font-mono text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('secret_access_key') border-red-500 @enderror"
                                    placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
                                >
                                @error('secret_access_key')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="default_region" class="block text-sm font-medium text-neutral-300 mb-1">
                            Região Padrão *
                        </label>
                        <select 
                            name="default_region" 
                            id="default_region"
                            class="w-full border border-neutral-600 rounded px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('default_region') border-red-500 @enderror"
                            required
                        >
                            @foreach($regions as $code => $name)
                                <option value="{{ $code }}" {{ old('default_region', $awsCredential->default_region) === $code ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('default_region')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-neutral-300 mb-1">
                            Descrição (opcional)
                        </label>
                        <textarea 
                            name="description" 
                            id="description"
                            rows="3"
                            class="w-full border border-neutral-600 rounded px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('description') border-red-500 @enderror"
                        >{{ old('description', $awsCredential->description) }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            id="is_active"
                            value="1"
                            {{ old('is_active', $awsCredential->is_active) ? 'checked' : '' }}
                            class="rounded border-neutral-600 text-info-400 focus:ring-primary-500"
                        >
                        <label for="is_active" class="ml-2 text-sm text-neutral-300">
                            Credencial ativa
                        </label>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button 
                        type="submit" 
                        class="flex-1 bg-info-600 hover:bg-info-700 text-white px-4 py-2 rounded font-medium"
                    >
                        Atualizar Credencial
                    </button>
                    <a 
                        href="{{ route('aws-credentials.index') }}" 
                        class="flex-1 text-center bg-neutral-700 hover:bg-neutral-200 text-neutral-300 px-4 py-2 rounded font-medium"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>
