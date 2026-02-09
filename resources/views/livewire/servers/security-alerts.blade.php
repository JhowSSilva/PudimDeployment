<div class="bg-neutral-800 rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-neutral-100">Security Alerts</h3>
        <button wire:click="refresh" class="text-info-400 hover:text-info-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </button>
    </div>

    <!-- Security Threats -->
    <div class="mb-6">
        <h4 class="text-sm font-semibold text-neutral-300 mb-3">Recent Threats (Last 24h)</h4>
        @if(count($threats) > 0)
            <div class="space-y-2">
                @foreach($threats as $threat)
                    <div class="flex items-start space-x-3 p-3 bg-error-900/20 border border-red-200 rounded-lg">
                        <svg class="w-5 h-5 text-error-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-red-900">{{ $threat->threat_type }}</span>
                                <span class="text-xs text-error-400">{{ \Carbon\Carbon::parse($threat->detected_at)->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-error-400 mt-1">{{ $threat->description }}</p>
                            @if($threat->severity)
                                <span class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded 
                                    {{ $threat->severity === 'critical' ? 'bg-error-700 text-white' : '' }}
                                    {{ $threat->severity === 'high' ? 'bg-orange-600 text-white' : '' }}
                                    {{ $threat->severity === 'medium' ? 'bg-warning-500 text-white' : '' }}
                                    {{ $threat->severity === 'low' ? 'bg-info-600 text-white' : '' }}">
                                    {{ ucfirst($threat->severity) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-neutral-500 bg-neutral-900 rounded-lg p-4 text-center">
                ✓ No threats detected in the last 24 hours
            </p>
        @endif
    </div>

    <!-- Blocked IPs -->
    <div>
        <h4 class="text-sm font-semibold text-neutral-300 mb-3">Blocked IPs</h4>
        @if(count($blockedIps) > 0)
            <div class="space-y-2">
                @foreach($blockedIps as $ip)
                    <div class="flex items-center justify-between p-3 bg-neutral-900 border border-neutral-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                            <div>
                                <div class="text-sm font-medium text-neutral-100">{{ $ip->ip_address }}</div>
                                <div class="text-xs text-neutral-500">
                                    Blocked {{ \Carbon\Carbon::parse($ip->blocked_at)->diffForHumans() }}
                                    @if($ip->reason)
                                        · {{ $ip->reason }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button 
                            wire:click="unblockIp({{ $ip->id }})"
                            class="px-3 py-1 text-xs font-medium text-info-400 hover:text-info-400 hover:bg-info-900/20 rounded">
                            Unblock
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-neutral-500 bg-neutral-900 rounded-lg p-4 text-center">
                No IPs are currently blocked
            </p>
        @endif
    </div>
</div>
