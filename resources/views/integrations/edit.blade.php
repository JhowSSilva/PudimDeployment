<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar: {{ $integration->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('cicd.integrations.update', $integration) }}" method="POST" class="p-6" 
                    x-data="{ provider: '{{ old('provider', $integration->provider) }}' }">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- Nome -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nome da Integração *</label>
                            <input type="text" name="name" id="name" required value="{{ old('name', $integration->name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Provider (não editável, apenas exibição) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Provider</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-md border border-gray-300">
                                <span class="font-medium">{{ ucfirst($integration->provider) }}</span>
                                <span class="text-gray-500 text-sm ml-2">(não pode ser alterado)</span>
                            </div>
                            <input type="hidden" name="provider" value="{{ $integration->provider }}">
                        </div>

                        <!-- GitHub/GitLab/Bitbucket Config -->
                        @if($integration->isGitHub() || $integration->isGitLab() || $integration->isBitbucket())
                            <div class="space-y-4">
                                <div>
                                    <label for="access_token" class="block text-sm font-medium text-gray-700">Access Token *</label>
                                    <input type="password" name="config[access_token]" id="access_token" 
                                        value="{{ old('config.access_token', $integration->config['access_token'] ?? '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="repository_url" class="block text-sm font-medium text-gray-700">Repository URL</label>
                                    <input type="url" name="config[repository_url]" id="repository_url" 
                                        value="{{ old('config.repository_url', $integration->config['repository_url'] ?? '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        @endif

                        <!-- Slack Config -->
                        @if($integration->isSlack())
                            <div class="space-y-4">
                                <div>
                                    <label for="slack_webhook" class="block text-sm font-medium text-gray-700">Webhook URL *</label>
                                    <input type="url" name="config[webhook_url]" id="slack_webhook" 
                                        value="{{ old('config.webhook_url', $integration->config['webhook_url'] ?? '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="slack_channel" class="block text-sm font-medium text-gray-700">Canal</label>
                                    <input type="text" name="config[channel]" id="slack_channel" 
                                        value="{{ old('config.channel', $integration->config['channel'] ?? '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        @endif

                        <!-- Discord Config -->
                        @if($integration->isDiscord())
                            <div>
                                <label for="discord_webhook" class="block text-sm font-medium text-gray-700">Webhook URL *</label>
                                <input type="url" name="config[webhook_url]" id="discord_webhook" 
                                    value="{{ old('config.webhook_url', $integration->config['webhook_url'] ?? '') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        @endif

                        <!-- Telegram Config -->
                        @if($integration->isTelegram())
                            <div class="space-y-4">
                                <div>
                                    <label for="telegram_bot_token" class="block text-sm font-medium text-gray-700">Bot Token *</label>
                                    <input type="password" name="config[bot_token]" id="telegram_bot_token" 
                                        value="{{ old('config.bot_token', $integration->config['bot_token'] ?? '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="telegram_chat_id" class="block text-sm font-medium text-gray-700">Chat ID *</label>
                                    <input type="text" name="config[chat_id]" id="telegram_chat_id" 
                                        value="{{ old('config.chat_id', $integration->config['chat_id'] ?? '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        @endif

                        <!-- Webhook Config -->
                        @if($integration->isWebhook())
                            <div class="space-y-4">
                                <div>
                                    <label for="webhook_url" class="block text-sm font-medium text-gray-700">Webhook URL *</label>
                                    <input type="url" name="webhook_url" id="webhook_url" 
                                        value="{{ old('webhook_url', $integration->webhook_url) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="webhook_secret" class="block text-sm font-medium text-gray-700">Webhook Secret</label>
                                    <input type="password" name="webhook_secret" id="webhook_secret" 
                                        value="{{ old('webhook_secret', $integration->webhook_secret) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        @endif

                        <!-- Eventos -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Eventos para Notificar</label>
                            <div class="space-y-2">
                                @php
                                    $currentEvents = old('events', $integration->events ?? []);
                                @endphp
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="deployment_started" 
                                        {{ in_array('deployment_started', $currentEvents) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Deployment Iniciado</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="deployment_success" 
                                        {{ in_array('deployment_success', $currentEvents) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Deployment Sucesso</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="deployment_failed" 
                                        {{ in_array('deployment_failed', $currentEvents) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Deployment Falhou</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="pipeline_started" 
                                        {{ in_array('pipeline_started', $currentEvents) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Pipeline Iniciado</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="pipeline_success" 
                                        {{ in_array('pipeline_success', $currentEvents) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Pipeline Sucesso</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="pipeline_failed" 
                                        {{ in_array('pipeline_failed', $currentEvents) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Pipeline Falhou</span>
                                </label>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" id="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="active" {{ old('status', $integration->status) === 'active' ? 'selected' : '' }}>Ativo</option>
                                <option value="inactive" {{ old('status', $integration->status) === 'inactive' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-between items-center pt-6 border-t">
                            <form action="{{ route('cicd.integrations.destroy', $integration) }}" method="POST" 
                                onsubmit="return confirm('Tem certeza?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                    Excluir Integração
                                </button>
                            </form>

                            <div class="flex gap-3">
                                <a href="{{ route('cicd.integrations.show', $integration) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Cancelar
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="//unpkg.com/alpinejs" defer></script>
</x-app-layout>
