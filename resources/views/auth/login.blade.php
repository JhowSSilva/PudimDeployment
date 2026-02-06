<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Bem-vindo ao Pudim Deployment</h2>
        <p class="mt-2 text-sm text-gray-600">Faça login para gerenciar seus servidores</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Senha" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Lembrar de mim</span>
            </label>
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                Entrar
            </x-primary-button>
        </div>

        @if (Route::has('password.request'))
            <div class="mt-4 text-center">
                <a class="text-sm text-indigo-600 hover:text-indigo-500" href="{{ route('password.request') }}">
                    Esqueceu sua senha?
                </a>
            </div>
        @endif
    </form>

    <div class="mt-6 text-center text-sm text-gray-500">
        <p>Credenciais de teste: admin@example.com / password</p>
    </div>
</x-guest-layout>
