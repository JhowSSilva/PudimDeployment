@php
$metrics = $server->metrics()
    ->where('created_at', '>=', now()->subHour())
    ->orderBy('created_at', 'asc')
    ->get();

$labels = [];
$cpuData = [];
$ramData = [];

foreach ($metrics as $metric) {
    $labels[] = $metric->created_at->format('H:i');
    $cpuData[] = round($metric->cpu_usage, 2);
    $ramData[] = round($metric->memory_usage_percentage, 2);
}
@endphp

<div class="bg-white shadow rounded-lg p-6">
    <div class="mb-4">
        <h3 class="text-lg font-medium text-gray-900">
            Métricas - {{ $server->name }}
        </h3>
        <p class="text-sm text-gray-500">Últimos 60 minutos</p>
    </div>

    @if($metrics->count() > 0)
        <div class="space-y-6">
            <!-- CPU Chart -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zm.75-12h9v9h-9v-9z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">CPU</span>
                    </div>
                    <span class="text-sm text-gray-500">{{ $metrics->last()?->cpu_usage ?? 0 }}%</span>
                </div>
                <canvas id="cpuChart{{ $server->id }}" height="100"></canvas>
            </div>

            <!-- RAM Chart -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">RAM</span>
                    </div>
                    <span class="text-sm text-gray-500">{{ $metrics->last()?->memory_percentage ?? 0 }}%</span>
                </div>
                <canvas id="ramChart{{ $server->id }}" height="100"></canvas>
            </div>
        </div>

        @script
        <script>
            // CPU Chart
            const cpuCtx{{ $server->id }} = document.getElementById('cpuChart{{ $server->id }}').getContext('2d');
            const cpuChart{{ $server->id }} = new Chart(cpuCtx{{ $server->id }}, {
                type: 'line',
                data: {
                    labels: @js($labels),
                    datasets: [{
                        label: 'CPU %',
                        data: @js($cpuData),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            // RAM Chart
            const ramCtx{{ $server->id }} = document.getElementById('ramChart{{ $server->id }}').getContext('2d');
            const ramChart{{ $server->id }} = new Chart(ramCtx{{ $server->id }}, {
                type: 'line',
                data: {
                    labels: @js($labels),
                    datasets: [{
                        label: 'RAM %',
                        data: @js($ramData),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            // Auto-refresh a cada 60 segundos
            setInterval(() => {
                window.location.reload();
            }, 60000);
        </script>
        @endscript
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">Sem dados</h3>
            <p class="mt-1 text-sm text-gray-500">Aguarde a coleta de métricas do servidor.</p>
        </div>
    @endif
</div>
