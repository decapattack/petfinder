<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold">Verificar E-mail</h4>
    </div>

    <p class="text-muted small mb-4">
        Obrigado por se cadastrar! Antes de continuar, verifique seu endereço de e-mail clicando no link que enviamos a você.
        Caso não tenha recebido, podemos reenviar.
    </p>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <div class="d-grid">
            <x-primary-button>Reenviar Link de Verificação</x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <div class="text-center">
            <button type="submit" class="btn btn-link text-muted text-decoration-none small">Sair</button>
        </div>
    </form>
</x-guest-layout>
