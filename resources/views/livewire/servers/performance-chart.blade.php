<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-neutral-900">Performance Chart</h3>
        <div class="flex items-center space-x-2">
            <select wire:model.live="period" class="text-sm border-neutral-300 rounded-md">
                <option value="24h">Last 24 Hours</option>
                <option value="7d">Last 7 Days</option>
            </select>
        </div>
    </div>

    <div class="h-64">
        @if(count($chartData) > 0)
            <canvas id="performance-chart-{{ $server->id }}"></canvas>
            
            @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('performance-chart-{{ $server->id }}').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: @js($chartData),
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
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
                });
            </script>
            @endpush
        @else
            <div class="flex items-center justify-center h-full text-neutral-500">
                No data available
            </div>
        @endif
    </div>
</div>
