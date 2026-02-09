<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-neutral-900">Cost Forecast</h3>
        <button wire:click="refresh" class="text-blue-600 hover:text-blue-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </button>
    </div>

    @if(isset($forecast['error']))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ $forecast['error'] }}
        </div>
    @else
        <!-- Current vs Forecasted -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-blue-900">Current Month</span>
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-blue-900">
                    ${{ number_format($currentMonthCost, 2) }}
                </div>
                <div class="text-xs text-blue-700 mt-1">
                    As of {{ now()->format('M d, Y') }}
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-purple-900">Next Month (Forecast)</span>
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-purple-900">
                    ${{ number_format($forecastedCost, 2) }}
                </div>
                <div class="text-xs text-purple-700 mt-1">
                    @php
                        $diff = $forecastedCost - $currentMonthCost;
                        $percentChange = $currentMonthCost > 0 ? ($diff / $currentMonthCost) * 100 : 0;
                    @endphp
                    @if($diff > 0)
                        <span class="text-red-600">↑ +{{ number_format($percentChange, 1) }}%</span>
                    @elseif($diff < 0)
                        <span class="text-green-600">↓ {{ number_format($percentChange, 1) }}%</span>
                    @else
                        <span>No change expected</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Server Breakdown -->
        @if(isset($forecast['breakdown']) && count($forecast['breakdown']) > 0)
            <div class="border-t border-neutral-200 pt-4">
                <h4 class="text-sm font-semibold text-neutral-700 mb-3">Cost Breakdown by Server</h4>
                <div class="space-y-2">
                    @foreach($forecast['breakdown'] as $serverCost)
                        <div class="flex items-center justify-between p-3 bg-neutral-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-neutral-900">{{ $serverCost['server_name'] }}</div>
                                    <div class="text-xs text-neutral-500">{{ $serverCost['provider'] ?? 'Custom' }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-neutral-900">
                                    ${{ number_format($serverCost['cost'], 2) }}
                                </div>
                                <div class="text-xs text-neutral-500">
                                    per month
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Trend Indicator -->
        @if(isset($forecast['trend']))
            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-blue-900">
                        <strong>Trend:</strong> {{ $forecast['trend'] }}
                    </p>
                </div>
            </div>
        @endif
    @endif
</div>
