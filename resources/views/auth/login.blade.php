<x-guest-layout>
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <div class="text-center mb-4">
        <h4 class="fw-bold">Entrar no PetFinder</h4>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" name="email" type="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" name="password" type="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-3 form-check">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label class="form-check-label" for="remember_me">Lembrar de mim</label>
        </div>

        <div class="d-grid gap-2 mb-3">
            <x-primary-button class="btn-lg w-100">{{ __('Entrar') }}</x-primary-button>
        </div>

        @if (Route::has('password.request'))
            <div class="text-center small">
                <a href="{{ route('password.request') }}" class="text-decoration-none">Esqueceu sua senha?</a>
            </div>
        @endif
    </form>

    <div class="d-flex align-items-center my-4">
        <hr class="flex-grow-1"><span class="px-3 text-muted small">ou entre com</span><hr class="flex-grow-1">
    </div>

    <div class="d-flex justify-content-center gap-2 mb-4">
        <a href="{{ route('auth.social.redirect', 'google') }}" class="btn btn-outline-dark rounded-circle p-2" title="Google">
            <img src="https://img.icons8.com/color/24/google-logo.png" alt="Google">
        </a>
        <a href="{{ route('auth.social.redirect', 'twitter-oauth-2') }}" class="btn btn-outline-dark rounded-circle p-2" title="X">
            <img src="https://img.icons8.com/ios-filled/24/twitterx--v2.png" alt="X">
        </a>
        <a href="{{ route('auth.social.redirect', 'microsoft') }}" class="btn btn-outline-dark rounded-circle p-2" title="Microsoft">
            <img src="https://img.icons8.com/color/24/microsoft.png" alt="Microsoft">
        </a>
    </div>

    <p class="text-center small">Não tem conta? <a href="{{ route('register') }}" class="fw-bold text-decoration-none">Cadastre-se</a></p>
</x-guest-layout>
