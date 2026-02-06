<!-- Sidebar Component -->
<div x-data="{ sidebarOpen: false, collapseStates: {} }" class="relative">
    <!-- Mobile overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-neutral-900/50 backdrop-blur-sm z-40 lg:hidden"
         style="display: none;"></div>

    <!-- Mobile menu button -->
    <button @click="sidebarOpen = !sidebarOpen" 
            class="fixed top-4 left-4 z-50 lg:hidden p-2 rounded-lg bg-neutral-900 text-white shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;" />
        </svg>
    </button>

    <!-- Sidebar -->
    <aside :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
           class="fixed top-0 left-0 z-50 w-72 h-screen bg-neutral-900 border-r border-neutral-800 transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col overflow-hidden">
        
        <!-- Logo & Branding -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-neutral-800">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-600 to-amber-700 rounded-lg flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-base font-bold text-white">Agile'sDeployment</h1>
                <p class="text-xs text-amber-600">Cloud Management</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto scrollbar-thin scrollbar-thumb-neutral-700 scrollbar-track-neutral-800">
            
            <!-- Dashboard (Single Item) -->
            <div>
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="font-semibold">Dashboard</span>
                </a>
            </div>

            <!-- GERENCIAMENTO -->
            <div>
                <div class="px-3 mb-2">
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Gerenciamento</span>
                </div>
                <div class="space-y-1">
                    <a href="{{ route('servers.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('servers.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                        </svg>
                        <span class="font-medium">Servidores</span>
                    </a>
                    <a href="{{ route('sites.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('sites.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        <span class="font-medium">Sites</span>
                    </a>
                </div>
            </div>

            <div class="border-t border-neutral-800"></div>

            <!-- FERRAMENTAS -->
            <div>
                <div class="px-3 mb-2">
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Ferramentas</span>
                </div>
                <div class="space-y-1">
                    <a href="{{ route('databases.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('databases.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                        <span class="font-medium">Databases</span>
                    </a>
                    <a href="{{ route('queue-workers.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('queue-workers.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="font-medium">Workers</span>
                    </a>
                    <a href="{{ route('ssl-certificates.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('ssl-certificates.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span class="font-medium">SSL</span>
                    </a>
                    <a href="{{ route('cloudflare-accounts.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('cloudflare-accounts.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13.2 1.68c.71.18 1.36.53 1.89 1.03.53.5.92 1.14 1.13 1.84.79-.05 1.57.13 2.27.53.7.39 1.27.98 1.64 1.69.36.71.5 1.51.38 2.29-.11.78-.46 1.52-1 2.09 1.23.74 2.03 2.07 2.03 3.54 0 2.3-1.86 4.16-4.16 4.16H6.46c-2.76 0-5-2.24-5-5 0-1.85 1-3.46 2.49-4.33-.06-.27-.09-.54-.09-.82 0-2.07 1.68-3.75 3.75-3.75.34 0 .68.05 1 .14C9.53 2.84 11.23 1.68 13.2 1.68z"/>
                        </svg>
                        <span class="font-medium">CloudFlare</span>
                    </a>
                </div>
            </div>

            <div class="border-t border-neutral-800"></div>

            <!-- SYNC -->
            <div>
                <div class="px-3 mb-2">
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Sync</span>
                </div>
                <div class="space-y-1">
                    <a href="{{ route('github.repositories') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('github.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                        </svg>
                        <span class="font-medium">Github</span>
                    </a>
                    <a href="{{ route('aws-credentials.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('aws-*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6.763 10.036c0 .296.032.535.088.71.064.176.144.368.256.576.04.063.056.127.056.183 0 .08-.048.16-.152.24l-.503.335a.383.383 0 01-.208.072c-.08 0-.16-.04-.239-.112a2.47 2.47 0 01-.287-.375 6.18 6.18 0 01-.248-.471c-.622.734-1.405 1.101-2.347 1.101-.67 0-1.205-.191-1.596-.574-.391-.384-.591-.894-.591-1.533 0-.678.239-1.226.726-1.644.487-.417 1.133-.627 1.955-.627.272 0 .551.024.846.064.296.04.6.104.918.176v-.583c0-.607-.127-1.03-.375-1.277-.255-.248-.686-.367-1.3-.367-.28 0-.568.031-.863.103-.296.072-.583.16-.862.272a2.287 2.287 0 01-.28.104.407.407 0 01-.113.023c-.099 0-.151-.071-.151-.223v-.351c0-.119.016-.208.056-.264a.62.62 0 01.215-.168c.28-.144.615-.264 1.005-.36.391-.095.808-.144 1.253-.144.957 0 1.656.216 2.101.647.438.432.662 1.085.662 1.963v2.586zm-3.24 1.214c.263 0 .534-.048.822-.144.287-.096.543-.271.758-.51.128-.152.224-.32.279-.512.056-.191.088-.423.088-.694v-.335a6.66 6.66 0 00-.735-.136 6.02 6.02 0 00-.75-.048c-.535 0-.926.104-1.189.32-.263.215-.39.518-.39.917 0 .375.095.655.295.846.191.2.47.296.822.296zm6.41.862c-.128 0-.215-.023-.271-.08-.057-.048-.104-.151-.145-.288l-1.595-5.257c-.04-.135-.063-.224-.063-.27 0-.112.056-.175.168-.175h.686c.135 0 .226.024.278.08.056.048.095.152.136.288l1.141 4.495 1.06-4.495c.032-.135.071-.24.127-.288a.554.554 0 01.287-.08h.559c.136 0 .227.024.287.08.057.048.104.152.128.288l1.076 4.543 1.173-4.543c.04-.135.087-.24.136-.288a.483.483 0 01.279-.08h.654c.111 0 .175.056.175.175 0 .048-.008.104-.023.168-.016.063-.04.135-.08.215l-1.636 5.257c-.04.135-.088.24-.144.288-.056.057-.15.08-.271.08h-.607c-.135 0-.226-.024-.286-.08-.057-.057-.104-.16-.128-.296l-1.053-4.368-1.044 4.36c-.032.135-.071.24-.127.296-.057.056-.15.08-.287.08h-.606zm10.116.215c-.407 0-.814-.048-1.205-.136-.39-.095-.678-.2-.853-.32a.69.69 0 01-.215-.231c-.032-.08-.048-.168-.048-.256v-.366c0-.151.056-.223.16-.223.048 0 .095.008.151.024.056.016.136.048.216.08.279.12.582.215.901.279.32.064.63.096.95.096.502 0 .894-.088 1.165-.264a.86.86 0 00.415-.758.777.777 0 00-.215-.559c-.144-.151-.415-.287-.83-.423l-1.19-.375c-.606-.191-1.053-.479-1.34-.83-.287-.36-.43-.758-.43-1.197 0-.35.071-.654.207-.917.144-.264.335-.486.575-.67.239-.184.51-.32.838-.415.319-.096.655-.144 1.012-.144.175 0 .359.008.535.032.183.024.35.056.518.088.16.04.312.08.455.127.144.048.256.096.336.144a.69.69 0 01.24.2.43.43 0 01.071.263v.335c0 .151-.056.23-.168.23-.064 0-.167-.031-.296-.103-.447-.207-.95-.311-1.517-.311-.455 0-.815.072-1.06.216-.247.144-.367.375-.367.71 0 .224.08.416.24.584.159.167.454.335.886.479l1.165.367c.598.191 1.037.455 1.3.798.264.343.39.75.39 1.213 0 .359-.072.686-.215.965-.144.288-.336.53-.583.734-.248.2-.543.36-.901.463-.36.111-.774.167-1.237.167z"/>
                        </svg>
                        <span class="font-medium">Cloud</span>
                    </a>
                </div>
            </div>

            <div class="border-t border-neutral-800"></div>

            <!-- BACKUP -->
            <div>
                <div class="px-3 mb-2">
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Backup</span>
                </div>
                <div class="space-y-1">
                    <a href="{{ route('backups.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('backups.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        <span class="font-medium">Backups</span>
                    </a>
                </div>
            </div>

            <div class="border-t border-neutral-800"></div>

            <!-- TERMINAL -->
            <div>
                <div class="px-3 mb-2">
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Terminal</span>
                </div>
                <div class="space-y-1">
                    <a href="{{ route('terminal.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('terminal.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'text-neutral-400 hover:bg-neutral-800 hover:text-amber-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-medium">SSH Terminal</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- User Card (Footer) -->
        <div class="px-4 py-4 border-t border-neutral-800">
            <div x-data="{ userMenuOpen: false }" class="relative">
                <button @click="userMenuOpen = !userMenuOpen" 
                        class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg transition-all hover:bg-neutral-800 text-neutral-300">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-600 to-amber-700 flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 text-left">
                        <div class="text-sm font-semibold text-white">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-neutral-500">{{ Auth::user()->email }}</div>
                    </div>
                    <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- User Dropdown -->
                <div x-show="userMenuOpen" 
                     @click.away="userMenuOpen = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute bottom-full left-0 right-0 mb-2 bg-neutral-800 rounded-lg shadow-xl border border-neutral-700 overflow-hidden"
                     style="display: none;">
                    <a href="{{ route('profile.edit') }}" 
                       class="block px-4 py-2.5 text-sm text-neutral-300 hover:bg-neutral-700 hover:text-white transition-colors">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full text-left px-4 py-2.5 text-sm text-red-400 hover:bg-neutral-700 hover:text-red-300 transition-colors border-t border-neutral-700">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
</div>
