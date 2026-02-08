<x-layout title="Certificados SSL">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-neutral-100">Certificados SSL</h1>
                <p class="mt-2 text-neutral-400">Gerencie certificados SSL de todos os seus sites</p>
            </div>
            @if($servers->isEmpty())
                <div class="bg-neutral-800 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg border border-neutral-700/50">
                    <div class="p-6 text-center">
                        <div class="text-neutral-400 mb-4">
                            <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-neutral-100 mb-2">
                            Nenhum servidor encontrado
                        </h3>
                        <p class="text-neutral-400 mb-4">
                            Você precisa adicionar servidores com sites antes de gerenciar certificados SSL.
                        </p>
                        <a href="{{ route('servers.create') }}" 
                           class="bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                            Adicionar Servidor
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($servers as $server)
                        @if($server->sites->isNotEmpty())
                            <div class="bg-neutral-800 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg border border-neutral-700/50">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="bg-primary-900/40 p-2 rounded-lg mr-4">
                                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-medium text-neutral-900 dark:text-white">
                                                    {{ $server->name }}
                                                </h3>
                                                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                                    {{ $server->ip_address }} • {{ $server->sites->count() }} {{ Str::plural('site', $server->sites->count()) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($server->sites as $site)
                                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="flex items-center">
                                                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                                        </svg>
                                                        <h4 class="font-medium text-neutral-900 dark:text-white">
                                                            {{ $site->domain }}
                                                        </h4>
                                                    </div>
                                                    @if($site->sslCertificates->isNotEmpty())
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                            SSL Ativo
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                            Sem SSL
                                                        </span>
                                                    @endif
                                                </div>

                                                @if($site->sslCertificates->isNotEmpty())
                                                    @foreach($site->sslCertificates->take(2) as $cert)
                                                        <div class="mb-2 p-2 bg-gray-50 dark:bg-gray-900 rounded text-sm">
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex items-center">
                                                                    <svg class="h-4 w-4 text-green-600 dark:text-green-400 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                                    </svg>
                                                                    <span class="font-medium">
                                                                        {{ $cert->type === 'letsencrypt' ? "Let's Encrypt" : 'Custom' }}
                                                                    </span>
                                                                </div>
                                                                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                                                    @if($cert->expires_at)
                                                                        @php
                                                                            $daysUntilExpiry = now()->diffInDays($cert->expires_at, false);
                                                                        @endphp
                                                                        @if($daysUntilExpiry < 0)
                                                                            <span class="text-red-600 dark:text-red-400">Expirado</span>
                                                                        @elseif($daysUntilExpiry < 30)
                                                                            <span class="text-yellow-600 dark:text-yellow-400">{{ $daysUntilExpiry }} dias</span>
                                                                        @else
                                                                            <span class="text-primary-600 dark:text-primary-400">{{ $daysUntilExpiry }} dias</span>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-gray-500">-</span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    @if($site->sslCertificates->count() > 2)
                                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">
                                                            +{{ $site->sslCertificates->count() - 2 }} certificado(s) adicional(is)
                                                        </p>
                                                    @endif
                                                @else
                                                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-3">
                                                        Nenhum certificado SSL configurado
                                                    </p>
                                                @endif

                                                <a href="{{ route('ssl.show', $site) }}" 
                                                   class="text-primary-600 dark:text-primary-400 hover:text-green-800 dark:hover:text-green-200 text-sm font-medium">
                                                    Gerenciar SSL →
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if($servers->every(function($server) { return $server->sites->isEmpty(); }))
                        <div class="bg-white/80 dark:bg-neutral-800 backdrop-blur-sm overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-center">
                                <div class="text-neutral-500 dark:text-neutral-400 mb-4">
                                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">
                                    Nenhum site encontrado
                                </h3>
                                <p class="text-neutral-500 dark:text-neutral-400 mb-4">
                                    Você precisa adicionar sites aos seus servidores antes de gerenciar certificados SSL.
                                </p>
                                <a href="{{ route('sites.create') }}" 
                                   class="bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                                    Adicionar Site
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-layout>