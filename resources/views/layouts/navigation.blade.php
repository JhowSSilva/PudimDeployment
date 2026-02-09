<nav class="bg-neutral-800 border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div>
                    <a href="{{ route('dashboard') }}">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-primary-600 to-primary-700 rounded-lg flex items-center justify-center shadow-lg">
                                <x-application-logo class="w-5 h-5 text-white" />
                            </div>
                            <span class="text-lg font-bold text-primary-400">Pudim</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden lg:flex gap-4 items-center overflow-x-auto">
                    <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-neutral-100 py-2 px-1 border-b-2 border-transparent text-sm font-medium transition-colors duration-200 whitespace-nowrap">Dashboard</a>
                    <a href="{{ route('servers.index') }}" class="text-neutral-400 hover:text-neutral-100 py-2 px-1 border-b-2 border-transparent text-sm font-medium transition-colors duration-200 whitespace-nowrap">Servidores</a>
                    <a href="{{ route('sites.index') }}" class="text-neutral-400 hover:text-neutral-100 py-2 px-1 border-b-2 border-transparent text-sm font-medium transition-colors duration-200 whitespace-nowrap">Sites</a>
                    <a href="{{ route('databases.index') }}" class="text-success-400 hover:text-success-300 py-2 px-1 border-b-2 border-transparent text-sm font-semibold transition-colors duration-200 whitespace-nowrap">🗄️ Databases</a>
                    <a href="{{ route('queue-workers.index') }}" class="text-purple-400 hover:text-purple-300 py-2 px-1 border-b-2 border-transparent text-sm font-semibold transition-colors duration-200 whitespace-nowrap">⚡ Workers</a>
                    <a href="{{ route('ssl-certificates.index') }}" class="text-primary-400 hover:text-primary-300 py-2 px-1 border-b-2 border-transparent text-sm font-semibold transition-colors duration-200 whitespace-nowrap">🔒 SSL</a>
                    <a href="{{ route('aws-credentials.index') }}" class="text-neutral-400 hover:text-neutral-100 py-2 px-1 border-b-2 border-transparent text-sm font-medium transition-colors duration-200 whitespace-nowrap">AWS</a>
                    <a href="{{ route('cloudflare-accounts.index') }}" class="text-neutral-400 hover:text-neutral-100 py-2 px-1 border-b-2 border-transparent text-sm font-medium transition-colors duration-200 whitespace-nowrap">Cloudflare</a>
                    <a href="{{ route('monitoring.index') }}" class="text-success-400 hover:text-success-300 py-2 px-1 border-b-2 border-transparent text-sm font-semibold transition-colors duration-200 whitespace-nowrap">📊 Monitoring</a>
                    <a href="{{ route('alerts.index') }}" class="text-error-400 hover:text-error-300 py-2 px-1 border-b-2 border-transparent text-sm font-semibold transition-colors duration-200 whitespace-nowrap">🚨 Alerts</a>
                    <a href="{{ route('activity.index') }}" class="text-purple-400 hover:text-purple-300 py-2 px-1 border-b-2 border-transparent text-sm font-semibold transition-colors duration-200 whitespace-nowrap">📝 Activity</a>
                    <a href="{{ route('scaling.pools.index') }}" class="text-warning-400 hover:text-warning-300 py-2 px-1 border-b-2 border-transparent text-sm font-semibold transition-colors duration-200 whitespace-nowrap">⚡ Auto-scaling</a>
                    <a href="{{ route('cicd.pipelines.index') }}" class="text-purple-400 hover:text-purple-300 py-2 px-1 border-b-2 border-transparent text-sm font-semibold transition-colors duration-200 whitespace-nowrap">⚙️ CI/CD</a>
                    <a href="{{ route('billing.plans') }}" class="text-info-400 hover:text-info-300 py-2 px-1 border-b-2 border-transparent text-sm font-semibold transition-colors duration-200 whitespace-nowrap">💳 Planos</a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-neutral-400 bg-neutral-800 hover:text-neutral-300 focus:outline-none transition duration-200">
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