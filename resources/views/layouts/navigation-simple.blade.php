<!-- DEBUG: Temporary simple navigation for testing -->
<nav class="bg-white dark:bg-neutral-800 border-b border-neutral-100 dark:border-neutral-700">
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
                            <span class="text-lg font-bold text-primary-700 dark:text-primary-400">Pudim</span>
                        </div>
                    </a>
                </div>

                <!-- Simple Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                    <x-nav-link :href="route('servers.index')" :active="request()->routeIs('servers.*')">
                        Servidores
                    </x-nav-link>
                    <x-nav-link :href="route('sites.index')" :active="request()->routeIs('sites.*')">
                        Sites
                    </x-nav-link>
                    <x-nav-link href="{{ route('databases.index') }}">
                        🗄️ Databases
                    </x-nav-link>
                    <x-nav-link href="{{ route('queue-workers.index') }}">
                        ⚡ Workers
                    </x-nav-link>
                    <x-nav-link href="{{ route('ssl-certificates.index') }}">
                        🔒 SSL
                    </x-nav-link>
                    <x-nav-link href="{{ route('aws-credentials.index') }}">
                        AWS
                    </x-nav-link>
                    <x-nav-link href="{{ route('cloudflare-accounts.index') }}">
                        Cloudflare
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown (existing) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-neutral-500 dark:text-neutral-400 bg-white dark:bg-neutral-800 hover:text-neutral-700 dark:hover:text-neutral-300 focus:outline-none transition ease-in-out duration-150">
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

            <!-- Hamburger (mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button class="inline-flex items-center justify-center p-2 rounded-md text-neutral-400 dark:text-neutral-500 hover:text-neutral-500 dark:hover:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-900 focus:outline-none focus:bg-neutral-100 dark:focus:bg-neutral-900 focus:text-neutral-500 dark:focus:text-neutral-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('servers.index')" :active="request()->routeIs('servers.*')">
                Servidores
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('sites.index')" :active="request()->routeIs('sites.*')">
                Sites
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('databases.index') }}">
                🗄️ Databases
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('queue-workers.index') }}">
                ⚡ Workers
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('ssl-certificates.index') }}">
                🔒 SSL
            </x-responsive-nav-link>
        </div>
    </div>
</nav>