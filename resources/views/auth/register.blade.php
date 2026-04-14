<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-4 text-center">
            <h4 class="fw-bold">Criar Conta PetFinder</h4>
        </div>

        <!-- Nome -->
        <div class="mb-3">
            <label class="form-label">Nome Completo</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- E-mail -->
        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Telefone -->
        <div class="mb-3">
            <label class="form-label">Telefone (WhatsApp)</label>
            <input type="tel" name="telefone" class="form-control @error('telefone') is-invalid @enderror" value="{{ old('telefone') }}" required placeholder="(00) 00000-0000">
            @error('telefone') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Senha -->
        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Confirmar Senha -->
        <div class="mb-3">
            <label class="form-label">Confirmar Senha</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="alert alert-info py-2 small mb-4">
            📍 <strong>Radar Pet:</strong> Ao finalizar, solicitaremos seu GPS para o radar de 1 KM.
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">Finalizar Cadastro</button>
        </div>

        <div class="divider d-flex align-items-center my-4">
            <hr class="flex-grow-1"><span class="px-3 text-muted small">ou cadastre com</span><hr class="flex-grow-1">
        </div>

        <div class="d-flex justify-content-center gap-2 mb-3">
            <a href="{{ route('auth.social.redirect', 'google') }}" class="btn btn-outline-dark rounded-circle p-2"><img src="https://img.icons8.com/color/24/google-logo.png"></a>
            <a href="{{ route('auth.social.redirect', 'twitter-oauth-2') }}" class="btn btn-outline-dark rounded-circle p-2"><img src="https://img.icons8.com/ios-filled/24/twitterx--v2.png"></a>
            <a href="{{ route('auth.social.redirect', 'microsoft') }}" class="btn btn-outline-dark rounded-circle p-2"><img src="https://img.icons8.com/color/24/microsoft.png"></a>
        </div>

        <p class="text-center small mt-4">Já tem conta? <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Login</a></p>
    </form>
</x-guest-layout>
