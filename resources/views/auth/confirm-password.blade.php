<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold">Confirmar Senha</h4>
        <p class="text-muted small">Por segurança, confirme sua senha antes de continuar.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-3">
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" name="password" type="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <div class="d-grid">
            <x-primary-button>{{ __('Confirmar') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
