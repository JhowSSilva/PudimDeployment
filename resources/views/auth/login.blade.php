<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-neutral-50">Bem-vindo ao Pudim Deployment</h2>
        <p class="mt-2 text-sm text-neutral-400">Faça login para gerenciar seus servidores</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-neutral-300 mb-2">E-mail</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autofocus 
                   autocomplete="username"
                   class="block w-full px-4 py-3 bg-neutral-900/50 border border-neutral-700 text-neutral-100 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 placeholder-neutral-500" 
                   placeholder="seu@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block text-sm font-medium text-neutral-300 mb-2">Senha</label>
            <input id="password" 
                   type="password" 
                   name="password" 
                   required 
                   autocomplete="current-password"
                   class="block w-full px-4 py-3 bg-neutral-900/50 border border-neutral-700 text-neutral-100 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 placeholder-neutral-500" 
                   placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" 
                       type="checkbox" 
                       class="rounded border-neutral-700 bg-neutral-900/50 text-primary-600 shadow-sm focus:ring-primary-500 focus:ring-offset-neutral-800" 
                       name="remember">
                <span class="ms-2 text-sm text-neutral-400 hover:text-neutral-300 transition-colors">Lembrar de mim</span>
            </label>
        </div>

        <div class="mt-6">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-primary/50">
                Entrar
            </button>
        </div>

        @if (Route::has('password.request'))
            <div class="mt-4 text-center">
                <a class="text-sm text-primary-400 hover:text-primary-300 font-medium transition-colors" 
                   href="{{ route('password.request') }}">
                    Esqueceu sua senha?
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
