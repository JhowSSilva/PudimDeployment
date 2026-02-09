<nav style="background: white; border-bottom: 1px solid #e5e7eb; padding: 0;">
    <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; height: 4rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <!-- Logo -->
                <div>
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
                    <a href="{{ route('monitoring.index') }}" style="color: #059669; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out; font-weight: bold;" onmouseover="this.style.color='#047857'" onmouseout="this.style.color='#059669'">📊 Monitoring</a>
                    <a href="{{ route('alerts.index') }}" style="color: #dc2626; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out; font-weight: bold;" onmouseover="this.style.color='#b91c1c'" onmouseout="this.style.color='#dc2626'">🚨 Alerts</a>
                    <a href="{{ route('activity.index') }}" style="color: #8b5cf6; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out; font-weight: bold;" onmouseover="this.style.color='#7c3aed'" onmouseout="this.style.color='#8b5cf6'">📝 Activity</a>
                    <a href="{{ route('scaling.pools.index') }}" style="color: #ea580c; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out; font-weight: bold;" onmouseover="this.style.color='#c2410c'" onmouseout="this.style.color='#ea580c'">⚡ Auto-scaling</a>
                    <a href="{{ route('cicd.pipelines.index') }}" style="color: #7c3aed; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out; font-weight: bold;" onmouseover="this.style.color='#6d28d9'" onmouseout="this.style.color='#7c3aed'">⚙️ CI/CD</a>
                    <a href="{{ route('billing.plans') }}" style="color: #0891b2; text-decoration: none; padding: 0.5rem 0.25rem; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease-in-out; font-weight: bold;" onmouseover="this.style.color='#0e7490'" onmouseout="this.style.color='#0891b2'">💳 Planos</a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div>
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
                        <x-dropdown-link :href="route('team.roles.index')">
                            Team Roles & Permissions
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('billing.subscription')">
                            Minha Assinatura
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('billing.usage')">
                            Uso de Recursos
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