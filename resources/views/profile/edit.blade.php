<x-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">
                    Configurações
                </h1>
                <p class="text-neutral-300 mt-2">Gerencie seu perfil, senha e times</p>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-gradient-to-r from-success-500 to-success-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-gradient-to-r from-error-500 to-error-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Tabs Navigation -->
            <div class="mb-6" x-data="{ activeTab: 'profile' }">
                <div class="flex space-x-2 border-b border-neutral-200">
                    <button @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'border-primary-500 text-primary-400' : 'border-transparent text-neutral-400 hover:text-neutral-200 hover:border-neutral-600'" class="px-6 py-3 font-medium border-b-2 transition-all">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Perfil
                    </button>
                    <button @click="activeTab = 'password'" :class="activeTab === 'password' ? 'border-primary-500 text-primary-400' : 'border-transparent text-neutral-400 hover:text-neutral-200 hover:border-neutral-600'" class="px-6 py-3 font-medium border-b-2 transition-all">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Senha
                    </button>
                    <button @click="activeTab = 'teams'" :class="activeTab === 'teams' ? 'border-primary-500 text-primary-400' : 'border-transparent text-neutral-400 hover:text-neutral-200 hover:border-neutral-600'" class="px-6 py-3 font-medium border-b-2 transition-all">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Times
                    </button>
                </div>

                <!-- Profile Tab -->
                <div x-show="activeTab === 'profile'" x-cloak class="mt-6">
                    <div class="bg-white rounded-2xl shadow-lg border border-neutral-200 p-8">
                        <h2 class="text-2xl font-bold text-white mb-6">Informações do Perfil</h2>
                        
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-200 mb-2">Nome</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    @error('name')
                                        <p class="text-error-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-200 mb-2">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    @error('email')
                                        <p class="text-error-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                            <div class="flex justify-end">
                                <x-button type="submit" variant="primary">
                                    Salvar Alterações
                                </x-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

                <!-- Password Tab -->
                <div x-show="activeTab === 'password'" x-cloak class="mt-6">
                    <div class="bg-white rounded-2xl shadow-lg border border-neutral-200 p-8">
                        <h2 class="text-2xl font-bold text-white mb-6">Atualizar Senha</h2>
                    
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-200 mb-2">Senha Atual</label>
                                    <input type="password" name="current_password" required class="w-full px-4 py-3 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    @error('current_password')
                                        <p class="text-error-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-200 mb-2">Nova Senha</label>
                                    <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    @error('password')
                                        <p class="text-error-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-200 mb-2">Confirmar Nova Senha</label>
                                    <input type="password" name="password_confirmation" required class="w-full px-4 py-3 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div class="flex justify-end">
                                    <x-button type="submit" variant="primary">
                                        Atualizar Senha
                                    </x-button>
                                </div>
                        </div>
                    </form>
                </div>
            </div>

                <!-- Teams Tab -->
                <div x-show="activeTab === 'teams'" x-cloak class="mt-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Create Team Card -->
                        <div class="bg-white rounded-2xl shadow-lg border border-neutral-200 p-8">
                            <h2 class="text-2xl font-bold text-white mb-6">Criar Novo Time</h2>
                        
                        <form action="{{ route('profile.teams.create') }}" method="POST">
                            @csrf

                            <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-200 mb-2">Nome do Time</label>
                                        <input type="text" name="name" required class="w-full px-4 py-3 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-200 mb-2">Descrição (opcional)</label>
                                        <textarea name="description" rows="3" class="w-full px-4 py-3 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                                    </div>

                                    <x-button type="submit" variant="primary" class="w-full">
                                        Criar Time
                                    </x-button>
                            </div>
                        </form>
                    </div>

                        <!-- Teams List -->
                        <div class="space-y-4">
                            <h2 class="text-2xl font-bold text-white">Meus Times</h2>
                        
                            @forelse($ownedTeams as $team)
                                <div class="bg-white rounded-xl shadow-lg border border-neutral-200 p-6 hover:shadow-2xl hover:scale-[1.02] transition-all">
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h3 class="text-xl font-bold text-white">{{ $team->name }}</h3>
                                            <p class="text-neutral-300 text-sm mt-1">{{ $team->description }}</p>
                                    </div>
                                    {!! $team->role_badge !!}
                                </div>

                                    <div class="flex items-center text-sm text-neutral-600 mb-4">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        {{ $team->users_count }} {{ Str::plural('membro', $team->users_count) }}
                                    </div>

                                    <div class="flex space-x-2">
                                        <x-button href="{{ route('teams.show', $team) }}" variant="primary" class="flex-1">
                                            Gerenciar
                                        </x-button>
                                        @if(!$team->personal_team)
                                            <form action="{{ route('profile.teams.delete', $team) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja deletar este time?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger">
                                                    Deletar
                                                </x-button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="bg-neutral-50 border-2 border-dashed border-neutral-300 rounded-xl p-8 text-center">
                                    <svg class="w-16 h-16 mx-auto text-neutral-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-neutral-300 font-medium">Você ainda não criou nenhum time</p>
                                    <p class="text-neutral-500 text-sm mt-2">Crie um time para colaborar com outros usuários</p>
                                </div>
                            @endforelse

                            @if($teams->count() > 0)
                                <h3 class="text-xl font-bold text-white mt-8">Times que Participo</h3>
                                @foreach($teams as $team)
                                    <div class="bg-white rounded-xl shadow-lg border border-neutral-200 p-6 hover:shadow-2xl hover:scale-[1.02] transition-all">
                                        <div class="flex items-start justify-between mb-4">
                                            <div>
                                                <h3 class="text-xl font-bold text-white">{{ $team->name }}</h3>
                                                <p class="text-neutral-300 text-sm mt-1">{{ $team->description }}</p>
                                        </div>
                                        {!! $team->role_badge !!}
                                    </div>

                                        <div class="flex items-center text-sm text-neutral-400 mb-4">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            {{ $team->users_count }} {{ Str::plural('membro', $team->users_count) }}
                                        </div>

                                        <x-button href="{{ route('teams.show', $team) }}" variant="primary" class="w-full">
                                            Ver Detalhes
                                        </x-button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        // Detectar hash na URL e ativar a aba correspondente
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash.substring(1); // Remove o #
            if (hash && ['profile', 'password', 'teams'].includes(hash)) {
                // Atualizar Alpine.js activeTab
                const tabContainer = document.querySelector('[x-data*="activeTab"]');
                if (tabContainer) {
                    // Trigger click no botão da aba
                    const tabButton = document.querySelector(`button[\\@click*="${hash}"]`);
                    if (tabButton) {
                        tabButton.click();
                    }
                }
            }
        });
    </script>
</x-layout>
