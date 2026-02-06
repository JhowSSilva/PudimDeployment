<nav style="background: white; border-bottom: 1px solid #e5e7eb; padding: 0;">
    <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; height: 4rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <!-- Logo -->
                <div>
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div style="display: flex; gap: 2rem; align-items: center;">
                    <a href="{{ route('dashboard') }}" style="color: #6b7280; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out;" onmouseover="this.style.color='#1f2937'" onmouseout="this.style.color='#6b7280'">Dashboard</a>
                    <a href="{{ route('servers.index') }}" style="color: #6b7280; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out;" onmouseover="this.style.color='#1f2937'" onmouseout="this.style.color='#6b7280'">Servidores</a>
                    <a href="{{ route('sites.index') }}" style="color: #6b7280; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out;" onmouseover="this.style.color='#1f2937'" onmouseout="this.style.color='#6b7280'">Sites</a>
                    <a href="{{ route('databases.index') }}" style="color: #16a34a; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out; font-weight: bold;" onmouseover="this.style.color='#15803d'" onmouseout="this.style.color='#16a34a'">🗄️ Databases</a>
                    <a href="{{ route('queue-workers.index') }}" style="color: #9333ea; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out; font-weight: bold;" onmouseover="this.style.color='#7e22ce'" onmouseout="this.style.color='#9333ea'">⚡ Workers</a>
                    <a href="{{ route('ssl-certificates.index') }}" style="color: #b45309; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out; font-weight: bold;" onmouseover="this.style.color='#92400e'" onmouseout="this.style.color='#b45309'">🔒 SSL</a>
                    <a href="{{ route('aws-credentials.index') }}" style="color: #6b7280; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out;" onmouseover="this.style.color='#1f2937'" onmouseout="this.style.color='#6b7280'">AWS</a>
                    <a href="{{ route('cloudflare-accounts.index') }}" style="color: #6b7280; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out;" onmouseover="this.style.color='#1f2937'" onmouseout="this.style.color='#6b7280'">Cloudflare</a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
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
        </div>
    </div>
</nav>