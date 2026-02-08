<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Load Balancer') }}: {{ $loadBalancer->name }}
            </h2>
            <a href="{{ route('scaling.load-balancers.show', $loadBalancer) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
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
                    <form method="POST" action="{{ route('scaling.load-balancers.update', $loadBalancer) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $loadBalancer->name) }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $loadBalancer->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="server_pool_id" class="block text-sm font-medium text-gray-700">Server Pool</label>
                                        <select name="server_pool_id" id="server_pool_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select a server pool (optional)</option>
                                            @foreach($serverPools as $pool)
                                                <option value="{{ $pool->id }}" {{ old('server_pool_id', $loadBalancer->server_pool_id) == $pool->id ? 'selected' : '' }}>
                                                    {{ $pool->name }} ({{ $pool->current_servers }} servers)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('server_pool_id')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                        <select name="status" id="status" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="active" {{ old('status', $loadBalancer->status) == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status', $loadBalancer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="error" {{ old('status', $loadBalancer->status) == 'error' ? 'selected' : '' }}>Error</option>
                                        </select>
                                        @error('status')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Network Configuration -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Network Configuration</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="ip_address" class="block text-sm font-medium text-gray-700">IP Address</label>
                                    <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', $loadBalancer->ip_address) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('ip_address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="port" class="block text-sm font-medium text-gray-700">Port</label>
                                    <input type="number" name="port" id="port" value="{{ old('port', $loadBalancer->port) }}" min="1" max="65535"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('port')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="protocol" class="block text-sm font-medium text-gray-700">Protocol *</label>
                                    <select name="protocol" id="protocol" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="http" {{ old('protocol', $loadBalancer->protocol) == 'http' ? 'selected' : '' }}>HTTP</option>
                                        <option value="https" {{ old('protocol', $loadBalancer->protocol) == 'https' ? 'selected' : '' }}>HTTPS</option>
                                        <option value="tcp" {{ old('protocol', $loadBalancer->protocol) == 'tcp' ? 'selected' : '' }}>TCP</option>
                                        <option value="udp" {{ old('protocol', $loadBalancer->protocol) == 'udp' ? 'selected' : '' }}>UDP</option>
                                    </select>
                                    @error('protocol')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Load Balancing Algorithm -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Load Balancing Algorithm</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Algorithm *</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @php
                                        $algorithms = [
                                            'round_robin' => ['name' => 'Round Robin', 'desc' => 'Distributes requests equally across all servers'],
                                            'least_connections' => ['name' => 'Least Connections', 'desc' => 'Routes to server with fewest active connections'],
                                            'ip_hash' => ['name' => 'IP Hash', 'desc' => 'Routes same IP to same server consistently'],
                                            'weighted' => ['name' => 'Weighted', 'desc' => 'Distributes based on server weights']
                                        ];
                                    @endphp

                                    @foreach($algorithms as $value => $algo)
                                    <label class="relative flex cursor-pointer rounded-lg border {{ old('algorithm', $loadBalancer->algorithm) == $value ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-white' }} p-4 shadow-sm focus:outline-none hover:border-blue-500">
                                        <input type="radio" name="algorithm" value="{{ $value }}" {{ old('algorithm', $loadBalancer->algorithm) == $value ? 'checked' : '' }} class="sr-only">
                                        <span class="flex flex-1">
                                            <span class="flex flex-col">
                                                <span class="block text-sm font-medium text-gray-900">{{ $algo['name'] }}</span>
                                                <span class="mt-1 flex items-center text-sm text-gray-500">{{ $algo['desc'] }}</span>
                                            </span>
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                                @error('algorithm')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- SSL Configuration -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">SSL Configuration</h3>
                            
                            <div class="space-y-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="ssl_enabled" value="1" {{ old('ssl_enabled', $loadBalancer->ssl_enabled) ? 'checked' : '' }}
                                        id="ssl_enabled" onchange="document.getElementById('ssl_fields').classList.toggle('hidden')"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Enable SSL/TLS</span>
                                </label>

                                <div id="ssl_fields" class="{{ old('ssl_enabled', $loadBalancer->ssl_enabled) ? '' : 'hidden' }} space-y-4 pl-6">
                                    <div>
                                        <label for="ssl_certificate" class="block text-sm font-medium text-gray-700">SSL Certificate</label>
                                        <textarea name="ssl_certificate" id="ssl_certificate" rows="4"
                                            placeholder="-----BEGIN CERTIFICATE-----"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-xs">{{ old('ssl_certificate', $loadBalancer->ssl_certificate) }}</textarea>
                                        @error('ssl_certificate')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="ssl_private_key" class="block text-sm font-medium text-gray-700">SSL Private Key</label>
                                        <textarea name="ssl_private_key" id="ssl_private_key" rows="4"
                                            placeholder="-----BEGIN PRIVATE KEY-----"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-xs">{{ old('ssl_private_key', $loadBalancer->ssl_private_key) }}</textarea>
                                        @error('ssl_private_key')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Health Checks -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Health Checks</h3>
                            
                            <div class="space-y-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="health_check_enabled" value="1" {{ old('health_check_enabled', $loadBalancer->health_check_enabled) ? 'checked' : '' }}
                                        id="health_check_enabled" onchange="document.getElementById('health_check_fields').classList.toggle('hidden')"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Enable Health Checks</span>
                                </label>

                                <div id="health_check_fields" class="{{ old('health_check_enabled', $loadBalancer->health_check_enabled) ? '' : 'hidden' }} space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="health_check_path" class="block text-sm font-medium text-gray-700">Health Check Path</label>
                                            <input type="text" name="health_check_path" id="health_check_path" value="{{ old('health_check_path', $loadBalancer->health_check_path) }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('health_check_path')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="health_check_interval" class="block text-sm font-medium text-gray-700">Interval (seconds)</label>
                                            <input type="number" name="health_check_interval" id="health_check_interval" value="{{ old('health_check_interval', $loadBalancer->health_check_interval) }}" min="5"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('health_check_interval')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="health_check_timeout" class="block text-sm font-medium text-gray-700">Timeout (seconds)</label>
                                            <input type="number" name="health_check_timeout" id="health_check_timeout" value="{{ old('health_check_timeout', $loadBalancer->health_check_timeout) }}" min="1"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('health_check_timeout')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="healthy_threshold" class="block text-sm font-medium text-gray-700">Healthy Threshold</label>
                                            <input type="number" name="healthy_threshold" id="healthy_threshold" value="{{ old('healthy_threshold', $loadBalancer->healthy_threshold) }}" min="1"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('healthy_threshold')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="unhealthy_threshold" class="block text-sm font-medium text-gray-700">Unhealthy Threshold</label>
                                            <input type="number" name="unhealthy_threshold" id="unhealthy_threshold" value="{{ old('unhealthy_threshold', $loadBalancer->unhealthy_threshold) }}" min="1"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('unhealthy_threshold')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Session Persistence -->
                        <div class="pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Session Persistence</h3>
                            
                            <div class="space-y-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="sticky_sessions" value="1" {{ old('sticky_sessions', $loadBalancer->sticky_sessions) ? 'checked' : '' }}
                                        id="sticky_sessions" onchange="document.getElementById('session_ttl_field').classList.toggle('hidden')"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Enable Sticky Sessions</span>
                                </label>

                                <div id="session_ttl_field" class="{{ old('sticky_sessions', $loadBalancer->sticky_sessions) ? '' : 'hidden' }} pl-6">
                                    <label for="session_ttl" class="block text-sm font-medium text-gray-700">Session TTL (seconds)</label>
                                    <input type="number" name="session_ttl" id="session_ttl" value="{{ old('session_ttl', $loadBalancer->session_ttl) }}" min="60"
                                        class="mt-1 block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('session_ttl')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <form method="POST" action="{{ route('scaling.load-balancers.destroy', $loadBalancer) }}" onsubmit="return confirm('Are you sure you want to delete this load balancer?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                    Delete Load Balancer
                                </button>
                            </form>

                            <div class="flex items-center space-x-3">
                                <a href="{{ route('scaling.load-balancers.show', $loadBalancer) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Update Load Balancer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
