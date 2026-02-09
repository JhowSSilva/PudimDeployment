<x-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent">
                        {{ $team->name }}
                    </h1>
                    <p class="text-neutral-400 mt-2">{{ $team->description }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-neutral-200 hover:bg-neutral-300 text-neutral-300 rounded-lg transition-all">
                    ← Voltar
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Invite Member Card -->
            @can('update', $team)
                <div class="bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-700 p-6">
                    <h2 class="text-xl font-bold text-neutral-100 mb-4">Convidar Membro</h2>
                    
                    <form action="{{ route('teams.invite', $team) }}" method="POST">
                        @csrf

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-300 mb-2">Email</label>
                                <input type="email" name="email" required placeholder="usuario@exemplo.com" class="w-full px-4 py-2 rounded-lg border border-neutral-600 focus:ring-2 focus:ring-primary-500">
                                <p class="text-xs text-neutral-500 mt-1">Enviaremos um convite por e-mail</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-300 mb-2">Função</label>
                                <select name="role" required class="w-full px-4 py-2 rounded-lg border border-neutral-600 focus:ring-2 focus:ring-primary-500">
                                    <option value="viewer">Visualizador - Apenas visualizar</option>
                                    <option value="member" selected>Membro - Criar e editar recursos</option>
                                    <option value="manager">Gerente - Gerenciar recursos e membros</option>
                                    <option value="admin">Admin - Controle total</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-lg shadow-lg transition-all">
                                Enviar Convite
                            </button>
                        </div>
                    </form>
                </div>
            @endcan

            <!-- Team Members List -->
            <div class="lg:col-span-2">
                <div class="bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-700 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-neutral-100">Membros ({{ $team->users->count() + 1 }})</h2>
                        @can('update', $team)
                            @php
                                $pendingInvites = \App\Models\TeamInvitation::where('team_id', $team->id)
                                    ->where('status', 'pending')
                                    ->count();
                            @endphp
                            @if($pendingInvites > 0)
                                <span class="text-sm text-neutral-400">{{ $pendingInvites }} convite(s) pendente(s)</span>
                            @endif
                        @endcan
                    </div>
                    
                    <div class="space-y-3">
                        <!-- Owner -->
                        <div class="flex items-center justify-between p-4 bg-purple-50 border border-purple-200 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 text-white font-bold mr-3">
                                    {{ strtoupper(substr($team->owner->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-neutral-100">{{ $team->owner->name }}</div>
                                    <div class="text-sm text-neutral-400">{{ $team->owner->email }}</div>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                👑 Proprietário
                            </span>
                        </div>

                        <!-- Members -->
                        @foreach($team->users as $member)
                            <div class="flex items-center justify-between p-4 bg-neutral-900 border border-neutral-700 rounded-lg hover:shadow-md transition-all">
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-primary-500 to-primary-600 text-white font-bold mr-3">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-neutral-100">{{ $member->name }}</div>
                                        <div class="text-sm text-neutral-400">{{ $member->email }}</div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    @can('update', $team)
                                        <form action="{{ route('teams.members.update', [$team, $member]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <select name="role" onchange="this.form.submit()" class="px-3 py-1 text-sm rounded-lg border border-neutral-600 focus:ring-2 focus:ring-primary-500">
                                                <option value="viewer" {{ $member->pivot->role === 'viewer' ? 'selected' : '' }}>Visualizador</option>
                                                <option value="member" {{ $member->pivot->role === 'member' ? 'selected' : '' }}>Membro</option>
                                                <option value="manager" {{ $member->pivot->role === 'manager' ? 'selected' : '' }}>Gerente</option>
                                                <option value="admin" {{ $member->pivot->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                            </select>
                                        </form>

                                        <form action="{{ route('teams.members.remove', [$team, $member]) }}" method="POST" onsubmit="return confirm('Remover este membro?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-error-500 hover:bg-error-500 text-white text-sm rounded-lg transition-all">
                                                Remover
                                            </button>
                                        </form>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                            @if($member->pivot->role === 'admin') bg-error-900/30 text-error-400
                                            @elseif($member->pivot->role === 'manager') bg-info-900/30 text-info-400
                                            @elseif($member->pivot->role === 'member') bg-success-900/30 text-success-400
                                            @else bg-neutral-700 text-neutral-800
                                            @endif">
                                            {{ ucfirst($member->pivot->role) }}
                                        </span>
                                    @endcan
                                </div>
                            </div>
                        @endforeach

                        @if($team->users->count() === 0)
                            <div class="bg-neutral-900 border-2 border-dashed border-neutral-600 rounded-xl p-8 text-center">
                                <svg class="w-16 h-16 mx-auto text-neutral-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <p class="text-neutral-400 font-medium">Nenhum membro ainda</p>
                                <p class="text-neutral-500 text-sm mt-2">Convide pessoas para colaborar com você</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pending Invitations -->
                @can('update', $team)
                    @php
                        $pendingInvitations = \App\Models\TeamInvitation::where('team_id', $team->id)
                            ->where('status', 'pending')
                            ->with('inviter')
                            ->get();
                    @endphp
                    
                    @if($pendingInvitations->count() > 0)
                        <div class="bg-warning-900/20 border border-yellow-200 rounded-2xl p-6 mt-6">
                            <h3 class="text-lg font-bold text-yellow-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Convites Pendentes ({{ $pendingInvitations->count() }})
                            </h3>
                            <div class="space-y-3">
                                @foreach($pendingInvitations as $invite)
                                    <div class="bg-neutral-800 rounded-lg p-4 border border-neutral-700">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold text-neutral-100">{{ $invite->email }}</div>
                                                <div class="text-sm text-neutral-400 mt-1">
                                                    Convidado por {{ $invite->inviter->name }} · 
                                                    {!! $invite->role_badge !!} ·
                                                    Expira em {{ $invite->expires_at->diffForHumans() }}
                                                </div>
                                                <div class="text-xs text-neutral-500 mt-2 font-mono bg-neutral-900 p-2 rounded border border-neutral-100 break-all">
                                                    {{ $invite->invite_url }}
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-2 flex-shrink-0">
                                                <form action="{{ route('invitations.resend', $invite) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                                                        Reenviar
                                                    </button>
                                                </form>
                                                <form action="{{ route('invitations.cancel', $invite) }}" method="POST" onsubmit="return confirm('Cancelar convite?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-4 py-2 bg-error-500 hover:bg-error-600 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                                                        Cancelar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endcan

                <!-- Permissions Guide -->
                <div class="bg-info-900/20 border border-blue-200 rounded-2xl p-6 mt-6">
                    <h3 class="text-lg font-bold text-blue-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Funções e Permissões
                    </h3>
                    <div class="space-y-2 text-sm text-info-400">
                        <div><strong>👑 Proprietário:</strong> Controle total, incluindo deletar o time</div>
                        <div><strong>🔴 Admin:</strong> Gerenciar membros e todos os recursos</div>
                        <div><strong>🔵 Gerente:</strong> Criar, editar e deletar recursos</div>
                        <div><strong>🟢 Membro:</strong> Criar e editar recursos próprios</div>
                        <div><strong>⚪ Visualizador:</strong> Apenas visualizar recursos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
