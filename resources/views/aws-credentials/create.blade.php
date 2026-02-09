<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('aws-credentials.index') }}" class="text-blue-600 hover:text-blue-700 mb-2 inline-block">
                    ← Voltar
                </a>
                <h1 class="text-3xl font-bold">Nova Credencial AWS</h1>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-900 mb-2">Como obter suas credenciais AWS:</h3>
                <ol class="list-decimal list-inside space-y-1 text-sm text-blue-800">
                    <li>Acesse o <a href="https://console.aws.amazon.com/iam/" target="_blank" class="underline">AWS IAM Console</a></li>
                    <li>Vá em "Users" → "Add user"</li>
                    <li>Crie um usuário com acesso programático (Access Key)</li>
                    <li>Anexe a policy "AmazonEC2FullAccess" e "CloudWatchReadOnlyAccess"</li>
                    <li>Copie o Access Key ID e Secret Access Key</li>
                </ol>
            </div>

            <form action="{{ route('aws-credentials.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 mb-1">
                            Nome da Credencial *
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name"
                            value="{{ old('name') }}"
                            class="w-full border border-neutral-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                            placeholder="Produção AWS"
                            required
                        >
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="access_key_id" class="block text-sm font-medium text-neutral-700 mb-1">
                            AWS Access Key ID *
                        </label>
                        <input 
                            type="text" 
                            name="access_key_id" 
                            id="access_key_id"
                            value="{{ old('access_key_id') }}"
                            class="w-full border border-neutral-300 rounded px-3 py-2 font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('access_key_id') border-red-500 @enderror"
                            placeholder="AKIAIOSFODNN7EXAMPLE"
                            required
                        >
                        @error('access_key_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="secret_access_key" class="block text-sm font-medium text-neutral-700 mb-1">
                            AWS Secret Access Key *
                        </label>
                        <input 
                            type="password" 
                            name="secret_access_key" 
                            id="secret_access_key"
                            value="{{ old('secret_access_key') }}"
                            class="w-full border border-neutral-300 rounded px-3 py-2 font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('secret_access_key') border-red-500 @enderror"
                            placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
                            required
                        >
                        @error('secret_access_key')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="default_region" class="block text-sm font-medium text-neutral-700 mb-1">
                            Região Padrão *
                        </label>
                        <select 
                            name="default_region" 
                            id="default_region"
                            class="w-full border border-neutral-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('default_region') border-red-500 @enderror"
                            required
                        >
                            <option value="">Selecione uma região</option>
                            @foreach($regions as $code => $name)
                                <option value="{{ $code }}" {{ old('default_region') === $code ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('default_region')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-neutral-700 mb-1">
                            Descrição (opcional)
                        </label>
                        <textarea 
                            name="description" 
                            id="description"
                            rows="3"
                            class="w-full border border-neutral-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                            placeholder="Conta AWS para ambiente de produção"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button 
                        type="submit" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium"
                    >
                        Salvar Credencial
                    </button>
                    <a 
                        href="{{ route('aws-credentials.index') }}" 
                        class="flex-1 text-center bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2 rounded font-medium"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>
