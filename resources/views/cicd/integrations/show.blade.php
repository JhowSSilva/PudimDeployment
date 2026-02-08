<x-layout title="Integração: {{ $integration->name }}">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            Integração: {{ $integration->name }}
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('cicd.integrations.edit', $integration) }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
                Editar
            </a>
            <a href="{{ route('cicd.integrations.index') }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-900/50 border border-green-500 text-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-neutral-800 p-4 rounded-lg border border-neutral-700">
            <div class="text-neutral-400 text-sm">Status</div>
            <div class="text-lg font-semibold {{ $integration->status === 'active' ? 'text-green-400' : 'text-neutral-400' }}">
                {{ ucfirst($integration->status) }}
            </div>
        </div>
        <div class="bg-neutral-800 p-4 rounded-lg border border-neutral-700">
            <div class="text-neutral-400 text-sm">Provider</div>
            <div class="text-white font-semibold">{{ ucfirst($integration->provider) }}</div>
        </div>
        <div class="bg-neutral-800 p-4 rounded-lg border border-neutral-700">
            <div class="text-neutral-400 text-sm">Eventos Ativos</div>
            <div class="text-white font-semibold">{{ count($integration->events ?? []) }}</div>
        </div>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
        <div class="p-6 text-neutral-300">
            <h3 class="text-lg font-semibold text-white mb-4">Informações</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-neutral-400">Nome:</span>
                    <span class="text-white ml-2">{{ $integration->name }}</span>
                </div>
                @if($integration->description)
                    <div>
                        <span class="text-neutral-400">Descrição:</span>
                        <p class="text-white mt-1">{{ $integration->description }}</p>
                    </div>
                @endif
                <div>
                    <span class="text-neutral-400">Criado em:</span>
                    <span class="text-white ml-2">{{ $integration->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
        <div class="p-6 text-neutral-300">
            <h3 class="text-lg font-semibold text-white mb-4">Configuração</h3>
            
            @if($integration->provider === 'slack' && isset($integration->config['slack']))
                <div>
                    <span class="text-neutral-400">Webhook URL:</span>
                    <span class="text-white ml-2 font-mono text-sm">{{ str_repeat('*', 40) }}{{ substr($integration->config['slack']['webhook_url'], -10) }}</span>
                </div>
            @elseif($integration->provider === 'discord' && isset($integration->config['discord']))
                <div>
                    <span class="text-neutral-400">Webhook URL:</span>
                    <span class="text-white ml-2 font-mono text-sm">{{ str_repeat('*', 40) }}{{ substr($integration->config['discord']['webhook_url'], -10) }}</span>
                </div>
            @elseif($integration->provider === 'github' && isset($integration->config['github']))
                <div class="space-y-2">
                    <div>
                        <span class="text-neutral-400">Repository:</span>
                        <span class="text-white ml-2">{{ $integration->config['github']['repository'] }}</span>
                    </div>
                    <div>
                        <span class="text-neutral-400">Token:</span>
                        <span class="text-white ml-2 font-mono text-sm">{{ str_repeat('*', 30) }}</span>
                    </div>
                </div>
            @elseif($integration->provider === 'email' && isset($integration->config['email']))
                <div>
                    <span class="text-neutral-400">Destinatários:</span>
                    <span class="text-white ml-2">{{ $integration->config['email']['recipients'] }}</span>
                </div>
            @elseif($integration->provider === 'webhook' && isset($integration->config['webhook']))
                <div class="space-y-2">
                    <div>
                        <span class="text-neutral-400">URL:</span>
                        <span class="text-white ml-2 break-all">{{ $integration->config['webhook']['url'] }}</span>
                    </div>
                    @if(isset($integration->config['webhook']['secret']))
                        <div>
                            <span class="text-neutral-400">Secret:</span>
                            <span class="text-white ml-2 font-mono text-sm">{{ str_repeat('*', 20) }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if($integration->events)
        <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 text-neutral-300">
                <h3 class="text-lg font-semibold text-white mb-4">Eventos Configurados</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($integration->events as $event)
                        <span class="px-3 py-1 bg-blue-900 text-blue-300 rounded text-sm">
                            {{ ucfirst(str_replace('_', ' ', $event)) }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-layout>
