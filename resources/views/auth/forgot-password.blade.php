<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold">Esqueceu a Senha?</h4>
        <p class="text-muted small">Informe seu e-mail e enviaremos um link para redefinir sua senha.</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" name="email" type="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div class="d-grid">
            <x-primary-button>{{ __('Enviar Link') }}</x-primary-button>
        </div>
    </form>

    <div class="text-center mt-3 small">
        <a href="{{ route('login') }}" class="text-decoration-none">Voltar para o login</a>
    </div>
</x-guest-layout>
