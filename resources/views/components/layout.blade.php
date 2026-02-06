<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-gradient-to-br from-neutral-50 to-neutral-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? "Pudim Deployment - Cloud Management Platform" }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('paw.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full font-sans antialiased">
    <div class="min-h-full">
        <!-- Navbar -->
        <nav class="bg-white/80 backdrop-blur-lg border-b border-neutral-200 sticky top-0 z-50 shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex flex-shrink-0 items-center">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent">Pudim Deployment</h1>
                                    <p class="text-xs text-neutral-500 -mt-0.5">Cloud Management</p>
                                </div>
                            </div>
                        </div>
                        <div class="hidden sm:-my-px sm:ml-10 sm:flex sm:space-x-1">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('dashboard') ? 'border-primary-500 text-primary-700' : 'border-transparent text-neutral-600 hover:border-neutral-300 hover:text-neutral-900' }} px-3 pt-1 text-sm font-semibold transition-all">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Dashboard
                            </a>
                            <a href="{{ route('servers.index') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('servers.*') ? 'border-primary-500 text-primary-700' : 'border-transparent text-neutral-600 hover:border-neutral-300 hover:text-neutral-900' }} px-3 pt-1 text-sm font-semibold transition-all">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                                Servidores
                            </a>
                            <a href="{{ route('sites.index') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('sites.*') ? 'border-primary-500 text-primary-700' : 'border-transparent text-neutral-600 hover:border-neutral-300 hover:text-neutral-900' }} px-3 pt-1 text-sm font-semibold transition-all">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                Sites
                            </a>
                            
                            <!-- Tools Links -->
                            <a href="{{ route('databases.index') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('databases.*') ? 'border-primary-500 text-primary-700' : 'border-transparent text-primary-600 hover:border-primary-300 hover:text-primary-700' }} px-3 pt-1 text-sm font-bold transition-all">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                </svg>
                                Databases
                            </a>
                            <a href="{{ route('queue-workers.index') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('queue-workers.*') ? 'border-primary-500 text-primary-700' : 'border-transparent text-primary-600 hover:border-primary-300 hover:text-primary-700' }} px-3 pt-1 text-sm font-bold transition-all">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Workers
                            </a>
                            <a href="{{ route('ssl-certificates.index') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('ssl-certificates.*') ? 'border-primary-500 text-primary-700' : 'border-transparent text-primary-600 hover:border-primary-300 hover:text-primary-700' }} px-3 pt-1 text-sm font-bold transition-all">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                SSL
                            </a>
                            
                            <!-- Clouds Dropdown -->
                            <div class="relative inline-flex items-center" x-data="{ open: false }">
                                <button @click="open = !open" class="inline-flex items-center border-b-2 {{ request()->routeIs('aws-*') || request()->routeIs('azure-*') || request()->routeIs('gcp-*') || request()->routeIs('digitalocean-*') ? 'border-primary-500 text-primary-700' : 'border-transparent text-neutral-600 hover:border-neutral-300 hover:text-neutral-900' }} px-3 pt-1 text-sm font-semibold transition-all h-16">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                                    </svg>
                                    Clouds
                                    <svg class="ml-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-0 z-50 w-80 origin-top-left rounded-xl bg-white shadow-xl ring-1 ring-neutral-200 focus:outline-none max-h-[80vh] overflow-y-auto" style="top: 100%; margin-top: 0.5rem; display: none;" role="menu">
                                    <div class="p-2">
                                        <!-- AWS Section -->
                                        <div class="mb-2">
                                            <div class="px-3 py-2 text-xs font-bold text-neutral-900 uppercase tracking-wider flex items-center gap-2">
                                                <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M6.763 10.036c0 .296.032.535.088.71.064.176.144.368.256.576.04.063.056.127.056.183 0 .08-.048.16-.152.24l-.503.335a.383.383 0 01-.208.072c-.08 0-.16-.04-.239-.112a2.47 2.47 0 01-.287-.375 6.18 6.18 0 01-.248-.471c-.622.734-1.405 1.101-2.347 1.101-.67 0-1.205-.191-1.596-.574-.391-.384-.591-.894-.591-1.533 0-.678.239-1.226.726-1.644.487-.417 1.133-.627 1.955-.627.272 0 .551.024.846.064.296.04.6.104.918.176v-.583c0-.607-.127-1.03-.375-1.277-.255-.248-.686-.367-1.3-.367-.28 0-.568.031-.863.103-.296.072-.583.16-.862.272a2.287 2.287 0 01-.28.104.407.407 0 01-.113.023c-.099 0-.151-.071-.151-.223v-.351c0-.119.016-.208.056-.264a.62.62 0 01.215-.168c.28-.144.615-.264 1.005-.36.391-.095.808-.144 1.253-.144.957 0 1.656.216 2.101.647.438.432.662 1.085.662 1.963v2.586zm-3.24 1.214c.263 0 .534-.048.822-.144.287-.096.543-.271.758-.51.128-.152.224-.32.279-.512.056-.191.088-.423.088-.694v-.335a6.66 6.66 0 00-.735-.136 6.02 6.02 0 00-.75-.048c-.535 0-.926.104-1.189.32-.263.215-.39.518-.39.917 0 .375.095.655.295.846.191.2.47.296.822.296zm6.41.862c-.128 0-.215-.023-.271-.08-.057-.048-.104-.151-.145-.288l-1.595-5.257c-.04-.135-.063-.224-.063-.27 0-.112.056-.175.168-.175h.686c.135 0 .226.024.278.08.056.048.095.152.136.288l1.141 4.495 1.06-4.495c.032-.135.071-.24.127-.288a.554.554 0 01.287-.08h.559c.136 0 .227.024.287.08.057.048.104.152.128.288l1.076 4.543 1.173-4.543c.04-.135.087-.24.136-.288a.483.483 0 01.279-.08h.654c.111 0 .175.056.175.175 0 .048-.008.104-.023.168-.016.063-.04.135-.08.215l-1.636 5.257c-.04.135-.088.24-.144.288-.056.057-.15.08-.271.08h-.607c-.135 0-.226-.024-.286-.08-.057-.057-.104-.16-.128-.296l-1.053-4.368-1.044 4.36c-.032.135-.071.24-.127.296-.057.056-.15.08-.287.08h-.606zm10.116.215c-.407 0-.814-.048-1.205-.136-.39-.095-.678-.2-.853-.32a.69.69 0 01-.215-.231c-.032-.08-.048-.168-.048-.256v-.366c0-.151.056-.223.16-.223.048 0 .095.008.151.024.056.016.136.048.216.08.279.12.582.215.901.279.32.064.63.096.95.096.502 0 .894-.088 1.165-.264a.86.86 0 00.415-.758.777.777 0 00-.215-.559c-.144-.151-.415-.287-.83-.423l-1.19-.375c-.606-.191-1.053-.479-1.34-.83-.287-.36-.43-.758-.43-1.197 0-.35.071-.654.207-.917.144-.264.335-.486.575-.67.239-.184.51-.32.838-.415.319-.096.655-.144 1.012-.144.175 0 .359.008.535.032.183.024.35.056.518.088.16.04.312.08.455.127.144.048.256.096.336.144a.69.69 0 01.24.2.43.43 0 01.071.263v.335c0 .151-.056.23-.168.23-.064 0-.167-.031-.296-.103-.447-.207-.95-.311-1.517-.311-.455 0-.815.072-1.06.216-.247.144-.367.375-.367.71 0 .224.08.416.24.584.159.167.454.335.886.479l1.165.367c.598.191 1.037.455 1.3.798.264.343.39.75.39 1.213 0 .359-.072.686-.215.965-.144.288-.336.53-.583.734-.248.2-.543.36-.901.463-.36.111-.774.167-1.237.167z"/>
                                                </svg>
                                                Amazon Web Services
                                            </div>
                                            <a href="{{ route('aws-provision.step1') }}" class="group flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 text-primary-600 group-hover:bg-primary-200 mr-3">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold">Provisionar EC2</div>
                                                    <div class="text-xs text-neutral-500 group-hover:text-primary-600">Nova instância</div>
                                                </div>
                                            </a>
                                            <a href="{{ route('aws-credentials.index') }}" class="group flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-neutral-700 hover:bg-neutral-100 hover:text-neutral-900 transition-all">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-neutral-100 text-neutral-600 group-hover:bg-neutral-200 mr-3">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold">Credenciais</div>
                                                    <div class="text-xs text-neutral-500 group-hover:text-neutral-700">Access Keys</div>
                                                </div>
                                            </a>
                                        </div>

                                        <div class="border-t border-neutral-100 my-2"></div>

                                        <!-- Azure Section -->
                                        <div class="mb-2">
                                            <div class="px-3 py-2 text-xs font-bold text-neutral-900 uppercase tracking-wider flex items-center gap-2">
                                                <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M13.05 11.561l-6.89 1.377 8.977 1.273 1.32-8.641zM10.154 3.896L0 6.27l8.26 11.604 2.92-13.978zm11.47 10.857l-6.939-1.255-1.244 8.143h8.183z"/>
                                                </svg>
                                                Microsoft Azure
                                            </div>
                                            <a href="{{ route('azure-credentials.index') }}" class="group flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 text-primary-600 group-hover:bg-primary-200 mr-3">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold">Credenciais</div>
                                                    <div class="text-xs text-neutral-500 group-hover:text-primary-600">Service Principal</div>
                                                </div>
                                            </a>
                                        </div>

                                        <div class="border-t border-neutral-100 my-2"></div>

                                        <!-- Google Cloud Section -->
                                        <div class="mb-2">
                                            <div class="px-3 py-2 text-xs font-bold text-neutral-900 uppercase tracking-wider flex items-center gap-2">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24">
                                                    <path fill="#EA4335" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                    <path fill="#4285F4" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                                    <path fill="#34A853" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                                </svg>
                                                Google Cloud Platform
                                            </div>
                                            <a href="{{ route('gcp-credentials.index') }}" class="group flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 text-primary-600 group-hover:bg-primary-200 mr-3">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold">Credenciais</div>
                                                    <div class="text-xs text-neutral-500 group-hover:text-primary-600">Service Account</div>
                                                </div>
                                            </a>
                                        </div>

                                        <div class="border-t border-neutral-100 my-2"></div>

                                        <!-- Digital Ocean Section -->
                                        <div>
                                            <div class="px-3 py-2 text-xs font-bold text-neutral-900 uppercase tracking-wider flex items-center gap-2">
                                                <svg class="w-4 h-4 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 24v-5.294h5.294C17.294 23.988 12 24 12 24zm0-5.294H6.706v-5.294H12v5.294zM6.706 13.412H1.412v-5.294h5.294v5.294zm5.294-5.294H6.706V2.824h5.294v5.294z"/>
                                                </svg>
                                                DigitalOcean
                                            </div>
                                            <a href="{{ route('digitalocean-credentials.index') }}" class="group flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 text-primary-600 group-hover:bg-primary-200 mr-3">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold">Credenciais</div>
                                                    <div class="text-xs text-neutral-500 group-hover:text-primary-600">API Token</div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Times Dropdown -->
                            @php
                                $userTeams = Auth::user()->ownedTeams->merge(Auth::user()->teams);
                                $currentTeam = Auth::user()->currentTeam();
                            @endphp
                            <div class="relative inline-flex items-center" x-data="{ open: false }">
                                <button @click="open = !open" class="inline-flex items-center border-b-2 {{ request()->routeIs('profile.edit') || request()->routeIs('teams.*') ? 'border-primary-500 text-primary-700' : 'border-transparent text-neutral-600 hover:border-neutral-300 hover:text-neutral-900' }} px-3 pt-1 text-sm font-semibold transition-all h-16">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Times
                                    <svg class="ml-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-0 z-50 w-72 origin-top-left rounded-xl bg-white shadow-xl ring-1 ring-neutral-200 focus:outline-none" style="top: 100%; margin-top: 0.5rem; display: none;" role="menu">
                                    <div class="p-2">
                                        <div class="px-3 py-2 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Time Atual</div>
                                        <div class="mb-2 px-2">
                                            <div class="bg-primary-50 border border-primary-200 rounded-lg p-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-100 text-primary-600">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="font-semibold text-neutral-900">{{ $currentTeam?->name ?? 'Nenhum time' }}</div>
                                                        @if($currentTeam && !$currentTeam->personal_team)
                                                            <div class="text-xs text-neutral-600">{{ $currentTeam->users_count ?? $currentTeam->users->count() }} membros</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($userTeams->count() > 1)
                                            <div class="border-t border-neutral-100 my-2"></div>
                                            <div class="px-3 py-2 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Trocar Time</div>
                                            @foreach($userTeams as $team)
                                                @if($currentTeam?->id !== $team->id)
                                                    <form action="{{ route('teams.switch', $team) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="w-full group flex items-center rounded-lg px-4 py-3 text-sm font-medium text-neutral-700 hover:bg-neutral-100 hover:text-neutral-900 transition-all">
                                                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-neutral-100 text-neutral-600 group-hover:bg-neutral-200 mr-3">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    @if($team->personal_team)
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                    @else
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                                    @endif
                                                                </svg>
                                                            </div>
                                                            <div class="text-left flex-1">
                                                                <div class="font-semibold">{{ $team->name }}</div>
                                                                <div class="text-xs text-neutral-500">
                                                                    @if($team->personal_team)
                                                                        Pessoal
                                                                    @else
                                                                        {{ $team->users_count ?? $team->users->count() }} membros
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endforeach
                                        @endif
                                        <div class="border-t border-neutral-100 mt-2 pt-2">
                                            <a href="{{ route('profile.edit') }}" class="group flex items-center rounded-lg px-4 py-3 text-sm font-medium text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-100 text-primary-600 group-hover:bg-primary-200 mr-3">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="font-semibold">Gerenciar Times</div>
                                                    <div class="text-xs text-neutral-500 group-hover:text-primary-600">Configurações e membros</div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="{{ route('cloudflare-accounts.index') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('cloudflare-accounts.*') ? 'border-primary-500 text-primary-700' : 'border-transparent text-neutral-600 hover:border-neutral-300 hover:text-neutral-900' }} px-3 pt-1 text-sm font-semibold transition-all">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                                </svg>
                                Cloudflare
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Notification Bell -->
                        @livewire('notification-bell')
                        
                        <!-- User Menu -->
                        <div class="ml-3 relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="flex items-center space-x-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-lg px-3 py-2 hover:bg-neutral-50 transition-all" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                <div class="flex flex-col items-end">
                                    <span class="font-semibold text-neutral-900">{{ Auth::user()->name }}</span>
                                    <span class="text-xs text-neutral-500">{{ Auth::user()->email }}</span>
                                </div>
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg ring-2 ring-white">
                                    <span class="font-bold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </div>
                                <svg class="h-4 w-4 text-neutral-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-10 mt-2 w-64 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-neutral-200 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1" style="display: none;">
                                <div class="p-4 border-b border-neutral-200">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg">
                                            <span class="font-bold text-white text-lg">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-neutral-900">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-neutral-500 mt-0.5">{{ Auth::user()->email }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-2">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50 rounded-lg transition-colors" role="menuitem">
                                        <svg class="w-4 h-4 mr-3 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Meu Perfil
                                    </a>
                                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50 rounded-lg transition-colors" role="menuitem">
                                        <svg class="w-4 h-4 mr-3 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Configurações
                                    </a>
                                </div>
                                <div class="border-t border-neutral-200 p-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full text-left px-4 py-2.5 text-sm text-error-600 hover:bg-error-50 rounded-lg transition-colors" role="menuitem">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Sair
                                        </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="py-8">
            <main>
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 rounded-xl bg-gradient-to-r from-turquoise-50 to-green-50 border border-turquoise-200 p-4 shadow-sm">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-turquoise-500 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-turquoise-900">{{ session('success') }}</p>
                                </div>
                                <div class="ml-auto pl-3">
                                    <button @click="show = false" class="inline-flex rounded-lg p-1.5 text-turquoise-600 hover:bg-turquoise-100 transition-colors">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 rounded-xl bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 p-4 shadow-sm">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-red-900">{{ session('error') }}</p>
                                </div>
                                <div class="ml-auto pl-3">
                                    <button @click="show = false" class="inline-flex rounded-lg p-1.5 text-red-600 hover:bg-red-100 transition-colors">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- Toast Notification -->
    <x-toast />

    <!-- Confirm Delete Modal -->
    <x-confirm-modal 
        name="delete-confirmation" 
        title="Confirmar exclusão"
        message="Esta ação não pode ser desfeita. Deseja realmente excluir?"
        confirmText="Excluir"
        cancelText="Cancelar"
    />

    @livewireScripts
</body>
</html>
