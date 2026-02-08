<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-100">Team Roles & Permissions</h1>
                <p class="mt-1 text-sm text-neutral-400">Manage custom roles and permissions for your team</p>
            </div>
            <x-button href="{{ route('team.roles.create') }}" variant="primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Role
            </x-button>
        </div>
    </div>

    @if($roles->isEmpty())
        <!-- Empty State -->
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-neutral-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-neutral-300 mb-2">No Custom Roles</h3>
                <p class="text-neutral-400 mb-6">Create custom roles to manage team permissions</p>
                <x-button href="{{ route('team.roles.create') }}" variant="primary">
                    Create First Role
                </x-button>
            </div>
        </x-card>
    @else
        <div class="grid grid-cols-1 gap-6">
            @foreach($roles as $role)
                <x-card>
                    <div class="flex items-start justify-between">
                        <!-- Role Info -->
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <!-- Color Indicator -->
                                <div class="w-4 h-4 rounded-full" style="background-color: {{ $role->color }}"></div>
                                
                                <h3 class="text-lg font-bold text-neutral-100">{{ $role->name }}</h3>
                                
                                @if($role->is_system)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-700 text-neutral-300">
                                        System Role
                                    </span>
                                @endif
                            </div>

                            @if($role->description)
                                <p class="text-sm text-neutral-400 mb-4">{{ $role->description }}</p>
                            @endif

                            <!-- Stats -->
                            <div class="flex items-center space-x-6 text-sm text-neutral-400 mb-4">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    {{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    {{ count($role->permissions) }} {{ Str::plural('permission', count($role->permissions)) }}
                                </span>
                            </div>

                            <!-- Permissions Preview -->
                            @if(!empty($role->permissions))
                                <div class="flex flex-wrap gap-2">
                                    @foreach(array_slice($role->permissions, 0, 5) as $permission)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-primary-900/20 text-primary-400">
                                            {{ ucwords(str_replace('-', ' ', $permission)) }}
                                        </span>
                                    @endforeach
                                    @if(count($role->permissions) > 5)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-neutral-700 text-neutral-300">
                                            +{{ count($role->permissions) - 5 }} more
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 ml-6">
                            @if(!$role->is_system)
                                <x-button href="{{ route('team.roles.edit', $role) }}" variant="ghost" size="sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </x-button>

                                <form 
                                    action="{{ route('team.roles.destroy', $role) }}" 
                                    method="POST" 
                                    class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this role? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="ghost" size="sm">
                                        <svg class="w-5 h-5 text-error-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </x-button>
                                </form>
                            @else
                                <span class="text-xs text-neutral-500 italic">Cannot modify system role</span>
                            @endif
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</x-layout>
