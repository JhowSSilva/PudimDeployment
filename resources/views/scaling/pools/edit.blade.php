<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Server Pool') }}: {{ $pool->name }}
            </h2>
            <a href="{{ route('scaling.pools.show', $pool) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
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
                    <form method="POST" action="{{ route('scaling.pools.update', $pool) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $pool->name) }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $pool->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Environment, Region, and Status -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="environment" class="block text-sm font-medium text-gray-700">Environment *</label>
                                        <select name="environment" id="environment" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="production" {{ old('environment', $pool->environment) == 'production' ? 'selected' : '' }}>Production</option>
                                            <option value="staging" {{ old('environment', $pool->environment) == 'staging' ? 'selected' : '' }}>Staging</option>
                                            <option value="development" {{ old('environment', $pool->environment) == 'development' ? 'selected' : '' }}>Development</option>
                                        </select>
                                        @error('environment')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="region" class="block text-sm font-medium text-gray-700">Region</label>
                                        <input type="text" name="region" id="region" value="{{ old('region', $pool->region) }}"
                                            placeholder="e.g., us-east-1, eu-west-1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('region')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                        <select name="status" id="status" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="active" {{ old('status', $pool->status) == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status', $pool->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="scaling" {{ old('status', $pool->status) == 'scaling' ? 'selected' : '' }}>Scaling</option>
                                            <option value="error" {{ old('status', $pool->status) == 'error' ? 'selected' : '' }}>Error</option>
                                        </select>
                                        @error('status')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scaling Configuration -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Scaling Configuration</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="min_servers" class="block text-sm font-medium text-gray-700">Minimum Servers *</label>
                                    <input type="number" name="min_servers" id="min_servers" value="{{ old('min_servers', $pool->min_servers) }}" min="1" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('min_servers')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="max_servers" class="block text-sm font-medium text-gray-700">Maximum Servers *</label>
                                    <input type="number" name="max_servers" id="max_servers" value="{{ old('max_servers', $pool->max_servers) }}" min="1" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('max_servers')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="desired_servers" class="block text-sm font-medium text-gray-700">Desired Servers *</label>
                                    <input type="number" name="desired_servers" id="desired_servers" value="{{ old('desired_servers', $pool->desired_servers) }}" min="1" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('desired_servers')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <p class="mt-2 text-sm text-gray-500">
                                Desired servers must be between minimum and maximum. The pool will auto-scale within these limits.
                            </p>
                        </div>

                        <!-- Health & Auto-healing -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Health & Auto-healing</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="health_check_interval" class="block text-sm font-medium text-gray-700">Health Check Interval (seconds) *</label>
                                    <input type="number" name="health_check_interval" id="health_check_interval" value="{{ old('health_check_interval', $pool->health_check_interval) }}" min="10" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('health_check_interval')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">Minimum: 10 seconds</p>
                                </div>

                                <div class="flex items-center h-full pt-6">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="auto_healing" value="1" {{ old('auto_healing', $pool->auto_healing) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Enable Auto-healing</span>
                                    </label>
                                    <div class="ml-2 group relative">
                                        <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="hidden group-hover:block absolute z-10 w-64 p-2 mt-2 text-sm text-white bg-gray-900 rounded shadow-lg">
                                            Automatically replace unhealthy servers with new instances
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Server Selection -->
                        <div class="pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Servers in Pool</h3>
                            
                            @if($servers->count() > 0)
                                <div class="space-y-2">
                                    @foreach($servers as $server)
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" name="servers[]" value="{{ $server->id }}" 
                                                {{ in_array($server->id, old('servers', $selectedServers)) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <div class="ml-3 flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-900">{{ $server->name }}</span>
                                                    <span class="text-xs px-2 py-1 rounded-full {{ $server->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                        {{ ucfirst($server->status) }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-500">{{ $server->ip_address }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('servers')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            @else
                                <div class="text-center py-8 bg-gray-50 rounded-lg">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-600">No servers available</p>
                                </div>
                            @endif
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <form method="POST" action="{{ route('scaling.pools.destroy', $pool) }}" onsubmit="return confirm('Are you sure you want to delete this server pool?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                    Delete Pool
                                </button>
                            </form>

                            <div class="flex items-center space-x-3">
                                <a href="{{ route('scaling.pools.show', $pool) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Update Server Pool
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
