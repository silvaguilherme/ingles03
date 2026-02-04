<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-alura-text">{{ __('Bem-vindo') }}</h1>
        <p class="text-alura-text-muted text-sm mt-1">{{ __('Entre com suas credenciais') }}</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Senha')" />

            <x-text-input id="password" class="block mt-2 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-700 bg-alura-card text-alura-accent shadow-sm focus:ring-alura-accent" name="remember">
                <span class="ms-2 text-sm text-alura-text-muted">{{ __('Lembrar-me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="text-sm text-alura-accent hover:text-alura-accent-hover rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-alura-dark focus:ring-alura-accent transition duration-200" href="{{ route('password.request') }}">
                    {{ __('Esqueceu sua senha?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Entrar') }}
            </x-primary-button>
        </div>

        <div class="text-center mt-4 text-sm">
            <span class="text-alura-text-muted">{{ __('Não tem conta?') }} </span>
            <a href="{{ route('register') }}" class="text-alura-accent hover:text-alura-accent-hover font-semibold">
                {{ __('Registrar') }}
            </a>
        </div>
    </form>
</x-guest-layout>
