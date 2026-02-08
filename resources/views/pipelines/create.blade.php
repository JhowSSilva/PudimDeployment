<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Criar Pipeline CI/CD') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('cicd.pipelines.store') }}" method="POST" class="p-6">
                    @csrf

                    <div class="space-y-6">
                        <!-- Nome -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nome do Pipeline *</label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descrição -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                        </div>

                        <!-- Site -->
                        <div>
                            <label for="site_id" class="block text-sm font-medium text-gray-700">Site (opcional)</label>
                            <select name="site_id" id="site_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Nenhum site específico</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }} ({{ $site->domain }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tipo de Trigger -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Trigger *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                                    <input type="radio" name="trigger_type" value="manual" {{ old('trigger_type', 'manual') === 'manual' ? 'checked' : '' }} class="sr-only">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900">Manual</span>
                                            <span class="mt-1 flex items-center text-sm text-gray-500">Executado manualmente</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                                    <input type="radio" name="trigger_type" value="push" {{ old('trigger_type') === 'push' ? 'checked' : '' }} class="sr-only">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900">Git Push</span>
                                            <span class="mt-1 flex items-center text-sm text-gray-500">Ao fazer push</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                                    <input type="radio" name="trigger_type" value="pull_request" {{ old('trigger_type') === 'pull_request' ? 'checked' : '' }} class="sr-only">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900">Pull Request</span>
                                            <span class="mt-1 flex items-center text-sm text-gray-500">Em PRs</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                                    <input type="radio" name="trigger_type" value="schedule" {{ old('trigger_type') === 'schedule' ? 'checked' : '' }} class="sr-only">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900">Agendado</span>
                                            <span class="mt-1 flex items-center text-sm text-gray-500">Cron schedule</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                                    <input type="radio" name="trigger_type" value="webhook" {{ old('trigger_type') === 'webhook' ? 'checked' : '' }} class="sr-only">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900">Webhook</span>
                                            <span class="mt-1 flex items-center text-sm text-gray-500">Via webhook</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Configurações -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="timeout_minutes" class="block text-sm font-medium text-gray-700">Timeout (minutos)</label>
                                <input type="number" name="timeout_minutes" id="timeout_minutes" min="1" max="180" value="{{ old('timeout_minutes', 30) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="retention_days" class="block text-sm font-medium text-gray-700">Retenção (dias)</label>
                                <input type="number" name="retention_days" id="retention_days" min="1" max="365" value="{{ old('retention_days', 30) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Auto Deploy -->
                        <div class="flex items-center">
                            <input type="checkbox" name="auto_deploy" id="auto_deploy" value="1" {{ old('auto_deploy') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="auto_deploy" class="ml-2 block text-sm text-gray-700">
                                Fazer deploy automaticamente após sucesso
                            </label>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end gap-3 pt-6 border-t">
                            <a href="{{ route('cicd.pipelines.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Criar Pipeline
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Próximo passo</h3>
                        <p class="mt-1 text-sm text-blue-700">Após criar o pipeline, você poderá adicionar stages (build, test, deploy) na página de detalhes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
