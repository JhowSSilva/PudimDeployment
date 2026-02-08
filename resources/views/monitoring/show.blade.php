<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <a href="{{ route('monitoring.index') }}" class="text-neutral-400 hover:text-neutral-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-neutral-100">{{ $server->name }}</h1>
                </div>
                <p class="text-sm text-neutral-400">{{ $server->ip_address }} • Last checked {{ $server->last_checked_at?->diffForHumans() ?? 'Never' }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <!-- Period Selector -->
                <div class="flex items-center space-x-2 bg-neutral-800 rounded-lg p-1">
                    <button onclick="changePeriod('1h')" class="period-btn {{ $period === '1h' ? 'bg-neutral-700 text-neutral-100' : 'text-neutral-400 hover:text-neutral-200' }} px-3 py-1.5 rounded text-sm font-medium transition">1h</button>
                    <button onclick="changePeriod('24h')" class="period-btn {{ $period === '24h' ? 'bg-neutral-700 text-neutral-100' : 'text-neutral-400 hover:text-neutral-200' }} px-3 py-1.5 rounded text-sm font-medium transition">24h</button>
                    <button onclick="changePeriod('7d')" class="period-btn {{ $period === '7d' ? 'bg-neutral-700 text-neutral-100' : 'text-neutral-400 hover:text-neutral-200' }} px-3 py-1.5 rounded text-sm font-medium transition">7d</button>
                    <button onclick="changePeriod('30d')" class="period-btn {{ $period === '30d' ? 'bg-neutral-700 text-neutral-100' : 'text-neutral-400 hover:text-neutral-200' }} px-3 py-1.5 rounded text-sm font-medium transition">30d</button>
                </div>

                <!-- Actions -->
                <form action="{{ route('monitoring.collect', $server) }}" method="POST" class="inline">
                    @csrf
                    <x-button type="submit" variant="primary" size="sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Collect Now
                    </x-button>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- CPU Card -->
        <x-card>
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center text-sm text-neutral-400 mb-2">
                        <svg class="w-5 h-5 mr-2 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                        CPU Usage
                    </div>
                    <div class="text-3xl font-bold text-neutral-100 mb-1">{{ number_format($summary['cpu']['current'] ?? 0, 1) }}%</div>
                    <div class="flex items-center space-x-4 text-xs text-neutral-400">
                        <span>Avg: {{ number_format($summary['cpu']['average'] ?? 0, 1) }}%</span>
                        <span>Max: {{ number_format($summary['cpu']['maximum'] ?? 0, 1) }}%</span>
                    </div>
                </div>
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-success-500/20 to-success-600/5 flex items-center justify-center">
                    <span class="text-2xl">⚡</span>
                </div>
            </div>
        </x-card>

        <!-- Memory Card -->
        <x-card>
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center text-sm text-neutral-400 mb-2">
                        <svg class="w-5 h-5 mr-2 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                        </svg>
                        Memory Usage
                    </div>
                    <div class="text-3xl font-bold text-neutral-100 mb-1">{{ number_format($summary['memory']['current'] ?? 0, 1) }}%</div>
                    <div class="flex items-center space-x-4 text-xs text-neutral-400">
                        <span>Avg: {{ number_format($summary['memory']['average'] ?? 0, 1) }}%</span>
                        <span>Max: {{ number_format($summary['memory']['maximum'] ?? 0, 1) }}%</span>
                    </div>
                </div>
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-500/20 to-primary-600/5 flex items-center justify-center">
                    <span class="text-2xl">💾</span>
                </div>
            </div>
        </x-card>

        <!-- Disk Card -->
        <x-card>
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center text-sm text-neutral-400 mb-2">
                        <svg class="w-5 h-5 mr-2 text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                        Disk Usage
                    </div>
                    <div class="text-3xl font-bold text-neutral-100 mb-1">{{ number_format($summary['disk']['current'] ?? 0, 1) }}%</div>
                    <div class="flex items-center space-x-4 text-xs text-neutral-400">
                        <span>Avg: {{ number_format($summary['disk']['average'] ?? 0, 1) }}%</span>
                        <span>Max: {{ number_format($summary['disk']['maximum'] ?? 0, 1) }}%</span>
                    </div>
                </div>
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-info-500/20 to-info-600/5 flex items-center justify-center">
                    <span class="text-2xl">💿</span>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Charts -->
    <div class="space-y-6">
        <!-- CPU Chart -->
        <x-card>
            <h3 class="text-lg font-semibold text-neutral-100 mb-4">CPU Usage Over Time</h3>
            <div id="cpu-chart" class="h-64"></div>
        </x-card>

        <!-- Memory Chart -->
        <x-card>
            <h3 class="text-lg font-semibold text-neutral-100 mb-4">Memory Usage Over Time</h3>
            <div id="memory-chart" class="h-64"></div>
        </x-card>

        <!-- Disk Chart -->
        <x-card>
            <h3 class="text-lg font-semibold text-neutral-100 mb-4">Disk Usage Over Time</h3>
            <div id="disk-chart" class="h-64"></div>
        </x-card>
    </div>

    @push('scripts')
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // Chart configuration
        const chartOptions = {
            chart: {
                type: 'area',
                height: 256,
                fontFamily: 'inherit',
                foreColor: '#a3a3a3',
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                },
                background: 'transparent'
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            grid: {
                borderColor: '#404040',
                strokeDashArray: 3,
                xaxis: {
                    lines: {
                        show: false
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    datetimeUTC: false
                }
            },
            yaxis: {
                min: 0,
                max: 100,
                labels: {
                    formatter: function(value) {
                        return value.toFixed(0) + '%';
                    }
                }
            },
            tooltip: {
                theme: 'dark',
                x: {
                    format: 'MMM dd, HH:mm'
                },
                y: {
                    formatter: function(value) {
                        return value.toFixed(1) + '%';
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            legend: {
                show: false
            }
        };

        // CPU Chart
        const cpuChart = new ApexCharts(document.querySelector("#cpu-chart"), {
            ...chartOptions,
            series: [{
                name: 'CPU Usage',
                data: @json($charts['cpu'] ?? [])
            }],
            colors: ['#22c55e']
        });
        cpuChart.render();

        // Memory Chart
        const memoryChart = new ApexCharts(document.querySelector("#memory-chart"), {
            ...chartOptions,
            series: [{
                name: 'Memory Usage',
                data: @json($charts['memory'] ?? [])
            }],
            colors: ['#3b82f6']
        });
        memoryChart.render();

        // Disk Chart
        const diskChart = new ApexCharts(document.querySelector("#disk-chart"), {
            ...chartOptions,
            series: [{
                name: 'Disk Usage',
                data: @json($charts['disk'] ?? [])
            }],
            colors: ['#06b6d4']
        });
        diskChart.render();

        // Period change handler
        function changePeriod(period) {
            const url = new URL(window.location.href);
            url.searchParams.set('period', period);
            window.location.href = url.toString();
        }
    </script>
    @endpush
</x-layout>
