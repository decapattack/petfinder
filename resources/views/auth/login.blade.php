<x-guest-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-4 text-center">
            <h4 class="fw-bold">Login PetFinder</h4>
        </div>

        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="seu@email.com">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-control" required placeholder="********">
        </div>

        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
        </div>

        <div class="divider d-flex align-items-center my-4">
            <hr class="flex-grow-1"><span class="px-3 text-muted small">ou entre com</span><hr class="flex-grow-1">
        </div>

        <div class="d-flex justify-content-center gap-2 mb-3">
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

        <p class="text-center small mt-4">Não tem conta? <a href="{{ route('register') }}" class="text-decoration-none fw-bold">Cadastre-se</a></p>
    </form>
</x-guest-layout>
