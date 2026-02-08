<x-layout title="Criar Integração">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Criar Integração') }}
        </h2>
        <a href="{{ route('cicd.integrations.index') }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
            Voltar
        </a>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <form action="{{ route('cicd.integrations.store') }}" method="POST" class="p-6" x-data="{ provider: 'slack' }">
            @csrf

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
            </div>

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Descrição</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Provider</label>
                <select name="provider" x-model="provider" required class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <option value="slack">Slack</option>
                    <option value="discord">Discord</option>
                    <option value="github">GitHub</option>
                    <option value="gitlab">GitLab</option>
                    <option value="email">Email</option>
                    <option value="webhook">Webhook</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            <!-- Slack Config -->
            <div class="mb-4 p-4 bg-neutral-700 rounded" x-show="provider === 'slack'">
                <h3 class="text-white font-semibold mb-3">Configuração Slack</h3>
                <div>
                    <label class="block text-neutral-300 text-sm mb-2">Webhook URL</label>
                    <input type="url" name="config[slack][webhook_url]" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
            </div>

            <!-- Discord Config -->
            <div class="mb-4 p-4 bg-neutral-700 rounded" x-show="provider === 'discord'">
                <h3 class="text-white font-semibold mb-3">Configuração Discord</h3>
                <div>
                    <label class="block text-neutral-300 text-sm mb-2">Webhook URL</label>
                    <input type="url" name="config[discord][webhook_url]" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
            </div>

            <!-- GitHub Config -->
            <div class="mb-4 p-4 bg-neutral-700 rounded" x-show="provider === 'github'">
                <h3 class="text-white font-semibold mb-3">Configuração GitHub</h3>
                <div class="mb-3">
                    <label class="block text-neutral-300 text-sm mb-2">Token</label>
                    <input type="password" name="config[github][token]" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
                <div>
                    <label class="block text-neutral-300 text-sm mb-2">Repository</label>
                    <input type="text" name="config[github][repository]" placeholder="owner/repo" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
            </div>

            <!-- Email Config -->
            <div class="mb-4 p-4 bg-neutral-700 rounded" x-show="provider === 'email'">
                <h3 class="text-white font-semibold mb-3">Configuração Email</h3>
                <div>
                    <label class="block text-neutral-300 text-sm mb-2">Emails (separados por vírgula)</label>
                    <input type="text" name="config[email][recipients]" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
            </div>

            <!-- Webhook Config -->
            <div class="mb-4 p-4 bg-neutral-700 rounded" x-show="provider === 'webhook'">
                <h3 class="text-white font-semibold mb-3">Configuração Webhook</h3>
                <div class="mb-3">
                    <label class="block text-neutral-300 text-sm mb-2">URL</label>
                    <input type="url" name="config[webhook][url]" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
                <div>
                    <label class="block text-neutral-300 text-sm mb-2">Secret</label>
                    <input type="password" name="config[webhook][secret]" class="w-full px-3 py-2 bg-neutral-600 border border-neutral-500 text-white rounded-md">
                </div>
            </div>

            <!-- Events -->
            <div class="mb-4 p-4 bg-neutral-700 rounded">
                <h3 class="text-white font-semibold mb-3">Eventos</h3>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="events[]" value="deployment_started" class="mr-2 bg-neutral-600 border-neutral-500 text-blue-600 focus:ring-blue-500">
                        <span class="text-neutral-300 text-sm">Deployment Iniciado</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="events[]" value="deployment_succeeded" checked class="mr-2 bg-neutral-600 border-neutral-500 text-blue-600 focus:ring-blue-500">
                        <span class="text-neutral-300 text-sm">Deployment Sucesso</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="events[]" value="deployment_failed" checked class="mr-2 bg-neutral-600 border-neutral-500 text-blue-600 focus:ring-blue-500">
                        <span class="text-neutral-300 text-sm">Deployment Falhou</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="events[]" value="approval_required" class="mr-2 bg-neutral-600 border-neutral-500 text-blue-600 focus:ring-blue-500">
                        <span class="text-neutral-300 text-sm">Aprovação Necessária</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="events[]" value="approval_granted" class="mr-2 bg-neutral-600 border-neutral-500 text-blue-600 focus:ring-blue-500">
                        <span class="text-neutral-300 text-sm">Aprovação Concedida</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="events[]" value="rollback_initiated" class="mr-2 bg-neutral-600 border-neutral-500 text-blue-600 focus:ring-blue-500">
                        <span class="text-neutral-300 text-sm">Rollback Iniciado</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Criar Integração
                </button>
            </div>
        </form>
    </div>
</x-layout>
