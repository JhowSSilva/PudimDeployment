@php
$latestMetric = $server->latestMetric();
@endphp

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                @if($server->status === 'online')
                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @else
                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>
            <div>
                <h3 class="text-lg font-semibold text-neutral-900">{{ $server->name }}</h3>
                <p class="text-sm text-neutral-500">{{ $server->ip_address }}{{ $server->os_type ? ' • ' . $server->os_type . ' ' . $server->os_version : '' }}</p>
            </div>
        </div>
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
            @if($server->status === 'online') bg-green-100 text-green-800
            @elseif($server->status === 'offline') bg-red-100 text-red-800
            @else bg-neutral-100 text-neutral-800
            @endif">
            {{ ucfirst($server->status) }}
        </span>
    </div>

    @if($latestMetric)
        <div class="grid grid-cols-3 gap-4">
            <!-- CPU -->
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-neutral-500">CPU</span>
                    <span class="text-xs font-semibold 
                        @if($latestMetric->cpu_usage > 80) text-red-600
                        @elseif($latestMetric->cpu_usage > 60) text-yellow-600
                        @else text-green-600
                        @endif">
                        {{ number_format($latestMetric->cpu_usage, 1) }}%
                    </span>
                </div>
                <div class="mt-2 w-full bg-neutral-200 rounded-full h-2">
                    <div class="h-2 rounded-full 
                        @if($latestMetric->cpu_usage > 80) bg-red-600
                        @elseif($latestMetric->cpu_usage > 60) bg-yellow-600
                        @else bg-green-600
                        @endif"
                        style="width: {{ min($latestMetric->cpu_usage, 100) }}%"></div>
                </div>
            </div>

            <!-- RAM -->
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-neutral-500">RAM</span>
                    <span class="text-xs font-semibold 
                        @if($latestMetric->memory_usage_percentage > 80) text-red-600
                        @elseif($latestMetric->memory_usage_percentage > 60) text-yellow-600
                        @else text-green-600
                        @endif">
                        {{ number_format($latestMetric->memory_usage_percentage, 1) }}%
                    </span>
                </div>
                <div class="mt-2 w-full bg-neutral-200 rounded-full h-2">
                    <div class="h-2 rounded-full 
                        @if($latestMetric->memory_usage_percentage > 80) bg-red-600
                        @elseif($latestMetric->memory_usage_percentage > 60) bg-yellow-600
                        @else bg-green-600
                        @endif"
                        style="width: {{ min($latestMetric->memory_usage_percentage, 100) }}%"></div>
                </div>
            </div>

            <!-- Disk -->
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-neutral-500">Disco</span>
                    <span class="text-xs font-semibold 
                        @if($latestMetric->disk_usage_percentage > 80) text-red-600
                        @elseif($latestMetric->disk_usage_percentage > 60) text-yellow-600
                        @else text-green-600
                        @endif">
                        {{ number_format($latestMetric->disk_usage_percentage, 1) }}%
                    </span>
                </div>
                <div class="mt-2 w-full bg-neutral-200 rounded-full h-2">
                    <div class="h-2 rounded-full 
                        @if($latestMetric->disk_usage_percentage > 80) bg-red-600
                        @elseif($latestMetric->disk_usage_percentage > 60) bg-yellow-600
                        @else bg-green-600
                        @endif"
                        style="width: {{ min($latestMetric->disk_usage_percentage, 100) }}%"></div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between text-xs text-neutral-500">
            <span>Uptime: {{ $latestMetric->uptime_human }}</span>
            <span>Última atualização: {{ $latestMetric->created_at->diffForHumans() }}</span>
        </div>
    @else
        <div class="text-center py-4">
            <p class="text-sm text-neutral-500">Nenhuma métrica disponível</p>
        </div>
    @endif
</div>
