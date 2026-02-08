<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nova Integração
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('cicd.integrations.store') }}" method="POST" class="p-6" 
                    x-data="{ provider: 'github' }">
                    @csrf

                    <div class="space-y-6">
                        <!-- Nome -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nome da Integração *</label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Provider -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Provider *</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <label class="cursor-pointer rounded-lg border bg-white p-3 text-center hover:bg-gray-50" 
                                    :class="provider === 'github' ? 'border-gray-900 ring-2 ring-gray-900' : ''">
                                    <input type="radio" name="provider" value="github" x-model="provider" class="sr-only">
                                    <span class="text-sm font-medium">GitHub</span>
                                </label>

                                <label class="cursor-pointer rounded-lg border bg-white p-3 text-center hover:bg-gray-50" 
                                    :class="provider === 'gitlab' ? 'border-orange-500 ring-2 ring-orange-500' : ''">
                                    <input type="radio" name="provider" value="gitlab" x-model="provider" class="sr-only">
                                    <span class="text-sm font-medium">GitLab</span>
                                </label>

                                <label class="cursor-pointer rounded-lg border bg-white p-3 text-center hover:bg-gray-50" 
                                    :class="provider === 'bitbucket' ? 'border-blue-500 ring-2 ring-blue-500' : ''">
                                    <input type="radio" name="provider" value="bitbucket" x-model="provider" class="sr-only">
                                    <span class="text-sm font-medium">Bitbucket</span>
                                </label>

                                <label class="cursor-pointer rounded-lg border bg-white p-3 text-center hover:bg-gray-50" 
                                    :class="provider === 'slack' ? 'border-purple-500 ring-2 ring-purple-500' : ''">
                                    <input type="radio" name="provider" value="slack" x-model="provider" class="sr-only">
                                    <span class="text-sm font-medium">Slack</span>
                                </label>

                                <label class="cursor-pointer rounded-lg border bg-white p-3 text-center hover:bg-gray-50" 
                                    :class="provider === 'discord' ? 'border-indigo-500 ring-2 ring-indigo-500' : ''">
                                    <input type="radio" name="provider" value="discord" x-model="provider" class="sr-only">
                                    <span class="text-sm font-medium">Discord</span>
                                </label>

                                <label class="cursor-pointer rounded-lg border bg-white p-3 text-center hover:bg-gray-50" 
                                    :class="provider === 'telegram' ? 'border-sky-500 ring-2 ring-sky-500' : ''">
                                    <input type="radio" name="provider" value="telegram" x-model="provider" class="sr-only">
                                    <span class="text-sm font-medium">Telegram</span>
                                </label>

                                <label class="cursor-pointer rounded-lg border bg-white p-3 text-center hover:bg-gray-50" 
                                    :class="provider === 'webhook' ? 'border-gray-500 ring-2 ring-gray-500' : ''">
                                    <input type="radio" name="provider" value="webhook" x-model="provider" class="sr-only">
                                    <span class="text-sm font-medium">Webhook</span>
                                </label>
                            </div>
                        </div>

                        <!-- GitHub/GitLab/Bitbucket Config -->
                        <div x-show="provider === 'github' || provider === 'gitlab' || provider === 'bitbucket'" class="space-y-4">
                            <div>
                                <label for="access_token" class="block text-sm font-medium text-gray-700">Access Token *</label>
                                <input type="password" name="config[access_token]" id="access_token" 
                                    value="{{ old('config.access_token') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Token de acesso pessoal ou de aplicação</p>
                            </div>

                            <div>
                                <label for="repository_url" class="block text-sm font-medium text-gray-700">Repository URL</label>
                                <input type="url" name="config[repository_url]" id="repository_url" 
                                    value="{{ old('config.repository_url') }}"
                                    placeholder="https://github.com/owner/repo"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Slack Config -->
                        <div x-show="provider === 'slack'" class="space-y-4">
                            <div>
                                <label for="slack_webhook" class="block text-sm font-medium text-gray-700">Webhook URL *</label>
                                <input type="url" name="config[webhook_url]" id="slack_webhook" 
                                    value="{{ old('config.webhook_url') }}"
                                    placeholder="https://hooks.slack.com/services/..."
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="slack_channel" class="block text-sm font-medium text-gray-700">Canal</label>
                                <input type="text" name="config[channel]" id="slack_channel" 
                                    value="{{ old('config.channel') }}"
                                    placeholder="#deployments"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Discord Config -->
                        <div x-show="provider === 'discord'">
                            <label for="discord_webhook" class="block text-sm font-medium text-gray-700">Webhook URL *</label>
                            <input type="url" name="config[webhook_url]" id="discord_webhook" 
                                value="{{ old('config.webhook_url') }}"
                                placeholder="https://discord.com/api/webhooks/..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Telegram Config -->
                        <div x-show="provider === 'telegram'" class="space-y-4">
                            <div>
                                <label for="telegram_bot_token" class="block text-sm font-medium text-gray-700">Bot Token *</label>
                                <input type="password" name="config[bot_token]" id="telegram_bot_token" 
                                    value="{{ old('config.bot_token') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="telegram_chat_id" class="block text-sm font-medium text-gray-700">Chat ID *</label>
                                <input type="text" name="config[chat_id]" id="telegram_chat_id" 
                                    value="{{ old('config.chat_id') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Generic Webhook Config -->
                        <div x-show="provider === 'webhook'" class="space-y-4">
                            <div>
                                <label for="webhook_url" class="block text-sm font-medium text-gray-700">Webhook URL *</label>
                                <input type="url" name="webhook_url" id="webhook_url" 
                                    value="{{ old('webhook_url') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="webhook_secret" class="block text-sm font-medium text-gray-700">Webhook Secret (opcional)</label>
                                <input type="password" name="webhook_secret" id="webhook_secret" 
                                    value="{{ old('webhook_secret') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Para assinatura HMAC SHA256</p>
                            </div>
                        </div>

                        <!-- Eventos -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Eventos para Notificar</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="deployment_started" {{ in_array('deployment_started', old('events', [])) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Deployment Iniciado</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="deployment_success" {{ in_array('deployment_success', old('events', [])) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Deployment Sucesso</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="deployment_failed" {{ in_array('deployment_failed', old('events', [])) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Deployment Falhou</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="pipeline_started" {{ in_array('pipeline_started', old('events', [])) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Pipeline Iniciado</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="pipeline_success" {{ in_array('pipeline_success', old('events', [])) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Pipeline Sucesso</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="events[]" value="pipeline_failed" {{ in_array('pipeline_failed', old('events', [])) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Pipeline Falhou</span>
                                </label>
                            </div>
                        </div>

                        <!-- Status Inicial -->
                        <div class="flex items-center">
                            <input type="checkbox" name="status" id="status" value="active" checked
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="status" class="ml-2 block text-sm text-gray-700">
                                Ativar integração imediatamente
                            </label>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end gap-3 pt-6 border-t">
                            <a href="{{ route('cicd.integrations.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Criar Integração
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="//unpkg.com/alpinejs" defer></script>
</x-app-layout>
