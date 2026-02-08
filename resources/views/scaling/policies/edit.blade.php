<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Scaling Policy') }}: {{ $policy->name }}
            </h2>
            <a href="{{ route('scaling.policies.show', $policy) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('scaling.policies.update', $policy) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Policy Name *</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $policy->name) }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $policy->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="server_pool_id" class="block text-sm font-medium text-gray-700">Server Pool</label>
                                    <select name="server_pool_id" id="server_pool_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select a server pool (optional)</option>
                                        @foreach($serverPools as $pool)
                                            <option value="{{ $pool->id }}" {{ old('server_pool_id', $policy->server_pool_id) == $pool->id ? 'selected' : '' }}>
                                                {{ $pool->name }} ({{ $pool->current_servers }}/{{ $pool->max_servers }} servers)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('server_pool_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Policy Type -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Policy Type</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @php
                                    $types = [
                                        'cpu' => ['name' => 'CPU-based', 'desc' => 'Scale based on CPU utilization', 'icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z'],
                                        'memory' => ['name' => 'Memory-based', 'desc' => 'Scale based on memory usage', 'icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01'],
                                        'schedule' => ['name' => 'Schedule-based', 'desc' => 'Scale at specific times', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                        'custom' => ['name' => 'Custom Metric', 'desc' => 'Scale based on custom metrics', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4']
                                    ];
                                @endphp

                                @foreach($types as $value => $type)
                                <label class="relative flex cursor-pointer rounded-lg border {{ old('type', $policy->type) == $value ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-white' }} p-4 shadow-sm focus:outline-none hover:border-blue-500">
                                    <input type="radio" name="type" value="{{ $value }}" {{ old('type', $policy->type) == $value ? 'checked' : '' }} 
                                        class="sr-only" onchange="updateMetricFields('{{ $value }}')">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span class="flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $type['icon'] }}"/>
                                                </svg>
                                                <span class="block text-sm font-medium text-gray-900">{{ $type['name'] }}</span>
                                            </span>
                                            <span class="mt-1 flex items-center text-sm text-gray-500">{{ $type['desc'] }}</span>
                                        </span>
                                    </span>
                                </label>
                                @endforeach
                            </div>
                            @error('type')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Metric Configuration -->
                        <div id="metric_fields" class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Metric Configuration</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div id="metric_field" class="{{ $policy->type === 'schedule' ? 'hidden' : '' }}">
                                    <label for="metric" class="block text-sm font-medium text-gray-700">Metric Name</label>
                                    <input type="text" name="metric" id="metric" value="{{ old('metric', $policy->metric) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('metric')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div></div>

                                <div id="threshold_up_field" class="{{ $policy->type === 'schedule' ? 'hidden' : '' }}">
                                    <label for="threshold_up" class="block text-sm font-medium text-gray-700">Scale Up Threshold (%) *</label>
                                    <input type="number" name="threshold_up" id="threshold_up" value="{{ old('threshold_up', $policy->threshold_up) }}" min="0" max="100" step="0.1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('threshold_up')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div id="threshold_down_field" class="{{ $policy->type === 'schedule' ? 'hidden' : '' }}">
                                    <label for="threshold_down" class="block text-sm font-medium text-gray-700">Scale Down Threshold (%) *</label>
                                    <input type="number" name="threshold_down" id="threshold_down" value="{{ old('threshold_down', $policy->threshold_down) }}" min="0" max="100" step="0.1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('threshold_down')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="evaluation_periods" class="block text-sm font-medium text-gray-700">Evaluation Periods *</label>
                                    <input type="number" name="evaluation_periods" id="evaluation_periods" value="{{ old('evaluation_periods', $policy->evaluation_periods) }}" min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('evaluation_periods')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="period_duration" class="block text-sm font-medium text-gray-700">Period Duration (seconds) *</label>
                                    <input type="number" name="period_duration" id="period_duration" value="{{ old('period_duration', $policy->period_duration) }}" min="10"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('period_duration')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Schedule Field -->
                            <div id="schedule_field" class="{{ $policy->type === 'schedule' ? '' : 'hidden' }} mt-6">
                                <label for="schedule" class="block text-sm font-medium text-gray-700">Schedule (JSON)</label>
                                <textarea name="schedule" id="schedule" rows="4"
                                    placeholder='[{"time": "09:00", "servers": 5}, {"time": "17:00", "servers": 2}]'
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">{{ old('schedule', is_array($policy->schedule) ? json_encode($policy->schedule) : $policy->schedule) }}</textarea>
                                @error('schedule')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Scaling Configuration -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Scaling Configuration</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="scale_up_by" class="block text-sm font-medium text-gray-700">Scale Up By *</label>
                                    <input type="number" name="scale_up_by" id="scale_up_by" value="{{ old('scale_up_by', $policy->scale_up_by) }}" min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('scale_up_by')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="scale_down_by" class="block text-sm font-medium text-gray-700">Scale Down By *</label>
                                    <input type="number" name="scale_down_by" id="scale_down_by" value="{{ old('scale_down_by', $policy->scale_down_by) }}" min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('scale_down_by')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="min_servers" class="block text-sm font-medium text-gray-700">Minimum Servers *</label>
                                    <input type="number" name="min_servers" id="min_servers" value="{{ old('min_servers', $policy->min_servers) }}" min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('min_servers')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="max_servers" class="block text-sm font-medium text-gray-700">Maximum Servers *</label>
                                    <input type="number" name="max_servers" id="max_servers" value="{{ old('max_servers', $policy->max_servers) }}" min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('max_servers')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="cooldown_minutes" class="block text-sm font-medium text-gray-700">Cooldown Period (minutes) *</label>
                                    <input type="number" name="cooldown_minutes" id="cooldown_minutes" value="{{ old('cooldown_minutes', $policy->cooldown_minutes) }}" min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('cooldown_minutes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Policy Status -->
                        <div class="pb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $policy->is_active) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Policy is active</span>
                            </label>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <form method="POST" action="{{ route('scaling.policies.destroy', $policy) }}" onsubmit="return confirm('Are you sure you want to delete this scaling policy?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                    Delete Policy
                                </button>
                            </form>

                            <div class="flex items-center space-x-3">
                                <a href="{{ route('scaling.policies.show', $policy) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Update Policy
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateMetricFields(type) {
            const scheduleField = document.getElementById('schedule_field');
            const metricField = document.getElementById('metric_field');
            const thresholdUpField = document.getElementById('threshold_up_field');
            const thresholdDownField = document.getElementById('threshold_down_field');
            
            if (type === 'schedule') {
                scheduleField.classList.remove('hidden');
                metricField.classList.add('hidden');
                thresholdUpField.classList.add('hidden');
                thresholdDownField.classList.add('hidden');
            } else {
                scheduleField.classList.add('hidden');
                metricField.classList.remove('hidden');
                thresholdUpField.classList.remove('hidden');
                thresholdDownField.classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>
