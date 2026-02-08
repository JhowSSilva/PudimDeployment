<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-2">
            <a href="{{ route('alerts.rules') }}" class="text-neutral-400 hover:text-neutral-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-neutral-100">Create Alert Rule</h1>
        </div>
        <p class="text-sm text-neutral-400 mt-1">Define custom monitoring rules with automatic notifications</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
        <div class="lg:col-span-2">
            <x-card>
                <form action="{{ route('alerts.rules.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Rule Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">
                            Rule Name <span class="text-error-400">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            value="{{ old('name') }}"
                            class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                            placeholder="e.g., High CPU Usage Alert"
                            required>
                        @error('name')
                            <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-neutral-300 mb-2">
                            Description
                        </label>
                        <textarea 
                            name="description" 
                            id="description" 
                            rows="3" 
                            class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                            placeholder="Describe when this alert should trigger...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Metric Type -->
                        <div>
                            <label for="metric_type" class="block text-sm font-medium text-neutral-300 mb-2">
                                Metric Type <span class="text-error-400">*</span>
                            </label>
                            <select 
                                name="metric_type" 
                                id="metric_type" 
                                class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                required>
                                <option value="">Select metric...</option>
                                <option value="cpu" {{ old('metric_type') === 'cpu' ? 'selected' : '' }}>CPU Usage</option>
                                <option value="memory" {{ old('metric_type') === 'memory' ? 'selected' : '' }}>Memory Usage</option>
                                <option value="disk" {{ old('metric_type') === 'disk' ? 'selected' : '' }}>Disk Usage</option>
                                <option value="network_in" {{ old('metric_type') === 'network_in' ? 'selected' : '' }}>Network In</option>
                                <option value="network_out" {{ old('metric_type') === 'network_out' ? 'selected' : '' }}>Network Out</option>
                                <option value="response_time" {{ old('metric_type') === 'response_time' ? 'selected' : '' }}>Response Time</option>
                                <option value="requests_per_minute" {{ old('metric_type') === 'requests_per_minute' ? 'selected' : '' }}>Requests Per Minute</option>
                                <option value="error_rate" {{ old('metric_type') === 'error_rate' ? 'selected' : '' }}>Error Rate</option>
                            </select>
                            @error('metric_type')
                                <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Condition -->
                        <div>
                            <label for="condition" class="block text-sm font-medium text-neutral-300 mb-2">
                                Condition <span class="text-error-400">*</span>
                            </label>
                            <select 
                                name="condition" 
                                id="condition" 
                                class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                required>
                                <option value="">Select condition...</option>
                                <option value="greater_than" {{ old('condition') === 'greater_than' ? 'selected' : '' }}>Greater Than</option>
                                <option value="less_than" {{ old('condition') === 'less_than' ? 'selected' : '' }}>Less Than</option>
                                <option value="equals" {{ old('condition') === 'equals' ? 'selected' : '' }}>Equals</option>
                                <option value="not_equals" {{ old('condition') === 'not_equals' ? 'selected' : '' }}>Not Equals</option>
                            </select>
                            @error('condition')
                                <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Threshold -->
                        <div>
                            <label for="threshold" class="block text-sm font-medium text-neutral-300 mb-2">
                                Threshold Value <span class="text-error-400">*</span>
                            </label>
                            <input 
                                type="number" 
                                step="0.01"
                                name="threshold" 
                                id="threshold" 
                                value="{{ old('threshold') }}"
                                class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                                placeholder="e.g., 90"
                                required>
                            @error('threshold')
                                <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Duration -->
                        <div>
                            <label for="duration" class="block text-sm font-medium text-neutral-300 mb-2">
                                Duration (minutes)
                            </label>
                            <input 
                                type="number" 
                                name="duration" 
                                id="duration" 
                                value="{{ old('duration', 5) }}"
                                class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                                placeholder="e.g., 5">
                            <p class="mt-1 text-xs text-neutral-400">How long the condition must be true before triggering</p>
                            @error('duration')
                                <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Severity -->
                        <div>
                            <label for="severity" class="block text-sm font-medium text-neutral-300 mb-2">
                                Severity <span class="text-error-400">*</span>
                            </label>
                            <select 
                                name="severity" 
                                id="severity" 
                                class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                required>
                                <option value="">Select severity...</option>
                                <option value="info" {{ old('severity') === 'info' ? 'selected' : '' }}>Info</option>
                                <option value="warning" {{ old('severity', 'warning') === 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="critical" {{ old('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('severity')
                                <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cooldown -->
                        <div>
                            <label for="cooldown" class="block text-sm font-medium text-neutral-300 mb-2">
                                Cooldown (seconds)
                            </label>
                            <input 
                                type="number" 
                                name="cooldown" 
                                id="cooldown" 
                                value="{{ old('cooldown', 300) }}"
                                class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                                placeholder="e.g., 300">
                            <p class="mt-1 text-xs text-neutral-400">Minimum time between consecutive alerts</p>
                            @error('cooldown')
                                <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Server Scope -->
                    <div>
                        <label for="server_id" class="block text-sm font-medium text-neutral-300 mb-2">
                            Server (Optional)
                        </label>
                        <select 
                            name="server_id" 
                            id="server_id" 
                            class="w-full bg-neutral-800 border border-neutral-700 text-neutral-100 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">All Servers</option>
                            @foreach($servers as $server)
                                <option value="{{ $server->id }}" {{ old('server_id') == $server->id ? 'selected' : '' }}>
                                    {{ $server->name }} ({{ $server->ip_address }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-neutral-400">Leave empty to apply to all servers</p>
                        @error('server_id')
                            <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notification Channels -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-3">
                            Notification Channels
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 bg-neutral-800 rounded-lg cursor-pointer hover:bg-neutral-700 transition">
                                <input 
                                    type="checkbox" 
                                    name="channels[]" 
                                    value="email" 
                                    {{ is_array(old('channels')) && in_array('email', old('channels')) ? 'checked' : 'checked' }}
                                    class="w-4 h-4 text-primary-600 bg-neutral-700 border-neutral-600 rounded focus:ring-primary-500">
                                <span class="ml-3 text-sm text-neutral-200">Email</span>
                            </label>
                            <label class="flex items-center p-3 bg-neutral-800 rounded-lg cursor-pointer hover:bg-neutral-700 transition">
                                <input 
                                    type="checkbox" 
                                    name="channels[]" 
                                    value="slack" 
                                    {{ is_array(old('channels')) && in_array('slack', old('channels')) ? 'checked' : '' }}
                                    class="w-4 h-4 text-primary-600 bg-neutral-700 border-neutral-600 rounded focus:ring-primary-500">
                                <span class="ml-3 text-sm text-neutral-200">Slack</span>
                            </label>
                            <label class="flex items-center p-3 bg-neutral-800 rounded-lg cursor-pointer hover:bg-neutral-700 transition">
                                <input 
                                    type="checkbox" 
                                    name="channels[]" 
                                    value="discord" 
                                    {{ is_array(old('channels')) && in_array('discord', old('channels')) ? 'checked' : '' }}
                                    class="w-4 h-4 text-primary-600 bg-neutral-700 border-neutral-600 rounded focus:ring-primary-500">
                                <span class="ml-3 text-sm text-neutral-200">Discord</span>
                            </label>
                            <label class="flex items-center p-3 bg-neutral-800 rounded-lg cursor-pointer hover:bg-neutral-700 transition">
                                <input 
                                    type="checkbox" 
                                    name="channels[]" 
                                    value="webhook" 
                                    {{ is_array(old('channels')) && in_array('webhook', old('channels')) ? 'checked' : '' }}
                                    class="w-4 h-4 text-primary-600 bg-neutral-700 border-neutral-600 rounded focus:ring-primary-500">
                                <span class="ml-3 text-sm text-neutral-200">Custom Webhook</span>
                            </label>
                        </div>
                        @error('channels')
                            <p class="mt-1 text-sm text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-neutral-700">
                        <x-button href="{{ route('alerts.rules') }}" variant="ghost">
                            Cancel
                        </x-button>
                        <x-button type="submit" variant="primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Create Alert Rule
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>

        <!-- Sidebar Help -->
        <div class="space-y-6">
            <x-card>
                <h3 class="text-lg font-semibold text-neutral-100 mb-4">💡 Tips</h3>
                <div class="space-y-4 text-sm text-neutral-400">
                    <div>
                        <p class="font-medium text-neutral-300 mb-1">Metric Types</p>
                        <p>Choose the metric you want to monitor (CPU, Memory, Disk, etc.)</p>
                    </div>
                    <div>
                        <p class="font-medium text-neutral-300 mb-1">Conditions</p>
                        <p>Define when the alert should trigger based on the threshold value</p>
                    </div>
                    <div>
                        <p class="font-medium text-neutral-300 mb-1">Duration</p>
                        <p>Prevent false positives by requiring the condition to persist for a specified time</p>
                    </div>
                    <div>
                        <p class="font-medium text-neutral-300 mb-1">Cooldown</p>
                        <p>Avoid alert spam by setting a minimum time between notifications</p>
                    </div>
                </div>
            </x-card>

            <x-card>
                <h3 class="text-lg font-semibold text-neutral-100 mb-4">📋 Examples</h3>
                <div class="space-y-3 text-sm">
                    <div class="p-3 bg-neutral-800 rounded-lg">
                        <p class="font-medium text-neutral-200 mb-1">High CPU Usage</p>
                        <p class="text-xs text-neutral-400">CPU > 90% for 5 minutes</p>
                    </div>
                    <div class="p-3 bg-neutral-800 rounded-lg">
                        <p class="font-medium text-neutral-200 mb-1">Memory Pressure</p>
                        <p class="text-xs text-neutral-400">Memory > 85% for 10 minutes</p>
                    </div>
                    <div class="p-3 bg-neutral-800 rounded-lg">
                        <p class="font-medium text-neutral-200 mb-1">Disk Space</p>
                        <p class="text-xs text-neutral-400">Disk > 80% for 1 hour</p>
                    </div>
                    <div class="p-3 bg-neutral-800 rounded-lg">
                        <p class="font-medium text-neutral-200 mb-1">Slow Response</p>
                        <p class="text-xs text-neutral-400">Response Time > 2000ms for 3 minutes</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-layout>
