<nav x-data="{ open: false }" class="bg-neutral-800 border-b border-neutral-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-primary-600 to-primary-700 rounded-lg flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C10.9 2 10 2.9 10 4C10 4.3 10.1 4.6 10.2 4.9C8.9 5.5 8 6.7 8 8.1C8 9 8.4 9.8 9 10.3C8.4 10.8 8 11.5 8 12.4C8 13.1 8.3 13.7 8.7 14.2C7.7 14.9 7 16 7 17.3C7 19.3 8.7 21 10.7 21C11.1 21 11.5 20.9 11.9 20.8C12.2 20.9 12.6 21 13 21C15.2 21 17 19.2 17 17C17 15.9 16.5 14.9 15.7 14.2C16.1 13.7 16.4 13.1 16.4 12.4C16.4 11.5 16 10.8 15.4 10.3C16 9.8 16.4 9 16.4 8.1C16.4 6.7 15.5 5.5 14.2 4.9C14.3 4.6 14.4 4.3 14.4 4C14.4 2.9 13.5 2 12.4 2H12Z"/>
                                </svg>
                            </div>
                            <span class="text-lg font-bold text-primary-700">Pudim</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('servers.index')" :active="request()->routeIs('servers.*')">
                        {{ __('Servidores') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('sites.index')" :active="request()->routeIs('sites.*')">
                        {{ __('Sites') }}
                    </x-nav-link>
                    
                    <!-- Dropdown para Cloud -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = ! open" class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-neutral-500 hover:text-neutral-300 hover:border-neutral-600 focus:outline-none focus:text-neutral-300 focus:border-neutral-600 transition duration-150 ease-in-out">
                            {{ __('Clouds') }}
                            <svg class="ms-1 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-neutral-800 ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <a href="{{ route('aws-credentials.index') }}" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-neutral-700">AWS</a>
                                <a href="{{ route('azure-credentials.index') }}" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-neutral-700">Azure</a>
                                <a href="{{ route('gcp-credentials.index') }}" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-neutral-700">Google Cloud</a>
                                <a href="{{ route('digitalocean-credentials.index') }}" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-neutral-700">DigitalOcean</a>
                                <a href="{{ route('cloudflare-accounts.index') }}" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-neutral-700">Cloudflare</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dropdown para Ferramentas -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = ! open" class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-neutral-500 hover:text-neutral-300 hover:border-neutral-600 focus:outline-none focus:text-neutral-300 focus:border-neutral-600 transition duration-150 ease-in-out">
                            {{ __('Ferramentas') }}
                            <svg class="ms-1 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute z-50 mt-2 w-56 rounded-md shadow-lg bg-neutral-800 ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <div class="px-4 py-2 text-xs font-semibold text-neutral-400 uppercase tracking-wider">Banco de Dados</div>
                                <a href="{{ route('databases.index') }}" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-neutral-700">🗄️ Gerenciar Bancos</a>
                                
                                <div class="border-t border-neutral-100 my-1"></div>
                                <div class="px-4 py-2 text-xs font-semibold text-neutral-400 uppercase tracking-wider">Workers</div>
                                <a href="{{ route('queue-workers.index') }}" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-neutral-700">⚡ Queue Workers</a>
                                
                                <div class="border-t border-neutral-100 my-1"></div>
                                <div class="px-4 py-2 text-xs font-semibold text-neutral-400 uppercase tracking-wider">SSL</div>
                                <a href="{{ route('ssl-certificates.index') }}" class="block px-4 py-2 text-sm text-neutral-300 hover:bg-neutral-700">🔒 Certificados SSL</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-neutral-500 bg-neutral-800 hover:text-neutral-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-neutral-400 hover:text-neutral-500 hover:bg-neutral-700 focus:outline-none focus:bg-neutral-700 focus:text-neutral-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('servers.index')" :active="request()->routeIs('servers.*')">
                {{ __('Servidores') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('sites.index')" :active="request()->routeIs('sites.*')">
                {{ __('Sites') }}
            </x-responsive-nav-link>
            
            <!-- Cloud Credentials -->
            <div class="pt-2 pb-2 border-t border-neutral-700">
                <div class="px-4 text-xs font-semibold text-neutral-400 uppercase tracking-wider">Clouds</div>
                <x-responsive-nav-link :href="route('aws-credentials.index')">AWS</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('azure-credentials.index')">Azure</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('gcp-credentials.index')">Google Cloud</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('digitalocean-credentials.index')">DigitalOcean</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('cloudflare-accounts.index')">Cloudflare</x-responsive-nav-link>
            </div>
            
            <!-- Tools -->
            <div class="pt-2 pb-2 border-t border-neutral-700">
                <div class="px-4 text-xs font-semibold text-neutral-400 uppercase tracking-wider">Ferramentas</div>
                <x-responsive-nav-link href="{{ route('databases.index') }}">🗄️ Gerenciar Bancos</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('queue-workers.index') }}">⚡ Queue Workers</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('ssl-certificates.index') }}">🔒 Certificados SSL</x-responsive-nav-link>
            </div>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-neutral-700">
            <div class="px-4">
                <div class="font-medium text-base text-neutral-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-neutral-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
