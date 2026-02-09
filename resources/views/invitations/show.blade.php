<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-500 to-primary-700 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Card -->
            <div class="bg-neutral-800/90 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-8 py-6 text-center">
                    <div class="text-4xl mb-2">✉️</div>
                    <h1 class="text-2xl font-bold text-white">Convite para Time</h1>
                </div>

                <!-- Content -->
                <div class="px-8 py-6">
                    @if($invitation->isExpired())
                        <!-- Expired -->
                        <div class="text-center">
                            <div class="text-6xl mb-4">⏰</div>
                            <h2 class="text-xl font-bold text-neutral-100 mb-2">Convite Expirado</h2>
                            <p class="text-neutral-400 mb-6">Este convite expirou em {{ $invitation->expires_at->format('d/m/Y H:i') }}</p>
                            <a href="{{ route('profile.edit') }}" class="inline-block px-6 py-3 bg-neutral-9000 hover:bg-neutral-600 text-white font-semibold rounded-lg">
                                Ir para Perfil
                            </a>
                        </div>
                    @elseif($invitation->status === 'accepted')
                        <!-- Already Accepted -->
                        <div class="text-center">
                            <div class="text-6xl mb-4">✅</div>
                            <h2 class="text-xl font-bold text-neutral-100 mb-2">Convite Aceito</h2>
                            <p class="text-neutral-400 mb-6">Este convite já foi aceito anteriormente.</p>
                            <a href="{{ route('teams.show', $invitation->team) }}" class="inline-block px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-lg">
                                Ver Time
                            </a>
                        </div>
                    @elseif($invitation->status === 'rejected')
                        <!-- Rejected -->
                        <div class="text-center">
                            <div class="text-6xl mb-4">❌</div>
                            <h2 class="text-xl font-bold text-neutral-100 mb-2">Convite Recusado</h2>
                            <p class="text-neutral-400 mb-6">Este convite foi recusado.</p>
                            <a href="{{ route('profile.edit') }}" class="inline-block px-6 py-3 bg-neutral-9000 hover:bg-neutral-600 text-white font-semibold rounded-lg">
                                Ir para Perfil
                            </a>
                        </div>
                    @else
                        <!-- Pending - Show Invitation -->
                        <div class="text-center mb-6">
                            <p class="text-neutral-300 mb-2">
                                <strong>{{ $invitation->inviter->name }}</strong> convidou você para participar do time:
                            </p>
                            <h2 class="text-2xl font-bold text-primary-600 mb-2">{{ $invitation->team->name }}</h2>
                            @if($invitation->team->description)
                                <p class="text-neutral-400 text-sm">{{ $invitation->team->description }}</p>
                            @endif
                        </div>

                        <!-- Role Badge -->
                        <div class="bg-neutral-900 rounded-xl p-4 mb-6">
                            <p class="text-sm text-neutral-400 mb-2">Função no time:</p>
                            <div class="flex justify-center">
                                @if($invitation->role === 'admin')
                                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-error-900/30 text-error-400">
                                        🔴 Admin - Controle total
                                    </span>
                                @elseif($invitation->role === 'manager')
                                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-info-900/30 text-info-400">
                                        🔵 Gerente - Gerenciar recursos
                                    </span>
                                @elseif($invitation->role === 'member')
                                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-success-900/30 text-success-400">
                                        🟢 Membro - Criar recursos
                                    </span>
                                @else
                                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-neutral-700 text-neutral-800">
                                        ⚪ Visualizador - Apenas visualizar
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="space-y-3">
                            <form action="{{ route('invites.accept', $invitation->token) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-lg shadow-lg transition-all">
                                    ✅ Aceitar Convite
                                </button>
                            </form>

                            <form action="{{ route('invites.reject', $invitation->token) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-6 py-3 bg-neutral-200 hover:bg-neutral-300 text-neutral-300 font-semibold rounded-lg transition-all">
                                    ❌ Recusar
                                </button>
                            </form>
                        </div>

                        <!-- Expiration Info -->
                        <div class="mt-6 text-center">
                            <p class="text-xs text-neutral-500">
                                Este convite expira em {{ $invitation->expires_at->format('d/m/Y \à\s H:i') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center">
                <p class="text-white text-sm">
                    <a href="{{ route('login') }}" class="underline hover:no-underline">Fazer Login</a>
                    ou
                    <a href="{{ route('register') }}" class="underline hover:no-underline">Criar Conta</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
