<x-layout title="Criar Pipeline">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Criar Pipeline') }}
        </h2>
        <a href="{{ route('cicd.pipelines.index') }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
            Voltar
        </a>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <form action="{{ route('cicd.pipelines.store') }}" method="POST" class="p-6" x-data="{ triggerType: 'push' }">
            @csrf

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                @error('name')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Descrição</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Site</label>
                <select name="site_id" required class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <option value="">Selecione um site</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                            {{ $site->name }} ({{ $site->domain }})
                        </option>
                    @endforeach
                </select>
                @error('site_id')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Tipo de Trigger</label>
                <select name="trigger_type" x-model="triggerType" required class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <option value="push">Push</option>
                    <option value="pull_request">Pull Request</option>
                    <option value="schedule">Schedule</option>
                    <option value="manual">Manual</option>
                    <option value="webhook">Webhook</option>
                </select>
            </div>

            <div class="mb-4" x-show="triggerType === 'schedule'">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Cron Expression</label>
                <input type="text" name="trigger_config[cron]" value="{{ old('trigger_config.cron') }}" placeholder="0 0 * * *" class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                <p class="text-neutral-500 text-xs mt-1">Exemplo: 0 0 * * * (diariamente à meia-noite)</p>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="auto_deploy" value="1" {{ old('auto_deploy') ? 'checked' : '' }} class="mr-2 bg-neutral-700 border-neutral-600 text-blue-600 focus:ring-blue-500">
                    <span class="text-neutral-300 text-sm">Auto Deploy</span>
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-neutral-300 text-sm font-bold mb-2">Timeout (segundos)</label>
                <input type="number" name="timeout" value="{{ old('timeout', 3600) }}" min="60" class="w-full px-3 py-2 bg-neutral-700 border border-neutral-600 text-white rounded-md focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Criar Pipeline
                </button>
            </div>
        </form>
    </div>
</x-layout>
